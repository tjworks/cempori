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


class AddInMage_SpreadTheWord_Block_Adminhtml_Queue_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('spreadtheword_queue_grid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->_filterVisibility = false;
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('spreadtheword/queue')->getCollection();
		
		$collection->join('friends', 'main_table.friend_id = friends.id', array (
				'main_table.id', 
				'main_table.status', 
				'friends.source', 
				'friends.invited_time'));
		
		$collection->join('data', 'main_table.data_id = data.id', array (
				'data.type', 
				'data.personal_message', 
				'data.sender_name', 
				'data.sender_email'));
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array (
				'header' => $this->__('ID'), 
				'align' => 'right', 
				'width' => '30px', 
				'index' => 'id', 
				'filter_index' => 'main_table.id'));
		
		$this->addColumn('service', array (
				'header' => $this->__('Service / Tool'), 
				'align' => 'left', 
				'width' => '200px', 
				'index' => 'source', 
				'filter_index' => 'friends.source', 
				'frame_callback' => array ($this, 'decorateCode')));
		
		$this->addColumn('type', array (
				'header' => $this->__('Email Type'), 
				'align' => 'left', 
				'width' => '300px', 
				'index' => 'type', 
				'type' => 'options', 
				'options' => array (AddInMage_SpreadTheWord_Model_Data::STATUS_DISCOUNT => $this->__('Invitation with a discount'), AddInMage_SpreadTheWord_Model_Data::STATUS_NOACTION => $this->__('Invitation only'))));
		
		$this->addColumn('invited_time', array (
				'header' => $this->__('Queued on'), 
				'align' => 'left', 
				'width' => '200px', 
				'type' => 'datetime', 
				'index' => 'invited_time', 
				'filter_index' => 'friends.invited_time'));
		
		$this->addColumn('sender', array (
				'header' => $this->__('Sender'), 
				'align' => 'left', 
				'width' => '400px', 
				'index' => 'data.sender_name', 
				'renderer' => 'spreadtheword/adminhtml_queue_queue_grid_renderer_sender'));
		
		$this->addColumn('pm', array (
				'header' => $this->__('Personal Message'), 
				'align' => 'left', 
				'width' => '200px', 
				'index' => 'type', 
				'filter' => false, 
				'frame_callback' => array ($this, 'decoratePersonal')));
		
		$this->addColumn('status', array (
				'header' => $this->__('Status'), 
				'index' => 'status', 
				'type' => 'options', 
				'options' => array (AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE => $this->__('In queue'), AddInMage_SpreadTheWord_Model_Queue::STATUS_SENT => $this->__('Sent'), AddInMage_SpreadTheWord_Model_Queue::STATUS_SENDING => $this->__('Sending'), AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED => $this->__('Failed'), AddInMage_SpreadTheWord_Model_Queue::STATUS_PAUSED => $this->__('Paused')), 'width' => '100px'));
		
		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('spreadtheword_massaction_ids');
		
		$this->getMassactionBlock()->addItem('pause', array (
				'label' => $this->__('Pause the selected messages'), 
				'url' => $this->getUrl('*/*/massPause'), 
				'width' => '100px'));
		
		$this->getMassactionBlock()->addItem('unpause', array (
				'label' => $this->__('Unpause the selected messages'), 
				'url' => $this->getUrl('*/*/massUnpause'), 
				'width' => '100px'));
		
		$this->getMassactionBlock()->addItem('delete', array (
				'label' => $this->__('Delete the selected messages'),
				'url' => $this->getUrl('*/*/massDelete'), 
				'confirm' => $this->__('The selected messages will be deleted permanently.'), 
				'width' => '100px'));
		
		if (Mage::registry('can_show_failed_control')) {
			$this->getMassactionBlock()->addItem('retry', array (
					'label' => $this->__('Requeue the selected invitations'), 
					'url' => $this->getUrl('*/*/massRetry'), 
					'width' => '100px'));
		}
		
		return $this;
	}

	public function decorateCode($value, $row, $column, $isExport)
	{
		return ucfirst($value);
	}

	public function decoratePersonal($value, $row, $column, $isExport)
	{
		if ($row->getPersonalMessage()) $value = $this->__('Yes');
		else $value = $this->__('No');
		return $value;
	}
}