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


class AddInMage_SpreadTheWord_OauthVerifierController extends Mage_Core_Controller_Front_Action
{

	public function preDispatch()
	{
		parent::preDispatch();
		if (! Mage::getSingleton('customer/session')->isLoggedIn()) {
			$access_level = Mage::getStoreConfig('addinmage_spreadtheword/general/access', Mage::app()->getStore());
			if ($access_level == 'customer') {
				Mage::getSingleton('customer/session')->setBeforeAuthUrl($this->getRequest()
					->getRequestUri());
				$this->_redirect('customer/account/login');
			}
		}
	}

	public function indexAction()
	{
		$oauth_process_data = Mage::getSingleton('core/session')->getOauthVerifierProcess();
		$oauth_verifier = $this->getRequest()
			->getParam('verifier');
		
		if (! $oauth_process_data) $this->_redirect('*');
		
		if ($this->getRequest()
			->isPost() && $oauth_verifier) {
			
			if (! $this->_validateFormKey()) {
				$message = $this->__('The secure token failed. Please try again.');
				return Mage::helper('spreadtheword')->handleError($message, 'error');
			}
			$data = $oauth_process_data;
			$data ['oauth_verifier'] = (string) trim($oauth_verifier);
			Mage::getSingleton('core/session')->unsOauthVerifierProcess();
			Mage::getSingleton('core/session')->setOauthVerifier($data);
			$this->_redirect('spreadtheword/oauth1');
		} else {
			$this->loadLayout();
			$this->renderLayout();
		}
	}
}