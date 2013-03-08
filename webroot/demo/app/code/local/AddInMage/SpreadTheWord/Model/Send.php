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

class AddInMage_SpreadTheWord_Model_Send extends AddInMage_SpreadTheWord_Model_Friends
{
	
	protected $_rule_mode;
	protected $_sender_discount;
	protected $_friends_discount;
	protected $_friends_count;
	protected $_selected_friends_count;
	protected $_personal_message;
	protected $_sender_data;
	protected $_service;
	protected $_short_discount;

	const XML_PATH_EMAIL_TEMPLATE 			= 'addinmage_spreadtheword/email/';
	const XML_PATH_EMAIL_IDENTITY 			= 'addinmage_spreadtheword/email/identity';
	const XML_PATH_RULES 					= 'addinmage_spreadtheword/behaviour/rules';
	const XML_PATH_DIRECT_SIMPLE 			= 'addinmage_spreadtheword/message/direct_message_simple';
	const XML_PATH_DIRECT_DISCOUNT 			= 'addinmage_spreadtheword/message/direct_message_discount';
	const XML_PATH_DIRECT_TWITTER_SIMPLE 	= 'addinmage_spreadtheword/message/direct_twitter_simple';
	const XML_PATH_DIRECT_TWITTER_DISCOUNT 	= 'addinmage_spreadtheword/message/direct_twitter_discount';
	
	
	protected function _getHelper()
	{
		return Mage::helper('spreadtheword');
	}

	public function getStore()
	{
		$storeId = $this->getStoreId();
		if ($storeId) {return Mage::app()->getStore($storeId);}
		return Mage::app()->getStore();
	}

	public function handleInvitation($friends, $personal_message = false)
	{
		
		$session = Mage::getSingleton('customer/session');
		$discount_data = $session->getCurrentDiscountData();
		$current_service_data = $session->getCurrentService();
		$this->_service = $current_service_data;
		$sender_data = $session->getCurrentSenderData();
		
		if ($personal_message) $this->_preparePM($personal_message);
		
		$this->_rule_mode = $discount_data ['mode'];
		$this->_sender_discount = (isset($discount_data ['sender'])) ? $discount_data ['sender'] : false;
		$this->_friends_discount = (isset($discount_data ['friends'])) ? $discount_data ['friends'] : false;
		$this->_friends_count = count($session->getFriends());
		$this->_selected_friends_count = count($friends);
		
		if ($this->_rule_mode !== 'noaction') {
			$this->_sender_discount = $this->getDiscountRule('sender');
			$this->_friends_discount = $this->getDiscountRule('friends');
		}
		
		$this->_processFriends($friends);
		$this->_prepareSenderInformation();
		
		if ($sender_data ['can_send'] && ! Mage::registry('stw_sent')) $this->sendEmailToSender();
		
		return;
	}

	protected function _processFriends($friends)
	{
		$session = Mage::getSingleton('customer/session');
		
		$current_service_data = $session->getCurrentService();
		if ($session->isLoggedIn()) {
			$user_type = 'customer';
			$user_id = $session->getCustomer()
				->getId();
		} else {
			$user_type = 'visitor';
			$visitor_data = Mage::getSingleton('core/session')->getVisitorData();
			$user_id = $visitor_data ['visitor_id'];
		}
		
		$collection = $this->getCollection()
			->addFieldToFilter('user_type', $user_type)
			->addFieldToFilter('user_id', $user_id)
			->addFieldToFilter('source_type', $current_service_data ['type'])
			->addFieldToFilter('source', $current_service_data ['code'])
			->addFieldToFilter('friend_invite_link', array ('in' => $friends));
		
		if ($current_service_data ['type'] !== 'social') $this->sendEmailInvitationToFriends($collection);
		else $this->sendDirectInvitationToFriends($collection);
	
	}

	protected function sendDirectInvitationToFriends($collection)
	{
		$session = Mage::getSingleton('customer/session');
		$current_service_data = $session->getCurrentService();
		$sender_discount = ($this->_sender_discount) ? $this->_sender_discount : false;
		$discount = ($this->_friends_discount) ? $this->_friends_discount : false;
		
		$short_discount = ($discount) ? $this->_getShortDiscount() : false;
		$this->_short_discount = $short_discount;
		$coupon = Mage::getModel('salesrule/rule')->load($discount);
		$real_discount = ($coupon->getCouponCode()) ? $coupon->getCouponCode() : false;
		
		$skipped = 0;
		$recipients = array ();
		$inv_invited = $this->_getHelper()
			->inviteAlreadyInvited();
		$date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
		
		$track_data = array ();
		
		foreach ($collection as $recipient) {
			if (! $inv_invited && $recipient->getInvited()) {
				$skipped ++;
				continue;
			} else {
				$friend_id = $recipient->getFriendId();
				$recipients [] = $friend_id;
				$track_data [$friend_id] = $recipient->getFriendInviteLink();
			}
		}
		
		$message = $this->getDirectMessage($track_data);
		
		if ($skipped == $this->_friends_count || $this->_selected_friends_count - $skipped == 0) {
			Mage::register('stw_sent', true);
			$message = $this->_getHelper()
				->__('The invitations cannot be sent to those of your friends whom you invited before.');
			Mage::getSingleton('core/session')->addNotice($message);
			return Mage::app()->getFrontController()
				->getResponse()
				->setRedirect(Mage::getUrl('*/friends'));
		
		} else {
			if (isset($current_service_data ['captcha']) && $current_service_data ['captcha']) {
				$session->setStwAfterCaptcha(array ('skipped' => $skipped, 'collection' => $collection, 'discount' => $discount, 'rule' => Mage::getStoreConfig(self::XML_PATH_RULES, Mage::app()->getStore()), 'friends_count' => $this->_friends_count, 'sender_discount' => $sender_discount, 'short_discount' => $short_discount));
				Mage::helper('spreadtheword/services_' . $current_service_data ['code'])->sendInvitations($recipients, $message, $this->_selected_friends_count);
			}
			
			Mage::helper('spreadtheword/services_' . $current_service_data ['code'])->sendInvitations($recipients, $message);
			
			$state = Mage::registry('stw_direct_data');
			
			if (! $state ['success']) {
				Mage::register('stw_sent', true);
				if ($state ['message']) $message = $state ['message'];
				else $message = $this->_getHelper()
					->__('An error occurred while sending invitations. Please try again later.');
				Mage::getSingleton('core/session')->addNotice($message);
				return Mage::app()->getFrontController()
					->getResponse()
					->setRedirect(Mage::getUrl('*/friends'));
			}
			
			if (! $inv_invited) $collection->addFieldToFilter('invited', 0);
			
			if ($state ['skipped']) {
				$collection->addFieldToFilter('friend_id', array ('nin' => $state ['skipped']));
				$skipped += count($state ['skipped']);
			}
			
			$stw_sales_data = array ();
			$stw_rule = Mage::getStoreConfig(self::XML_PATH_RULES, Mage::app()->getStore());
			$friends_update = array ('discount_rule' => $discount, 'invited_time' => $date, 'stw_rule' => $stw_rule);
			foreach ($collection as $recipient) {
				if ($recipient->getInvited()) $friends_update ['increase_reinvited'] ['ids'] [] = $recipient->getId();
				$friends_update ['update'] ['ids'] [] = $recipient->getId();
				
				$stw_sales_data [] = array ('id' => null, 'stw_id' => $recipient->getId(), 'stw_rule' => $stw_rule, 'source' => $current_service_data ['code'], 'short_discount' => $short_discount, 'real_discount' => $real_discount, 'user_type' => 'friend');
			}
			
			$info = array ();
			Mage::getModel('spreadtheword/friends')->getResource()
				->proccessUpdates($friends_update);
			
			if ($skipped) {
				$info ['success'] = $this->_selected_friends_count - $skipped;
				$info ['skipped'] = $skipped;
			} else
				$info ['success'] = $this->_selected_friends_count;
			
			$this->_prepareSenderSalesData($stw_sales_data [0] ['stw_id'], $current_service_data ['code'], $stw_rule);
			
			if ($stw_sales_data) $this->_prepareFriendsSalesData($stw_sales_data);
			
			Mage::register('invitations_data', $info);
			$session->setStwSuccess(true);
		}
	}

	protected function getDirectMessage($track_data)
	{
		$discount = ($this->_friends_discount) ? $this->_friends_discount : false;
		if ($discount) {
			$coupon = Mage::getModel('salesrule/rule')->load($discount);
			$discount_amount = (int) $coupon->getDiscountAmount();
			$code = '1' . $this->_short_discount;
			$formated_discount = $this->_getHelper()
				->formatDiscount($discount_amount, $coupon->getSimpleAction());
		}
		
		$use_template = false;
		$bad_pm = false;
		
		$pm = ($this->_personal_message) ? $this->_personal_message : false;
		if ($pm) {
			$matches = ($discount) ? array ('{{var store}}', '{{var page}}', '{{var amount}}', '{{var code}}') : array ('{{var store}}', '{{var page}}');
			foreach ($matches as $var) {
				if (! preg_match('/(?:' . $var . ')/im', $pm)) $bad_pm = true;
			}
		}
		
		$direct_message_mode = Mage::app()->getFrontController()
			->getRequest()
			->getPost('stw-direct-message');
		
		if (! $direct_message_mode || $direct_message_mode == 'template' || $bad_pm) $use_template = true;
		
		if ($this->_service ['code'] == 'twitter') {
			$templateCode = Mage::getStoreConfig(($discount) ? self::XML_PATH_DIRECT_TWITTER_DISCOUNT : self::XML_PATH_DIRECT_TWITTER_SIMPLE, Mage::app()->getStore());
		} else
			$templateCode = Mage::getStoreConfig(($discount) ? self::XML_PATH_DIRECT_DISCOUNT : self::XML_PATH_DIRECT_SIMPLE, Mage::app()->getStore());
		
		$template = Mage::getModel('core/email_template')->load($templateCode);
		$template->loadDefault($templateCode, Mage::app()->getLocale()
			->getDefaultLocale());
		$message = array ();
		$message ['subject'] = $template->getProcessedTemplateSubject(array ());
		$store_url = Mage::getUrl('spreadtheword') . '?t=0';
		$page_url = Mage::getUrl('spreadtheword') . '?t=1';
		
		if ($use_template) {
			$template_text = $template->getPreparedTemplateText();
			$template_matches = ($discount) ? array ('{{var store}}', '{{var page}}', '{{var amount}}', '{{var code}}') : array ('{{var store}}', '{{var page}}');
			if ($discount) foreach ($track_data as $friend_id => $track_url)
				$message ['body'] [$friend_id] = str_replace($template_matches, array ($store_url . $track_url, $page_url . $track_url, $formated_discount, $code), $template_text);
			
			else foreach ($track_data as $friend_id => $track_url)
				$message ['body'] [$friend_id] = str_replace($template_matches, array ($store_url . $track_url, $page_url . $track_url), $template_text);
		} else {
			if ($discount) foreach ($track_data as $friend_id => $track_url)
				$message ['body'] [$friend_id] = str_replace($matches, array ($store_url . $track_url, $page_url . $track_url, $formated_discount, $code), $pm);
			
			else foreach ($track_data as $friend_id => $track_url)
				$message ['body'] [$friend_id] = str_replace($matches, array ($store_url . $track_url, $page_url . $track_url), $pm);
		}
		return $message;
	}

	protected function sendEmailInvitationToFriends($collection)
	{
		$session = Mage::getSingleton('customer/session');
		$current_service_data = $session->getCurrentService();
		
		$template = ($this->_rule_mode == 'noaction' || $this->_rule_mode == 'action_d_sen') ? $this->getTemplate('simple_invitation') : $this->getTemplate('discount_to_friends');
		$discount = ($this->_friends_discount) ? $this->_friends_discount : false;
		$type = (! in_array($this->_rule_mode, array ('action_d_sen', 'noaction'))) ? 'discount' : 'noaction';
		$sender_data = $session->getCurrentSenderData();
		$sender_name = (isset($sender_data ['name'])) ? $sender_data ['name'] : false;
		$sender_email = (isset($sender_data ['email'])) ? $sender_data ['email'] : '';
		$pm = ($this->_personal_message) ? $this->_personal_message : false;
		
		$queue_data = array ();
		$queue_data ['template'] = $template;
		$queue_data ['type'] = $type;
		
		$short_discount = ($discount) ? $this->_getShortDiscount() : false;
		$coupon = Mage::getModel('salesrule/rule')->load($discount);
		$real_discount = ($coupon->getCouponCode()) ? $coupon->getCouponCode() : false;
		
		if ($discount) {
			$queue_data ['discount_rule'] = $discount;
			$queue_data ['simple_action'] = $coupon->getSimpleAction();
			$queue_data ['promised_amount'] = (int) $coupon->getDiscountAmount();
		}
		
		$queue_data ['source'] = $current_service_data ['code'];
		$queue_data ['store_id'] = $this->getStore()
			->getId();
		if ($sender_name) $queue_data ['sender_name'] = $sender_name;
		;
		if ($sender_email) $queue_data ['sender_email'] = $sender_email;
		if ($pm) $queue_data ['personal_message'] = $pm;
		
		$queue_model = Mage::getModel('spreadtheword/queue');
		$queued_friends = $queue_model->getCollection()
			->getColumnValues('friend_id');
		
		$queue_data_model = Mage::getModel('spreadtheword/data');
		$queue_data_exist = $queue_data_model->getCollection()
			->addFilter('template', $template)
			->addFilter('type', $type)
			->addFieldToFilter('discount_rule', ($discount) ? array ('eq' => $discount) : array ('null' => true))
			->addFilter('source', $current_service_data ['code'])
			->addFilter('personal_message', $pm)
			->addFilter('sender_name', $sender_name)
			->addFilter('sender_email', $sender_email)
			->getFirstItem();
		
		$data_id = ($queue_data_exist->hasId()) ? $queue_data_exist->getId() : $queue_data_model->setData($queue_data)
			->save()
			->getId();
		$skipped = 0;
		$already_inv = 0;
		$already_cust = 0;
		$inv_invited = $this->_getHelper()
			->inviteAlreadyInvited();
		$inv_customer = $this->_getHelper()
			->inviteCustomers();
		$date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
		$stw_rule = Mage::getStoreConfig(self::XML_PATH_RULES, Mage::app()->getStore());
		
		$stw_sales_data = array ();
		$queued_data = array ();
		$friends_update = array ('discount_rule' => $discount, 'invited_time' => $date, 'stw_rule' => $stw_rule);
		
		foreach ($collection as $recipient) {
			
			if (! $inv_invited && $recipient->getInvited()) {
				$skipped ++;
				$already_inv ++;
				continue;
			}
			if (! $inv_invited && $recipient->getAlreadyCustomer()) {
				$skipped ++;
				$already_cust ++;
				continue;
			}
			if (! in_array($recipient->getId(), $queued_friends)) {
				
				$queued_data [] = array ('friend_id' => $recipient->getId(), 'data_id' => $data_id);
				
				if ($recipient->getInvited()) $friends_update ['increase_reinvited'] ['ids'] [] = $recipient->getId();
				$friends_update ['update'] ['ids'] [] = $recipient->getId();
				
				$stw_sales_data [] = array ('id' => null, 'stw_id' => $recipient->getId(), 'stw_rule' => $stw_rule, 'source' => $current_service_data ['code'], 'short_discount' => $short_discount, 'real_discount' => $real_discount, 'user_type' => 'friend');
			} else {
				$skipped ++;
			}
		}
		
		if ($skipped == $this->_friends_count || $this->_selected_friends_count - $skipped == 0) {
			Mage::register('stw_sent', true);
			if ($already_inv || $already_cust) $message = $this->_getHelper()
				->__('The invitations cannot be sent to some of your friends for one of the following reasons: you invited them before, they are existing customers or they are in line for getting an invitation.');
			else $message = $this->_getHelper()
				->__('You invited the selected friends before and  they will receive you invitations soon. We can\'t send the same invitation twice.');
			Mage::getSingleton('core/session')->addNotice($message);
			return ($current_service_data ['type'] == 'manual') ? Mage::app()->getFrontController()
				->getResponse()
				->setRedirect(Mage::getUrl('*')) : Mage::app()->getFrontController()
				->getResponse()
				->setRedirect(Mage::getUrl('*/friends'));
		
		} else {
			$stw_id = $collection->getFirstItem()
				->getId();
			Mage::getModel('spreadtheword/friends')->getResource()
				->proccessUpdates($friends_update);
			$info = array ();
			
			if ($skipped) {
				$info ['success'] = $this->_selected_friends_count - $skipped;
				$info ['skipped'] = $skipped;
			} else
				$info ['success'] = $this->_selected_friends_count;
			
			$this->_prepareSenderSalesData($stw_id, $current_service_data ['code'], $stw_rule);
			
			if ($queued_data) Mage::getModel('spreadtheword/queue')->getResource()
				->addEmailsToQueue($queued_data);
			
			if ($stw_sales_data) $this->_prepareFriendsSalesData($stw_sales_data);
			
			Mage::register('invitations_data', $info);
			$session->setStwSuccess(true);
		}
	}

	protected function _prepareSenderSalesData($id, $service, $stw_rule)
	{
		$data = array ();
		
		$short_discount = ($this->_sender_discount) ? $this->_getShortDiscount() : false;
		$coupon = Mage::getModel('salesrule/rule')->load($this->_sender_discount);
		$real_discount = ($coupon->getCouponCode()) ? $coupon->getCouponCode() : false;
		$this->_short_discount = $short_discount;
		
		$data ['stw_id'] = $id;
		$data ['stw_rule'] = $stw_rule;
		$data ['source'] = $service;
		$data ['short_discount'] = $short_discount;
		$data ['real_discount'] = $real_discount;
		$data ['user_type'] = 'sender';
		
		$stw_sales_model = Mage::getModel('spreadtheword/sales');
		$stw_sales_id = $stw_sales_model->setData($data)
			->save()
			->getId();
		Mage::getSingleton('customer/session')->setStwSalesId($stw_sales_id);
		Mage::helper('spreadtheword')->refreshStwCustomerId($stw_sales_id);
	}

	protected function _prepareFriendsSalesData($data)
	{
		Mage::getModel('spreadtheword/sales')->getResource()
			->addFriendsToSalesTracker($data);
	}

	protected function sendEmailToSender()
	{
		$storeId = $this->getStore()
			->getId();
		$vars = array ();
		
		$send_ref = $this->_getHelper()
			->getSendEmailReFactoredFlag();
		
		if ($send_ref) {
			
			$mailer = Mage::getModel('core/email_template_mailer');
			$emailInfo = Mage::getModel('core/email_info');
			$emailInfo->addTo($this->_sender_data ['sender_data'] ['email'], $this->_sender_data ['sender_data'] ['name']);
			
			$mailer->addEmailInfo($emailInfo);
		} 

		else {
			
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$mailTemplate = Mage::getModel('core/email_template');
			
			$sendTo = array (array ('email' => $this->_sender_data ['sender_data'] ['email'], 'name' => $this->_sender_data ['sender_data'] ['name']));
		}
		
		$vars ['name'] = $this->_sender_data ['sender_data'] ['name'];
		if ($this->_sender_data ['service_data'] ['type'] !== 'manual') $vars ['not_manual'] = true;
		$vars ['service'] = $this->_sender_data ['service_data'] ['service'];
		$vars ['imported'] = $this->_friends_count;
		$vars ['checked'] = $this->_selected_friends_count;
		if (isset($this->_sender_data ['invitations_data'] ['skipped'])) $vars ['skipped'] = $this->_sender_data ['invitations_data'] ['skipped'];
		$vars ['invited'] = $this->_sender_data ['invitations_data'] ['success'];
		
		if ($this->_sender_discount) {
			$vars ['amount'] = $this->_sender_data ['discount_data'] ['amount'];
			$vars ['code'] = $this->_sender_data ['discount_data'] ['code'];
			if (isset($this->_sender_data ['discount_data'] ['discount_have_conditions'])) $vars ['discount_have_conditions'] = true;
			
			if (isset($this->_sender_data ['discount_data'] ['coupon_expire'])) $vars ['coupon_expire'] = $this->_sender_data ['discount_data'] ['coupon_expire'];
			$template = $this->getTemplate('discount_to_sender');
		} 

		else
			$template = $this->getTemplate('sender_confirmation');
		
		if ($send_ref) {
			$mailer->setTemplateId($template);
			$mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
			$mailer->setStoreId($storeId);
			$mailer->setTemplateParams($vars);
			$mailer->send();
		} else {
			foreach ($sendTo as $recipient) {
				$mailTemplate->setDesignConfig(array ('area' => 'frontend', 'store' => $storeId))
					->sendTransactional($template, Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId), $recipient ['email'], $recipient ['name'], $vars);
			}
		}
	}

	public function checkForDateLimit($rule)
	{
		$discount = Mage::getModel('salesrule/rule')->load($rule);
		
		$limit = $discount->getToDate();
		
		if ($limit) {
			list ($exp_year, $exp_month, $exp_day) = explode('-', $limit);
			$expiring_timestamp = mktime(23, 59, 59, $exp_month, $exp_day, $exp_year);
			$notice_expire_date = new Zend_Date($expiring_timestamp);
			$date = $notice_expire_date->toString(Mage::app()->getLocale()
				->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
			return $date;
		}
		return false;
	}

	public function checkForCondition($rule)
	{
		$discount = Mage::getModel('salesrule/rule')->load($rule);
		
		$conditions = unserialize($discount->getConditionsSerialized());
		$actions = unserialize($discount->getActionsSerialized());
		
		if ((isset($conditions ['conditions']) && ! empty($conditions ['conditions'])) || (isset($actions ['conditions']) && ! empty($actions ['conditions']))) return true;
		
		return false;
	}

	protected function _getEmails($configPath)
	{
		$data = Mage::getStoreConfig($configPath, Mage::app()->getStore()
			->getId());
		if (! empty($data)) {return explode(',', $data);}
		return false;
	}

	public function conditionToText($discount_rule)
	{
		$conditions = Mage::getModel('salesrule/rule')->load($discount_rule)
			->getConditions()
			->asStringRecursive();
		echo nl2br($conditions);
		
		$actions = Mage::getModel('salesrule/rule')->load($discount_rule)
			->getActions()
			->asStringRecursive();
		echo '<br/><br/>' . nl2br($actions);
	
	}

	protected function getTemplate($temp)
	{
		return Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE . $temp, Mage::app()->getStore());
	}

	protected function getDiscountRule($for)
	{
		if ($for == 'sender') {
			if ($this->_sender_discount && $this->_sender_discount ['mode'] !== 'fixed') return ($this->_sender_discount ['mode'] == 'dynamic') ? $this->calculateDiscount($this->_sender_discount ['data'], 'dynamic', 'sender') : $this->calculateDiscount($this->_sender_discount, 'dynamic_levels', 'sender');
			else return $this->_sender_discount ['data'];
		} else {
			if ($this->_friends_discount && $this->_friends_discount ['mode'] !== 'fixed') return ($this->_friends_discount ['mode'] == 'dynamic') ? $this->calculateDiscount($this->_friends_discount ['data'], 'dynamic', 'friends') : $this->calculateDiscount($this->_friends_discount, 'dynamic_levels', 'friends');
			else return $this->_friends_discount ['data'];
		}
	}

	public function calculateDiscount($discount_data, $discount_mode, $for = null)
	{
		$session = Mage::getSingleton('customer/session');
		
		if ($discount_mode == 'dynamic') {
			$discount_rule = Mage::getModel('salesrule/rule')->load($discount_data);
			$max_discount = (int) $discount_rule->getDiscountAmount();
			$discount_simple_action = $discount_rule->getSimpleAction();
			$calculated_discount = floor(($this->_selected_friends_count / $this->_friends_count) * $max_discount);
			
			if ($calculated_discount == 0) {
				
				$message = ($for == 'sender') ? $this->_getHelper()
					->__('According to the number of friends you selected, your discount is %s. Please select more friends to get a discount!', $this->_getHelper()
					->formatDiscount(0, $discount_simple_action)) : $this->_getHelper()
					->__('According to the number of friends you selected, the discount your friends will get is %s. Please select more friends to send them a discount!', $this->_getHelper()
					->formatDiscount(0, $discount_simple_action));
				Mage::getSingleton('core/session')->addNotice($message);
				die(Mage::app()->getFrontController()
					->getResponse()
					->setRedirect(Mage::getUrl('*/friends')));
			
			}
			
			if ($calculated_discount == $max_discount) return $discount_data;
			
			else {
				$model = Mage::getModel('salesrule/rule');
				$max = $model->load($discount_data);
				$appendix = $this->_getHelper()
					->__('copy with discount amount %s for rule id #%d', $this->_getHelper()
					->formatDiscount($calculated_discount, $max->getSimpleAction()), $max->getRuleId());
				
				$max_name = $max->getName();
				$pattern = '/\[.+\]/';
				$generated = preg_match($pattern, $max_name);
				
				if ($generated) $check_name = preg_replace($pattern, '[' . quotemeta($appendix) . ']', $max_name);
				else $check_name = $max_name . ' [' . $appendix . ']';
				$check_for_exist = ($generated) ? stripslashes($check_name) : $check_name;
				
				$discount = $model->getCollection()
					->addFilter('name', $check_for_exist)
					->getFirstItem();
				
				if ($discount->getRuleId()) return $discount->getRuleId();
				
				else {
					$new_level = $this->_duplicate($max, $calculated_discount, $appendix);
					return $new_level;
				}
			}
		}
		
		if ($discount_mode == 'dynamic_levels') {
			$selected_percent = floor(($this->_selected_friends_count / $this->_friends_count) * 100);
			if ($selected_percent < $discount_data ['data'] [0] ['percent'] || $selected_percent == 0) {
				$zero_formated = $this->_getHelper()
					->formatDiscountByType(0, $discount_data ['amount_type']);
				$message = ($for == 'sender') ? $this->_getHelper()
					->__('According to the number of friends you selected, your discount is %s. Please select more friends to get a discount!', $zero_formated) : $this->_getHelper()
					->__('According to the number of friends you selected, the discount your friends will get is %s. Please select more friends to send them a discount!', $zero_formated);
				Mage::getSingleton('core/session')->addNotice($message);
				die(Mage::app()->getFrontController()
					->getResponse()
					->setRedirect(Mage::getUrl('*/friends')));
			}
			foreach ($discount_data ['data'] as $level) {
				if ($selected_percent >= $level ['percent']) $discount_level = (isset($discount_data ['default'])) ? $level ['rule'] : $level ['discount_rule'];
			}
			return $discount_level;
		}
	}

	protected function _duplicate($original, $calculated_discount, $appendix)
	{
		$new_rule = $original->getData();
		unset($new_rule ['rule_id']);
		
		$name = $original->getName();
		$pattern = '/\[.+\]/';
		$generated = preg_match($pattern, $name);
		
		if ($generated) $new_name = preg_replace($pattern, '[' . quotemeta($appendix) . ']', $name);
		else $new_name = $name . ' [' . $appendix . ']';
		
		$new_rule ['name'] = ($generated) ? stripslashes($new_name) : $new_name;
		$new_rule ['coupon_code'] = $this->generateCode();
		$new_rule ['discount_amount'] = $calculated_discount;
		
		$model = Mage::getModel('salesrule/rule')->setData($new_rule);
		$model->save();
		return $model->getRuleId();
	}

	public function generateCode()
	{
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$lengthMin = 6;
		$lengthMax = 10;
		$length = rand($lengthMin, $lengthMax);
		$result = '';
		$indexMax = strlen($alphabet) - 1;
		for($i = 0; $i < $length; $i ++) {
			$index = rand(0, $indexMax);
			$result .= $alphabet {$index};
		}
		if (Mage::getModel('salesrule/rule')->getCollection()
			->addFilter('code', $result)
			->getFirstItem()
			->getRuleId()) return $this->generateCode();
		
		else return $result;
	
	}

	protected function _preparePM($message)
	{
		$pm = (strip_tags(trim($message)));
		if ($this->_getHelper()
			->getMaxChars()) {
			$count = iconv_strlen($pm, 'UTF-8');
			if ($count > $this->_getHelper()
				->getMaxChars()) $pm = substr($pm, 0, $this->_getHelper()
				->getMaxChars()) . ' ...';
		}
		$this->_personal_message = $pm;
	}

	protected function _prepareSenderInformation()
	{
		$session = Mage::getSingleton('customer/session');
		$data = array ();
		
		$data ['sender_data'] = $session->getCurrentSenderData();
		$data ['service_data'] = $session->getCurrentService();
		$data ['invitations_data'] = Mage::registry('invitations_data');
		$data ['invitations_data'] ['imported'] = $this->_friends_count;
		$data ['invitations_data'] ['checked'] = $this->_selected_friends_count;
		
		if ($this->_sender_discount) {
			$sender_discount_data = array ();
			
			$coupon = Mage::getModel('salesrule/rule')->load($this->_sender_discount);
			$sender_discount_data ['code'] = '0' . $this->_short_discount;
			
			$sender_discount_data ['amount'] = $this->_getHelper()
				->formatDiscount($coupon->getDiscountAmount(), $coupon->getSimpleAction());
			
			if ($this->checkForCondition($this->_sender_discount)) $sender_discount_data ['discount_have_conditions'] = true;
			
			$coupon_expire = $this->checkForDateLimit($this->_sender_discount);
			if ($coupon_expire) $sender_discount_data ['coupon_expire'] = $coupon_expire;
			
			$data ['discount_data'] = $sender_discount_data;
		}
		$this->_sender_data = ($data) ? $data : false;
		$session->setStwData($this->_sender_data);
		return;
	}

	public function _afterCaptcha($failed, $selected_friends_count)
	{
		$session = Mage::getSingleton('customer/session');
		$data = $session->getStwAfterCaptcha();
		$current_service_data = $session->getCurrentService();
		
		$collection = $data ['collection'];
		$skipped = $data ['skipped'];
		
		$discount = $data ['discount'];
		
		$short_discount = $data ['short_discount'];
		$coupon = Mage::getModel('salesrule/rule')->load($discount);
		$real_discount = ($coupon->getCouponCode()) ? $coupon->getCouponCode() : false;
		
		$rule = $data ['rule'];
		$this->_friends_count = $data ['friends_count'];
		$this->_sender_discount = $data ['sender_discount'];
		
		$this->_selected_friends_count = $selected_friends_count;
		
		$inv_invited = $this->_getHelper()
			->inviteAlreadyInvited();
		$date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
		if (! $inv_invited) $collection->addFieldToFilter('invited', 0);
		
		if ($failed) {
			$collection->addFieldToFilter('friend_id', array ('nin' => $failed));
			$skipped += count($failed);
		}
		$stw_sales_data = array ();
		$friends_update = array ('discount_rule' => $discount, 'invited_time' => $date, 'stw_rule' => $rule);
		foreach ($collection as $recipient) {
			if ($recipient->getInvited()) $friends_update ['increase_reinvited'] ['ids'] [] = $recipient->getId();
			$friends_update ['update'] ['ids'] [] = $recipient->getId();
			
			$stw_sales_data [] = array ('id' => null, 'stw_id' => $recipient->getId(), 'stw_rule' => $rule, 'source' => $current_service_data ['code'], 'short_discount' => $short_discount, 'real_discount' => $real_discount, 'user_type' => 'friend');
		}
		$info = array ();
		Mage::getModel('spreadtheword/friends')->getResource()
			->proccessUpdates($friends_update);
		if ($skipped) {
			$info ['success'] = $selected_friends_count - $skipped;
			$info ['skipped'] = $skipped;
		} else
			$info ['success'] = $selected_friends_count;
		
		$this->_prepareSenderSalesData($stw_sales_data [0] ['stw_id'], $current_service_data ['code'], $rule);
		if ($stw_sales_data) $this->_prepareFriendsSalesData($stw_sales_data);
		
		Mage::register('invitations_data', $info);
		$session->setStwSuccess(true);
		$this->_prepareSenderInformation();
		
		die(Mage::app()->getFrontController()
			->getResponse()
			->setRedirect(Mage::getUrl('*/success')));
	}

	protected function _getShortDiscount()
	{
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$lengthMin = 6;
		$lengthMax = 6;
		$length = rand($lengthMin, $lengthMax);
		$result = '';
		$indexMax = strlen($alphabet) - 1;
		for($i = 0; $i < $length; $i ++) {
			$index = rand(0, $indexMax);
			$result .= $alphabet {$index};
		}
		if (Mage::getModel('spreadtheword/sales')->getResource()
			->testShortDiscount($result)) return $this->_getShortDiscount();
		
		else return $result;
	}
}