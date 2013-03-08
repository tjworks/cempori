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

class AddInMage_SpreadTheWord_Adminhtml_QueueController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
		
		$this->_getSession()->unsExpNotice();
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Invitation Email Queue'));
		$this->_initAction()->_addBreadcrumb($this->__('Spread The Word'), $this->__('Invitation Email Queue'));
		$this->renderLayout();
	}

	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('addinmage/spreadtheword/invitation_emails/queue')
			->_addBreadcrumb($this->__('Spread The Word'), $this->__('Invitation Email Queue'));
		return $this;
	}

	public function clearAction()
	{
		try {
			
			$msgs = Mage::getModel('spreadtheword/queue')->getCollection();
			$friends_ids = $msgs->addFieldToFilter('status', array ('eq' => AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED))
				->getColumnValues('friend_id');
			Mage::getModel('spreadtheword/friends')->getResource()
				->updateInviteStatus($friends_ids, false);
			
			$delete_result = Mage::getModel('spreadtheword/queue')->getResource()
				->deleteFromQueue(false, array (AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED));
			
			Mage::getModel('spreadtheword/data')->checkQueuedData();
			
			if ($delete_result) $this->_getSession()
				->addSuccess($this->__('Total of %d failed message(s) were successfully deleted from the queue!', $delete_result));
		
		} catch (Exception $e) {
			$this->_getSession()
				->addError($e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		return $this->_redirect('*/*/index');
	
	}

	public function retryAction()
	{
		try {
			Mage::getModel('spreadtheword/queue')->queueFailed(false);
		} catch (Exception $e) {
			$this->_getSession()
				->addError($e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		return $this->_redirect('*/*/index');
	
	}

	public function nowAction()
	{
		$time = $this->getRequest()
			->getParam('stw-delay-value');
		$dimension = $this->getRequest()
			->getParam('stw-delay-dimension');
		$delay = ($time && $dimension) ? array ('time' => trim((int) $time), 'dimension' => trim((string) $dimension)) : null;
		$show_progress = $this->getRequest()
			->getParam('show_pb');
		
		if ($show_progress) {
			set_time_limit(0);
			ignore_user_abort(true);
			Mage::getModel('spreadtheword/queue')->sendNow($delay, true);
		} else {
			$collection = Mage::getModel('spreadtheword/queue')->getCollection()
				->addFilter('status', AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE);
			ob_end_clean();
			header("Connection: close");
			set_time_limit(0);
			ignore_user_abort(true);
			ob_start();
			if ($collection->count()) echo $this->__('The queue is locked for sending %d message(s). All messages will be processed in the background. You can safely leave or refresh this page.', $collection->count());
			else echo $this->__('There are no messages ready for sending.');
			$size = ob_get_length();
			header("Content-Length: $size");
			ob_end_flush();
			flush();
			sleep(2);
			Mage::getModel('spreadtheword/queue')->sendNow($delay, false);
		}
	}

	public function massDeleteAction()
	{
		$ids = $this->getRequest()
			->getParam('spreadtheword_massaction_ids');
		if (! is_array($ids)) {
			$this->_getSession()
				->addError($this->__('Please select message(s)'));
		} else {
			$deleted = 0;
			$msgs = Mage::getModel('spreadtheword/queue');
			try {
				$sending = $msgs->getCollection()
					->addFieldToFilter('id', array ('in' => $ids))
					->addFieldToFilter('status', array ('eq' => AddInMage_SpreadTheWord_Model_Queue::STATUS_SENDING))
					->getAllIds();
				$friends_ids = $msgs->getCollection()
					->addFieldToFilter('id', array ('in' => $ids))
					->addFieldToFilter('status', array ('neq' => AddInMage_SpreadTheWord_Model_Queue::STATUS_SENDING))
					->getColumnValues('friend_id');
				Mage::getModel('spreadtheword/friends')->getResource()
					->updateInviteStatus($friends_ids, false);
				
				$delete_result = Mage::getModel('spreadtheword/queue')->getResource()
					->deleteFromQueue($ids, false, array (AddInMage_SpreadTheWord_Model_Queue::STATUS_SENDING));
				
				$deleted += $delete_result;
				
				if ($sending) $this->_getSession()
					->addNotice($this->__('The messages with the following ids cannot be deleted because they are being sent: %s.', implode(', ', $sending)));
				
				if ($deleted > 0) $this->_getSession()
					->addSuccess($this->__('Total of %d message(s) were successfully deleted.', $deleted));
				Mage::getModel('spreadtheword/data')->checkQueuedData();
			
			} catch (Exception $e) {
				$this->_getSession()
					->addError($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		
		$this->_redirect('*/*/index');
	}

	public function massPauseAction()
	{
		$ids = $this->getRequest()
			->getParam('spreadtheword_massaction_ids');
		
		if (! is_array($ids)) $this->_getSession()
			->addError($this->__('Please select message(s)'));
		
		else {
			$updated = 0;
			$msgs = Mage::getModel('spreadtheword/queue');
			
			try {
				
				$sending = $msgs->getCollection()
					->addFieldToFilter('id', array ('in' => $ids))
					->addFieldToFilter('status', array ('eq' => AddInMage_SpreadTheWord_Model_Queue::STATUS_SENDING))
					->getAllIds();
				$failed = $msgs->getCollection()
					->addFieldToFilter('id', array ('in' => $ids))
					->addFieldToFilter('status', array ('eq' => AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED))
					->getAllIds();
				
				$result = Mage::getModel('spreadtheword/queue')->getResource()
					->changeStatus($ids, AddInMage_SpreadTheWord_Model_Queue::STATUS_PAUSED, array (AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE));
				
				$updated += $result;
				
				if ($sending) $this->_getSession()
					->addNotice($this->__('The messages with the following ids cannot be paused because they are being sent: %s.', implode(', ', $sending)));
				
				if ($failed) $this->_getSession()
					->addNotice($this->__('The messages with the following ids cannot be paused because they are failed: %s.', implode(', ', $failed)));
				
				if ($updated > 0) $this->_getSession()
					->addSuccess($this->__('Total of %d message(s) were successfully paused.', $updated));
				
				else $this->_getSession()
					->addSuccess($this->__('There are no messages ready to be paused.'));
			} catch (Exception $e) {
				$this->_getSession()
					->addError($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		
		$this->_redirect('*/*/index');
	}

	public function massUnpauseAction()
	{
		$ids = $this->getRequest()
			->getParam('spreadtheword_massaction_ids');
		
		if (! is_array($ids)) $this->_getSession()
			->addError($this->__('Please select message(s)'));
		
		else {
			$updated = 0;
			try {
				$result = Mage::getModel('spreadtheword/queue')->getResource()
					->changeStatus($ids, AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE, array (AddInMage_SpreadTheWord_Model_Queue::STATUS_PAUSED));
				
				$updated += $result;
				if ($updated > 0) $this->_getSession()
					->addSuccess($this->__('Total of %d message(s) were successfully unpaused.', $updated));
				
				else $this->_getSession()
					->addSuccess($this->__('There are no messages ready to be unpaused.'));
			} catch (Exception $e) {
				$this->_getSession()
					->addError($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		
		$this->_redirect('*/*/index');
	}

	public function massRetryAction()
	{
		$ids = $this->getRequest()
			->getParam('spreadtheword_massaction_ids');
		$can_be_queued = array ();
		$already_queued = array ();
		$reassign_req = array ();
		
		if (! is_array($ids)) $this->_getSession()
			->addError($this->__('Please select message(s)'));
		
		else {
			try {
				$msgs = Mage::getModel('spreadtheword/queue')->getCollection();
				$resource = Mage::getSingleton('core/resource');
				$joined_tables = $msgs->join($resource->getTableName('problems'), 'main_table.id = problems.queue_id', array ('main_table.id', 'main_table.status', 'problems.queue_id', 'problems.error_code'))
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
				
				$have_access = Mage::getSingleton('admin/session')->isAllowed('addinmage/invitation_emails/problems');
				$link = ($have_access) ? '<a href=' . $this->getUrl('spreadtheword/adminhtml_problems') . '>' . $this->__('Please follow this link to update discounts') . '</a>.' : '';
				
				if ($reassign_req) $this->_getSession()
					->addNotice($this->__('Total of %d  message(s) can not be processed because the invitation discounts have expired. Please update the discounts and try to requeue again. %s', count($reassign_req), $link));
			
			} catch (Exception $e) {
				$this->_getSession()
					->addError($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		
		$this->_redirect('*/*/index');
	}
}
