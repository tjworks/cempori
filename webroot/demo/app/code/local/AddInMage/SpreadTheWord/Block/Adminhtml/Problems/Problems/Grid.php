<?php

/**
 * Add In Mage::
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the EULA that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL: http://add-in-mage.com/support/presales/eula/
 *
 *
 * PROPRIETARY DATA
 * 
 * This file contains trade secret data which is the property of Add In Mage:: Ltd. 
 * This file is submitted to recipient in confidence.
 * Information and source code contained herein may not be used, copied, sold, distributed, 
 * sub-licensed, rented, leased or disclosed in whole or in part to anyone except as permitted by written
 * agreement signed by an officer of Add In Mage:: Ltd.
 * 
 * 
 * MAGENTO EDITION NOTICE
 * 
 * This software is designed for Magento Community edition.
 * Add In Mage:: Ltd. does not guarantee correct work of this extension on any other Magento edition.
 * Add In Mage:: Ltd. does not provide extension support in case of using a different Magento edition.
 * 
 * 
 * @category    AddInMage
 * @package     AddInMage_SpreadTheWord
 * @copyright   Copyright (c) 2012 Add In Mage:: Ltd. (http://www.add-in-mage.com)
 * @license     http://add-in-mage.com/support/presales/eula/  End User License Agreement (EULA)
 * @author      Add In Mage:: Team <team@add-in-mage.com>
 */

class AddInMage_SpreadTheWord_Block_Adminhtml_Problems_Problems_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('spreadtheword_problems_grid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->_filterVisibility = false;
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('spreadtheword/problems')->getCollection();
		$collection->join('queue', 'main_table.queue_id = queue.id', array (
					'main_table.id', 
					'main_table.failed_at', 
					'queue.friend_id', 
					'queue.data_id', 
					'queue.status', 
					'main_table.error_code'));
		
		$collection->join('friends', 'queue.friend_id = friends.id', array (
				'friends.friend_id', 
				'friends.friend_name'));
		
		$collection->join('data', 'queue.data_id = data.id', array (
				'data.type', 
				'data.promised_amount', 
				'data.simple_action', 
				'data.sender_name', 
				'data.sender_email', 
				'data.discount_rule'));
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array (
				'header' => $this->__('ID'), 
				'align' => 'right', 
				'index' => 'id', 
				'filter_index' => 'main_table.id'));
		
		$this->addColumn('sender', array (
				'header' => $this->__('Sender'), 
				'align' => 'left', 
				'index' => 'sender_name', 
				'renderer' => 'spreadtheword/adminhtml_queue_queue_grid_renderer_sender', 
				'filter_index' => 'data.sender_name'));
		
		$this->addColumn('recipient', array (
				'header' => $this->__('Recipient'), 
				'align' => 'left', 
				'index' => 'friend_name', 
				'renderer' => 'spreadtheword/adminhtml_problems_problems_grid_renderer_recipient', 
				'filter_index' => 'friends.friend_name'));
		
		$this->addColumn('type', array (
				'header' => $this->__('Email Type'), 
				'align' => 'left', 
				'index' => 'type', 
				'type' => 'options', 
				'options' => array (AddInMage_SpreadTheWord_Model_Data::STATUS_DISCOUNT => $this->__('Invitation with a discount'), AddInMage_SpreadTheWord_Model_Data::STATUS_NOACTION => $this->__('Invitation only'))));
		
		$this->addColumn('failed_at', array (
				'header' => $this->__('Failed On'), 
				'align' => 'left', 
				'type' => 'datetime', 
				'index' => 'failed_at', 
				'filter_index' => 'main_table.failed_at'));
		
		$this->addColumn('problem', array (
				'header' => $this->__('Problem Description'), 
				'index' => 'error_code', 
				'type' => 'options', 
				'renderer' => 'spreadtheword/adminhtml_problems_problems_grid_renderer_discount', 
				'options' => array (AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG => $this->__('System error'), AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_DSC => $this->__('The given discount has expired'), AddInMage_SpreadTheWord_Model_Problems::ERROR_DSC_DLT => $this->__('The given discount no longer exists'))));
		
		$this->addColumn('status', array (
				'header' => $this->__('Status'), 
				'index' => 'status', 
				'type' => 'options', 
				'frame_callback' => array ($this, 'decorateStatus'), 
				'options' => array (
						AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE => $this->__('Requeued'), 
						AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED => $this->__('Failed'))));
		
		return parent::_prepareColumns();
	}

	public function decorateStatus($value, $row, $column, $isExport)
	{
		switch ($row->getStatus()) {
						
			case AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE:
			$value = $this->__('Requeued');
			break;
			
			case AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED:
			$value = $this->__('Failed');
			break;			
		}
		
		return $value;
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('spreadtheword_massaction_ids');
		if (Mage::registry('can_show_failed_control')) {
			$this->getMassactionBlock()->addItem('retry', array (
					'label' => $this->__('Requeue the selected invitations'), 
					'url' => $this->getUrl('*/*/massRetry'), 'width' => '100px'));
		}
		
		return $this;
	}
}