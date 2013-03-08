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


class AddInMage_SpreadTheWord_Block_Elements_Success_Success extends Mage_Core_Block_Template
{

	const XML_PATH_EMAIL 				= 'addinmage_spreadtheword/advanced/notification_email';
	const XML_PATH_EMAIL_COPY_TO 		= 'addinmage_spreadtheword/advanced/copy_to';
	const XML_PATH_EMAIL_COPY_METHOD 	= 'addinmage_spreadtheword/advanced/copy_method';
	const XML_PATH_EMAIL_TEMPLATE 		= 'addinmage_spreadtheword/advanced/notification_template';
	const XML_PATH_EMAIL_IDENTITY 		= 'addinmage_spreadtheword/advanced/identity';
	
	protected $_stw_data;

	protected function _prepareLayout()
	{
		$session = Mage::getSingleton('customer/session');
		$this->_stw_data = $session->getStwData();
		$this->notify();
		return parent::_prepareLayout();
	}

	public function notify()
	{
		if (Mage::getStoreConfig('addinmage_spreadtheword/advanced/notifications', Mage::app()->getStore())) {
			if (! Mage::registry('stw_notification')) switch (Mage::getStoreConfig('addinmage_spreadtheword/advanced/notification_method', Mage::app()->getStore())) {
			case 'inbox':
				$this->inboxNotification();
				break;
				case 'email':
				$this->emailNotification();
				break;
				default:
				$this->inboxNotification();
				break;
			}
		}
	}

	protected function inboxNotification()
	{
		$stats = $this->getStats();
		$service = $this->getServiceName();
		$skipped = $this->isSomeoneSkipped();
		if (isset($this->_stw_data ['discount_data'])) $discount_amount = $this->getDiscountAmount();
		
		$title = $this->__('%d friends were invited via %s.', $stats ['success'], $service);
		$_service_info = '<b>' . $this->__('Service / Tool: ') . '</b>';
		$description = $_service_info . $service . "<br/>";
		
		if (isset($this->_stw_data ['discount_data'])) {
			$_discount_info = '<b>' . $this->__('Discount Amount: ') . '</b>';
			$description = $_discount_info . $discount_amount . "<br/>";
		}
		
		$_stats_info = '<b>' . $this->__('Stats: ') . '</b>';
		
		$_stats_info .= $this->__('%d imported / ', $stats ['imported']);
		if (! $this->isManualInvitation()) $_stats_info .= $this->__('%d selected / ', $stats ['checked']);
		if (isset($stats ['skipped'])) $_stats_info .= $this->__('%d skipped / ', $stats ['skipped']);
		$_stats_info .= $this->__('%d invited', $stats ['success']);
		$description .= $_stats_info;
		
		Mage::getModel('adminnotification/inbox')->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE)
			->setTitle($title)
			->setDateAdded(gmdate('Y-m-d H:i:s'))
			->setUrl(false)
			->setDescription($description)
			->save();
		
		Mage::register('stw_notification', true);
	}

	public function getStore()
	{
		$storeId = $this->getStoreId();
		if ($storeId) {return Mage::app()->getStore($storeId);}
		return Mage::app()->getStore();
	}

	protected function emailNotification()
	{
		$storeId = $this->getStore()
			->getId();
		$vars = array ();
		
		$send_ref = $this->_getHelper()
			->getSendEmailReFactoredFlag();
		
		$template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, Mage::app()->getStore());
		$copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
		$copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
		$notif_email = Mage::getStoreConfig(self::XML_PATH_EMAIL, Mage::app()->getStore());
		$validator = new Zend_Validate_EmailAddress();
		
		if (! $notif_email || ! $validator->isValid($notif_email)) {
			Mage::register('stw_notification', true);
			return;
		}
		
		if ($send_ref) {
			
			$mailer = Mage::getModel('core/email_template_mailer');
			$emailInfo = Mage::getModel('core/email_info');
			$emailInfo->addTo($notif_email, null);
			
			$mailer->addEmailInfo($emailInfo);
			
			if ($copyTo && $copyMethod == 'bcc') {
				foreach ($copyTo as $email) {
					$emailInfo->addBcc($email);
				}
			}
			
			if ($copyTo && $copyMethod == 'copy') {
				foreach ($copyTo as $email) {
					$emailInfo = Mage::getModel('core/email_info');
					$emailInfo->addTo($email);
					$mailer->addEmailInfo($emailInfo);
				}
			}
		} else {
			
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$mailTemplate = Mage::getModel('core/email_template');
			
			if ($copyTo && $copyMethod == 'bcc') {
				foreach ($copyTo as $email) {
					$mailTemplate->addBcc($email);
				}
			}
			
			$sendTo = array (array ('email' => $notif_email, 'name' => null));
			
			if ($copyTo && $copyMethod == 'copy') {
				foreach ($copyTo as $email) {
					$sendTo [] = array ('email' => $email, 'name' => null);
				}
			}
		}
		
		$stats = $this->getStats();
		$service = $this->getServiceName();
		$skipped = $this->isSomeoneSkipped();
		if (isset($this->_stw_data ['discount_data'])) $discount_amount = $this->getDiscountAmount();
		
		$title = $this->__('%d friends were invited via %s', $stats ['success'], $service);
		
		$vars ['info'] = $title;
		$_service_info = $this->__('Service / Tool: ');
		
		$vars ['service_info'] = $_service_info . $service;
		
		if (isset($this->_stw_data ['discount_data'])) {
			$_discount_info = $this->__('Discount Amount: ');
			$vars ['discount_info'] = $_discount_info . $discount_amount;
		}
		
		$_stats_info = $this->__('Stats: ');
		$_stats_info .= $this->__('%d imported / ', $stats ['imported']);
		if (! $this->isManualInvitation()) $_stats_info .= $this->__('%d selected / ', $stats ['checked']);
		if (isset($stats ['skipped'])) $_stats_info .= $this->__('%d skipped / ', $stats ['skipped']);
		$_stats_info .= $this->__('%d invited', $stats ['success']);
		$vars ['stats_info'] = $_stats_info;
		
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
		
		Mage::register('stw_notification', true);
	}

	protected function _getEmails($configPath)
	{
		$data = Mage::getStoreConfig($configPath, Mage::app()->getStore()
			->getId());
		if (! empty($data)) {return explode(',', $data);}
		return false;
	}

	protected function _getHelper()
	{
		return Mage::helper('spreadtheword');
	}

	public function getSkippedReason()
	{
		$session = Mage::getSingleton('customer/session');
		return ($session->getStwSkippedReason()) ? $session->getStwSkippedReason() : false;
	}

	public function canShowDiscountContainer()
	{
		return (isset($this->_stw_data ['discount_data'])) ? true : false;
	}

	public function getCouponCode()
	{
		return $this->_stw_data ['discount_data'] ['code'];
	}

	public function getDiscountAmount()
	{
		return $this->_stw_data ['discount_data'] ['amount'];
	}

	public function isSent()
	{
		return $this->_stw_data ['sender_data'] ['can_send'];
	}

	public function getExpire()
	{
		return (isset($this->_stw_data ['discount_data'] ['coupon_expire'])) ? $this->_stw_data ['discount_data'] ['coupon_expire'] : false;
	}

	public function canExpire()
	{
		return (isset($this->_stw_data ['discount_data'] ['coupon_expire'])) ? true : false;
	}

	public function getServiceName()
	{
		return $this->_stw_data ['service_data'] ['service'];
	}

	public function isManualInvitation()
	{
		return ($this->_stw_data ['service_data'] ['type'] == 'manual') ? true : false;
	}

	public function haveConditions()
	{
		return (isset($this->_stw_data ['discount_data'] ['discount_have_conditions'])) ? true : false;
	}

	public function canApplyNow()
	{
		$items = Mage::getSingleton('checkout/session')->getQuote()
			->getItemsCount();
		return ($items) ? true : false;
	}

	public function getStats()
	{
		return $this->_stw_data ['invitations_data'];
	}

	public function isSomeoneSkipped()
	{
		return (isset($this->_stw_data ['invitations_data'] ['skipped'])) ? true : false;
	}

	public function getDownloadUrl()
	{
		return Mage::getUrl('*/success/download');
	}
}