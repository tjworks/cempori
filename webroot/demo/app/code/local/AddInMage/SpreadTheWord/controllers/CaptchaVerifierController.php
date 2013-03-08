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


class AddInMage_SpreadTheWord_CaptchaVerifierController extends Mage_Core_Controller_Front_Action
{

	public function preDispatch()
	{
		parent::preDispatch();
		if (! Mage::getSingleton('customer/session')->isLoggedIn()) {
			$access_level = Mage::getStoreConfig('addinmage_spreadtheword/general/access', Mage::app()->getStore());
			if ($access_level == 'customer') {
				Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());
				$this->_redirect('customer/account/login');
			}
		}
	}

	public function indexAction()
	{
		$session = Mage::getSingleton('customer/session');
		$captcha_data = $session->getStwCaptcha();
		$captcha_verifier = $this->getRequest()
			->getParam('captcha_sid');
		
		if (! $captcha_data) $this->_redirect('*');
		
		if ($this->getRequest()
			->isPost() && $captcha_verifier) {
			if (! $this->_validateFormKey()) {
				Mage::getSingleton('core/session')->addNotice($this->__('Secure token failed. Please try again.'));
				$this->_redirect('spreadtheword');
			}
			$captcha_data ['code'] = trim($captcha_verifier);
			$session->setStwCaptcha($captcha_data);
			$current_service_data = $session->getCurrentService();
			Mage::helper('spreadtheword/services_' . $current_service_data ['code'])->sendInvitations();
		} else {
			$this->loadLayout();
			$this->renderLayout();
		}
	}
}