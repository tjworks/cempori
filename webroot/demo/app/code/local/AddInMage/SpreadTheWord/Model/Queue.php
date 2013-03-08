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

class AddInMage_SpreadTheWord_Model_Queue extends Mage_Core_Model_Abstract
{

	const STATUS_IN_QUEUE 					= 0;
	const STATUS_SENT 						= 1;
	const STATUS_SENDING 					= 2;
	const STATUS_FAILED 					= 3;
	const STATUS_PAUSED 					= 4;
	const XML_PATH_CRON_MODE 				= 'addinmage_spreadtheword/email_sending/cron_mode';
	const XML_PATH_SEND_PARTIALLY 			= 'addinmage_spreadtheword/email_sending/partially';
	const XML_PATH_BY_PERIOD 				= 'addinmage_spreadtheword/email_sending/item_by_period';
	const XML_PATH_GENERATE_ACCOUNT 		= 'addinmage_spreadtheword/behaviour/account';
	const XML_PATH_NEW_ACCOUNTS_WEB 		= 'addinmage_spreadtheword/behaviour/account_web';
	const XML_PATH_NEW_ACCOUNT_TEMPLATE 	= 'customer/create_account/email_template';
	
	protected $_discount;
	protected $_discount_problem;
	
	protected $_updater;
	protected $_delay;
	protected $_dimension;
	
	protected $_real_discount_id;
	protected $_short_discount;
	
	protected $_failed = 0;

	public function _construct()
	{
		parent::_construct();
		$this->_init('spreadtheword/queue');
	}

	public function sendNow($delay_data = null, $pb = false)
	{
		if ($delay_data) {
			$this->_delay = $delay_data ['time'];
			$this->_dimension = $delay_data ['dimension'];
		}
		$collection = $this->getCollection();
		$collection->addFilter('status', self::STATUS_IN_QUEUE);
		$col_count = $collection->count();
		$use_progress = ($pb) ? true : false;
		$this->_updater = ($use_progress) ? new Zend_ProgressBar(new Zend_ProgressBar_Adapter_JsPush(array ('updateMethodName' => 'stwUpdate', 'finishMethodName' => 'stwFinish')), 0, $col_count) : false;
		if ($use_progress) {
			$this->_updater->next(null, array ('status' => $this->_getHelper()
				->__('Getting information about the queue')));
			sleep(2);
		}
		try {
			if ($col_count) {
				if ($use_progress) {
					$this->_updater->next(null, array ('status' => $this->_getHelper()
						->__('Locking the queue for sending %d message(s)', $collection->count())));
					sleep(2);
				}
				Mage::getModel('spreadtheword/queue')->getResource()
					->changeStatus(false, self::STATUS_SENDING, array (self::STATUS_IN_QUEUE));
				if ($use_progress) $this->_updater->next(null, array ('status' => $this->_getHelper()
					->__('Queue is locked, sending process is started')));
				
				foreach ($collection as $item) {
					$this->sendEmail($item);
				}
				
				if ($use_progress) {
					sleep(2);
					$this->_updater->next(null, array ('status' => $this->_getHelper()
						->__('Checking data integrity')));
					sleep(2);
				}
				$this->_finish();
			} else {
				if ($use_progress) {
					$this->_updater->next(null, array ('status' => $this->_getHelper()
						->__('No messages ready for sending are found')));
					$this->_updater->getAdapter()
						->setOptions(array ('finishMethodName' => 'stwNoJob'));
				}
			
			}
			if ($use_progress) {
				$this->_updater->next(null, array ('status' => $this->_getHelper()
					->__('The emails are sent successfully')));
				sleep(2);
				$this->_updater->finish();
			}
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()
				->addError($e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			if ($use_progress) {
				sleep(2);
				$this->_handleException();
			}
		} catch (Exception $e) {
			$this->_getSession()
				->addError($e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			if ($use_progress) {
				sleep(2);
				$this->_handleException();
			}
		}
	}

	public function scheduledSend()
	{
		
		$cron_mode = Mage::getStoreConfig(self::XML_PATH_CRON_MODE);
		$use_partially_sending = Mage::getStoreConfig(self::XML_PATH_SEND_PARTIALLY);
		$items_by_period = Mage::getStoreConfig(self::XML_PATH_BY_PERIOD);
		$collection = $this->getCollection();
		$collection->addFilter('status', self::STATUS_IN_QUEUE);
		if ($cron_mode == 'cron_period' && $use_partially_sending && ! empty($items_by_period)) $collection->setPageSize($items_by_period);
		if ($collection->count()) {
			$collection->setDataToAll('status', self::STATUS_SENDING)
				->save();
			foreach ($collection as $item) {
				$this->sendEmail($item);
			}
			$this->_finish();
		}
	}

	public function sendEmail($item)
	{
		if ($this->_delay) $this->sleepDelay();
		
		$data = Mage::getModel('spreadtheword/data')->getCollection()
			->addFilter('id', $item->getDataId())
			->getFirstItem();
		$info = Mage::getModel('spreadtheword/friends')->getCollection()
			->addFilter('id', $item->getFriendId())
			->getFirstItem();
		
		if (! $this->_lifetime_tracking) $this->_lifetime_tracking = Mage::getStoreConfig('addinmage_spreadtheword/tracking/lifetime_tracking', Mage::app()->getStore());
		
		$sales_id = false;
		if ($this->_lifetime_tracking) $sales_id = Mage::getModel('spreadtheword/sales')->getResource()
			->getIdByUserId($item->getFriendId());
		
		if (! $data->hasId() || ! $info->hasId()) return $this;
		if (! $this->_validateResource($data, $info)) return $this;
		
		if ($data->getType() == 'discount' && $data->getDiscountRule()) {
			$is_problem = $this->_isProblemDiscount($data->getDiscountRule());
			if ($is_problem) {
				$this->_processFailed($item, true);
				return $this;
			}
		}
		
		$translate = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$mailTemplate = Mage::getModel('core/email_template');
		
		$storeId = $data->getStoreId();
		
		$vars = array ();
		$vars ['sender'] = $data->getSenderName();
		$vars ['sender_email'] = $data->getSenderEmail();
		$vars ['track_link'] = $info->getFriendInviteLink();
		
		if ($data->getPersonalMessage()) $vars ['pm'] = $data->getPersonalMessage();
		
		if ($data->getType() == 'discount' && $data->getDiscountRule()) {
			$rule = $data->getDiscountRule();
			$coupon = Mage::getModel('salesrule/rule')->load($rule);
			
			if (! $this->_real_discount_id || $this->_real_discount_id != $rule) {
				$this->_real_discount_id = $rule;
				$this->_short_discount = Mage::getModel('spreadtheword/sales')->getResource()
					->getShortDiscount($coupon->getCouponCode(), array ('friend'));
			}
			
			$discount = ($this->_short_discount) ? '1' . $this->_short_discount : $coupon->getCouponCode();
			$vars ['amount'] = $this->_getHelper()
				->formatDiscount($coupon->getDiscountAmount(), $coupon->getSimpleAction());
			$vars ['code'] = $discount;
			
			if (Mage::getModel('spreadtheword/send')->checkForCondition($rule)) $vars ['discount_have_conditions'] = true;
			
			$coupon_expire = Mage::getModel('spreadtheword/send')->checkForDateLimit($rule);
			if ($coupon_expire) $vars ['coupon_expire'] = $coupon_expire;
		}
		
		$successSend = $mailTemplate->setDesignConfig(array ('area' => 'frontend', 'store' => $storeId))
			->sendTransactional($data->getTemplate(), Mage::getStoreConfig('addinmage_spreadtheword/email/identity', $storeId), $info->getFriendId(), $info->getFriendName(), $vars);
		
		if ($successSend->getSentSuccess()) {
			$this->_setInvitedStatus(true, $item->getFriendId());
			$item->delete();
			if ($this->_updater) $this->_updater->next(1, array ('status' => $this->_getHelper()
				->__('The invitation is sent')));
			
			if (Mage::getStoreConfig(self::XML_PATH_GENERATE_ACCOUNT)) {
				$full_name = explode(' ', $info->getFriendName());
				if (count($full_name) == 2) $this->_generateAccount($full_name, $info->getFriendId(), $sales_id);
			}
		} else {
			$this->_processFailed($item, false);
		}
		return $this;
	}

	protected function _generateAccount($full_name, $email, $sales_id = false)
	{
		
		$customer = Mage::getModel('customer/customer');
		$customer->setWebsiteId(Mage::getStoreConfig(self::XML_PATH_NEW_ACCOUNTS_WEB));
		$customer->loadByEmail($email);
		
		if (! $customer->getId()) {
			$customer->setWebsiteId(Mage::getStoreConfig(self::XML_PATH_NEW_ACCOUNTS_WEB));
			$customer->setEmail($email);
			$customer->setFirstname($full_name [0]);
			$customer->setLastname($full_name [1]);
			$customer->setPassword($this->_generatePassword());
			$customer->setConfirmation(null);
			if ($sales_id) $customer->setStwCustomerId($sales_id);
			$customer->save();
			$customer->sendNewAccountEmail('registered');
			if ($this->_updater) $this->_updater->next(null, array ('status' => $this->_getHelper()
				->__('Generating account and sending login information')));
		} else
			return;
		return;
	}

	protected function _generatePassword()
	{
		$alphabet = ($this->getAlphabet() ? $this->getAlphabet() : 'abcdefghijklmnopqrstuvwxyz0123456789');
		$lengthMin = ($this->getLengthMin() ? $this->getLengthMin() : 6);
		$lengthMax = ($this->getLengthMax() ? $this->getLengthMax() : 8);
		$length = ($this->getLength() ? $this->getLength() : rand($lengthMin, $lengthMax));
		$result = '';
		$indexMax = strlen($alphabet) - 1;
		for($i = 0; $i < $length; $i ++) {
			$index = rand(0, $indexMax);
			$result .= $alphabet {$index};
		}
		return $result;
	}

	protected function _processFailed($item, $bad_discount = false)
	{
		$problems = Mage::getModel('spreadtheword/problems');
		
		$item->setStatus(self::STATUS_FAILED)
			->save();
		$this->_setInvitedStatus(false, $item->getFriendId());
		
		$problem = $problems->getCollection()
			->getItemByColumnValue('queue_id', $item->getId());
		
		$code = ($bad_discount) ? $this->_discount_problem : AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG;
		
		if ($problem) {
			$problem->setErrorCode($code);
			$problem->setFailedAt(now())
				->save();
		} else {
			$problems->addRecipientData($item->getId())
				->addTimeOfFailure()
				->addErrorData($code)
				->save();
		}
		if ($this->_updater) {
			$this->_failed += 1;
			
			switch ($code) {
			case AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG:
			$notification = $this->_getHelper()
				->__('System exception (see exception.log)');
			break;
			case AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_DSC:
			$notification = $this->_getHelper()
				->__('Assigned discount is expired');
			break;
			case AddInMage_SpreadTheWord_Model_Problems::ERROR_DSC_DLT:
			$notification = $this->_getHelper()
				->__('Assigned discount does not exist');
			break;
			}
			$this->_updater->next(1, array ('failed' => $this->_failed, 'error' => $notification));
		}
		return $this;
	}

	protected function _validateResource($data, $info)
	{
		if (! $data->getTemplate() || ! $data->getType() || ! $data->getSenderName() || ! $data->getSenderEmail() || ! $info->getFriendId() || ! $info->getFriendName()) return false;
		return true;
	}

	protected function _finish()
	{
		Mage::getModel('spreadtheword/data')->checkQueuedData();
	}

	public function queueFailed($from_problem_contr = false)
	{
		$msgs = $this->getCollection();
		$can_be_queued = array ();
		$reassign_req = array ();
		
		$joined_tables = $this->getCollection()
			->join('problems', 'main_table.id = problems.queue_id', array ('main_table.id', 'main_table.status', 'problems.queue_id', 'problems.error_code'))
			->addFieldToFilter('main_table.status', array ('eq' => self::STATUS_FAILED));
		
		foreach ($joined_tables as $item) {
			if ($item->getErrorCode() == AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG) $can_be_queued [] = $item->getId();
			
			if ($item->getErrorCode() != AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG) $reassign_req [] = $item->getId();
		}
		
		if ($can_be_queued) {
			$result = Mage::getModel('spreadtheword/queue')->getResource()
				->changeStatus($can_be_queued, AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE, array (AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED));
			if ($result) $this->_getSession()
				->addSuccess($this->_getHelper()
				->__('%d message(s) were successfully queued.', $result));
		}
		
		$have_access = Mage::getSingleton('admin/session')->isAllowed('addinmage/invitation_emails/problems');
		$link = ($have_access && ! $from_problem_contr) ? '<a href=' . Mage::helper('adminhtml')->getUrl('spreadtheword/adminhtml_problems') . '>' . $this->_getHelper()
			->__('Please follow this link') . '</a>.' : '';
		if ($reassign_req) $this->_getSession()
			->addNotice($this->_getHelper()
			->__('Total of %d  message(s) could not be processed. It is necessary to reassign discounts manually. %s', count($reassign_req), $link));
	
	}

	protected function _getSession()
	{
		return Mage::getSingleton('adminhtml/session');
	}

	public function _setInvitedStatus($status, $friend_id = null)
	{
		if ($friend_id) Mage::getModel('spreadtheword/friends')->load($friend_id)
			->setInvited($status)
			->setInvited($status)
			->save();
		else Mage::getModel('spreadtheword/friends')->load($this->getFriendId())
			->setInvited($status)
			->save();
	}

	protected function sleepDelay()
	{
		if ($this->_dimension == 'sec') sleep($this->_delay);
		else usleep($this->_delay);
	}

	protected function _getHelper()
	{
		return Mage::helper('spreadtheword');
	}

	protected function _isProblemDiscount($rule)
	{
		if ($this->_discount && $this->_discount_problem && $rule == $this->_discount) {
			return $this->_discount_problem;
		} else {
			$discount_rule = Mage::getModel('salesrule/rule')->load($rule);
			if (! $discount_rule->hasRuleId()) {
				$this->_discount = $rule;
				$this->_discount_problem = AddInMage_SpreadTheWord_Model_Problems::ERROR_DSC_DLT;
				return $this->_discount_problem;
			} elseif ($discount_rule->hasToDate()) {
				list ($month, $day, $year) = explode('/', date('m/d/Y', Mage::app()->getLocale()
					->storeTimeStamp()));
				$now = mktime(23, 59, 59, $month, $day, $year);
				list ($exp_year, $exp_month, $exp_day) = explode('-', $discount_rule->getToDate());
				$expiring_timestamp = mktime(23, 59, 59, $exp_month, $exp_day, $exp_year);
				$discount_expired = ($expiring_timestamp < $now) ? true : false;
				if ($discount_expired) {
					$this->_discount = $rule;
					$this->_discount_problem = AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_DSC;
					return $this->_discount_problem;
				}
			}
			return false;
		}
	}

	protected function _handleException()
	{
		$this->_updater->next(null, array ('status' => $this->_getHelper()
			->__('An error occurred while sending emails')));
		$this->_updater->next(null, array ('status' => $this->_getHelper()
			->__('An error occurred while sending emails')));
		sleep(4);
		$this->_updater->getAdapter()
			->setOptions(array ('finishMethodName' => 'stwExeption'));
		$this->_updater->next(null, array ('status' => $this->_getHelper()
			->__('Please refer to exception.log file')));
		$this->_updater->finish();
	}
}