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


class AddInMage_SpreadTheWord_ManualController extends Mage_Core_Controller_Front_Action
{

	protected function isAjax()
	{
		if (Mage::helper('spreadtheword')->isAjaxImplemented()) return $this->getRequest()
			->isAjax();
		
		else {
			if ($this->getRequest()
				->isXmlHttpRequest()) {return true;}
			if ($this->getRequest()
				->getParam('ajax') || $this->getRequest()
				->getParam('isAjax')) {return true;}
			return false;
		}
	}

	public function bodyAction()
	{
		if (! $this->isAjax()) return $this->_redirect('*');
		
		if (! $this->_validateFormKey()) {
			$message = $this->__('The secure token failed or your session has expired. Please try again.');
			Mage::getSingleton('core/session')->addError($message);
			header("Status: 307 Secure token failed.");
		} else {
			$this->loadLayout(false);
			$this->renderLayout();
		}
	}

	public function titleAction()
	{
		
		if (! $this->isAjax()) return $this->_redirect('*');
		$this->_checkConfigurationChanges();
		$this->loadLayout(false);
		$this->renderLayout();
	}

	protected function _checkConfigurationChanges()
	{
		
		$session = Mage::getSingleton('core/session');
		$rule = Mage::helper('spreadtheword')->getRule();
		if (is_object($rule)) $check_hash = md5($rule->toString());
		else $check_hash = md5('noaction');
		$conf = $session->getStwConfig();
		$conf_hash = $session->getStwConfigHash();
		
		if (! $conf_hash || ! $conf) {
			$session->addNotice($this->__('Your session has expired. Please start over. Thank you.'));
			echo '<script type="text/javascript">location.href = "' . Mage::getUrl('spreadtheword') . '"</script>';
		}
		
		if ($conf_hash !== $check_hash) {
			$session->addNotice($this->__('Sorry, the invitation terms have been changed. Please start over. Thank you.'));
			echo '<script type="text/javascript">location.href = "' . Mage::getUrl('spreadtheword') . '"</script>';
		}
	}

	public function inviteAction()
	{
		
		if (! $this->getRequest()
			->isPost()) return $this->_redirect('*');
		
		if (! $this->_validateFormKey()) {
			$message = $this->__('The secure token failed or your session has expired. Please try again.');
			Mage::getSingleton('core/session')->addError($message);
			return $this->_redirect('*');
		}
		$session = Mage::getSingleton('customer/session');
		$validator = new Zend_Validate_EmailAddress();
		$recipients = $this->getRequest()
			->getPost('recipients');
		$personal_message = ($this->getRequest()
			->getPost('personal-message')) ? $this->getRequest()
			->getPost('personal-message') : false;
		
		if (! $session->isLoggedIn()) {
			$sender_name = $this->getRequest()
				->getPost('sender_name');
			$sender_email = $this->getRequest()
				->getPost('sender_email');
			if (! $sender_name) {
				Mage::getSingleton('core/session')->addError($this->__('Please enter your full name.'));
				return $this->_redirect('*');
			} elseif (! $sender_email) {
				Mage::getSingleton('core/session')->addError($this->__('Please enter your email address.'));
				return $this->_redirect('*');
			} else {
				if (! $validator->isValid($sender_email)) {
					Mage::getSingleton('core/session')->addError($this->__('Please verify your email address. In order to invite your friends you need to enter a valid email address.'));
					return $this->_redirect('*');
				}
				$session->setCurrentSenderData(array ('name' => $sender_name, 'email' => $sender_email, 'can_send' => true));
			}
			;
		} else {
			$session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
		}
		
		if (! $recipients) {
			Mage::getSingleton('core/session')->addError($this->__('Please enter the names and email addresses of the friends  you want to invite.'));
			return $this->_redirect('*');
		}
		$data = array ();
		foreach ($recipients as $recipient) {
			if ($validator->isValid($recipient ['email'])) $data [] = array ('name' => $recipient ['name'], 'id' => $recipient ['email']);
		}
		
		if (count($data)) {
			$contacts = Mage::getModel('spreadtheword/friends')->processContacts($data, 'manual', 'manual');
			$session->setFriends($contacts);
			Mage::getModel('spreadtheword/friends')->addFriends($contacts, 'manual');
			$session->setCurrentService(array ('service' => $this->__('Manual Invitation'), 'type' => 'manual', 'code' => 'manual'));
			$config = Mage::getSingleton('core/session')->getStwConfig();
			
			if (is_object($config)) {
				$configuration = unserialize($config->getConfiguration());
				$discount_data = array ('mode' => $config ['rule_mode']);
				if ($config ['rule_mode'] == 'action_d_sen' || $config ['rule_mode'] == 'action_d_all') $discount_data ['sender'] = array ('mode' => 'fixed', 'data' => $configuration ['manual_discount']);
				if ($config ['rule_mode'] == 'action_d_frd' || $config ['rule_mode'] == 'action_d_all') $discount_data ['friends'] = array ('mode' => 'fixed', 'data' => $configuration ['manual_discount']);
			} else
				$discount_data = array ('mode' => 'noaction');
			
			$session->setCurrentDiscountData($discount_data);
			$friends = array ();
			foreach ($contacts as $contact) {
				$friends [] = $contact ['friend_invite_link'];
			}
			try {
				Mage::getModel('spreadtheword/send')->handleInvitation($friends, $personal_message);
				if ($session->getStwSuccess()) return $this->_redirect('*/success');
			} catch (Exception $e) {
				Mage::getSingleton('core/session')->addError($this->__('An error occurred while sending invitations. Please try again later.'));
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
				return $this->_redirect('*');
			}
		} else {
			Mage::getSingleton('core/session')->addError($this->__('Please verify your friends\' email addresses.'));
			return $this->_redirect('*');
		}
	}
}