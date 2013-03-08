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


class AddInMage_SpreadTheWord_Adminhtml_ProblemsController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
		$this->_getSession()->unsExpNotice();
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Invitation Email Problem Reports'));
		$this->_initAction()->_addBreadcrumb($this->__('Spread The Word'), $this->__('Invitation Email Problem Reports'));
		$this->renderLayout();
	}

	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu('addinmage/spreadtheword/invitation_emails/problems')->_addBreadcrumb($this->__('Spread The Word'), $this->__('Invitation Email Problem Reports'));
		return $this;
	}

	public function retryAction()
	{
		try {
			Mage::getModel('spreadtheword/queue')->queueFailed($from_problem_contr = true);
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		return $this->_redirect('*/*/index');
	
	}

	public function massReassignDiscountsAction()
	{
		$failed = $this->getRequest()->getParam('reassign_failed');
		$new = $this->getRequest()->getParam('reassign_new');
		
		if (! $failed || ! $new) $this->_getSession()->addNotice($this->__('Please specify failed and new discounts'));
		else {
			try {
				$ids = Mage::getModel('spreadtheword/problems')->reassignDiscount($failed, $new);
				if ($ids) $this->_getSession()
					->addSuccess($this->__('The selected discount was successfully applied to %d failed message(s).', $ids));
			} catch (Exception $e) {
				$this->_getSession()->addError($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		
		$this->_redirect('*/*/index');
	}

	public function massRetryAction()
	{
		$ids = $this->getRequest()->getParam('spreadtheword_massaction_ids');
		$can_be_queued = array ();
		$already_queued = array ();
		$reassign_req = array ();
		
		if (! is_array($ids)) $this->_getSession()->addError($this->__('Please select message(s)'));
		
		else {
			try {
				$problems = Mage::getModel('spreadtheword/problems')->getCollection();
				$resource = Mage::getSingleton('core/resource');
				$joined_tables = $problems->join($resource->getTableName('queue'), 'main_table.queue_id = queue.id', array ('main_table.id', 'main_table.queue_id', 'main_table.error_code', 'queue.id', 'queue.status'))
					->addFieldToFilter('main_table.id', array ('in' => $ids));
				
				foreach ($joined_tables as $item) {
					if ($item->getStatus() == AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED && $item->getErrorCode() == AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG) $can_be_queued [] = $item->getQueueId();
					
					if ($item->getStatus() == AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE && $item->getErrorCode() !== AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG) $already_queued [] = $item->getQueueId();
					
					if ($item->getStatus() == AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED && $item->getErrorCode() != AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG) $reassign_req [] = $item->getQueueId();
				}
				
				if ($can_be_queued) {
					$result = Mage::getModel('spreadtheword/queue')->getResource()
						->changeStatus($can_be_queued, AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE, array (AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED));
					
					if ($result) $this->_getSession()
						->addSuccess($this->__('Total of %d failed message(s) were successfully requeued.', $result));
				
				}
				if ($already_queued) $this->_getSession()
					->addNotice($this->__('Total of %d  message(s) can not be processed because they are alredy requeued.', count($already_queued)));
				
				if ($reassign_req) $this->_getSession()
					->addNotice($this->__('Total of %d  message(s) can not be processed because the invitation discounts have expired. Please update the discounts and try to requeue again.', count($reassign_req)));
			} catch (Exception $e) {
				$this->_getSession()
					->addError($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		
		$this->_redirect('*/*/index');
	}
}
