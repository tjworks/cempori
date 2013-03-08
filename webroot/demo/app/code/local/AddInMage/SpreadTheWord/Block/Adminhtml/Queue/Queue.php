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


class AddInMage_SpreadTheWord_Block_Adminhtml_Queue_Queue extends Mage_Adminhtml_Block_Widget_Container
{

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('addinmage/spreadtheword/queue.phtml');
	}

	protected function _prepareLayout()
	{
		if ($this->testNow()) {
			$this->_addButton('now', array (
					'label' => $this->__('Send All Now'), 
					'title' => $this->__('Ingnore Cron Job and send all Emails now'), 
					'id' => "adm_stw_send_now_button", 
					'onclick' => "stwSendQueued();", 
					'class' => 'adm-stw-now'));
		}
		
		if ($this->testFailed()) {
			Mage::register('can_show_failed_control', true);
			$this->_addButton('retry', array (
					'label' => $this->__('Requeue all failed invitations'), 
					'title' => $this->__('Requeue all failed invitations'), 
					'onclick' => "setLocation('{$this->getUrl('*/*/retry')}')", 
					'class' => 'adm-stw-retry'));
			
			$confirm_text = $this->__('Removing failed invitation messages from the queue will also delete the respective problem reports.\n\n The messages will be deleted permanently.');
			$this->_addButton('clear', array (
					'label' => $this->__('Delete all failed invitations'), 
					'title' => $this->__('Delete all failed invitations'), 
					'onclick' => "if(confirm('{$confirm_text}')){setLocation('{$this->getUrl('*/*/clear')}')}", 
					'class' => 'adm-stw-clear'));
		}
		
		$this->setChild('grid', $this->getLayout()->createBlock('spreadtheword/adminhtml_queue_queue_grid', 'queue.grid'));
		
		return parent::_prepareLayout();
	}

	public function getGridHtml()
	{
		return $this->getChildHtml('grid');
	}

	protected function testFailed()
	{
		return (Mage::getModel('spreadtheword/queue')->getCollection()
			->addFilter('status', AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED)
			->count()) ? true : false;
	}

	protected function testNow()
	{
		return (Mage::getModel('spreadtheword/queue')->getCollection()
			->count()) ? true : false;
	}
}
