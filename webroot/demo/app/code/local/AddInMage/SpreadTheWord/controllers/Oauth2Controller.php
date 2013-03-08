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


class AddInMage_SpreadTheWord_Oauth2Controller extends Mage_Core_Controller_Front_Action
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

	private function _checkAccessDenied($params = null)
	{
		$message = Mage::helper('spreadtheword')->__('You denied access to your friend list. You need to allow access in order to invite your friends. Please try again.');
		if ($this->getRequest()
			->getParam('error') && $this->getRequest()
			->getParam('error') == 'access_denied' || $params && $params ['error'] == 'access_denied') {
			Mage::getSingleton('core/session')->addNotice($message);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			die('asd');
		}
	}

	public function indexAction()
	{
		
		$get_data = $this->getRequest()
			->getParams();
		$service = $this->getRequest()
			->getParam('service');
		$code = $this->getRequest()
			->getParam('code');
		
		// Fix Yammer
		if (preg_match('/(\?code=)/i', $get_data ['service'])) {
			$service = preg_replace('/(\?code=.+)|(.)/i', '$2', $get_data ['service']);
			$code = preg_replace('/(.+\?code=)|(.)/i', '$2', $get_data ['service']);
		}
		// Fix Yammer
		if (preg_match('/(\?error=)/i', $get_data ['service'])) {
			$error = preg_replace('/(.+\?error=)|(.)/i', '$2', $get_data ['service']);
			$get_data ['error'] = $error;
			$this->_checkAccessDenied(array ('error' => $error));
		}
		
		$this->_checkAccessDenied();
		
		if (! $service) return $this->_redirect('*');
		
		if (! Mage::helper('spreadtheword')->checkProvider($service, $this->getRequest()
			->getControllerName())) return $this->_redirect('*');
		
		$oauth_settings = Mage::helper('spreadtheword')->getOauthData($service);
		
		$clientId = $oauth_settings ['oauth_consumer'];
		$clientSecret = $oauth_settings ['oauth_secret'];
		
		$configuration = Mage::helper('spreadtheword/services_' . $service)->getConfiguration_v_2($clientId, $clientSecret, $service);
		
		$session = Mage::getSingleton('core/session');
		$consumer = new AddInMage_Oauth2($configuration);
		
		try {
			if ($code) {
				$verificationCode = trim(addslashes(strip_tags($code)));
				
				if (preg_match('/(code=)/i', $verificationCode) && $service == 'vk') {
					$verificationCode = explode('code=', $verificationCode);
					$verificationCode = $verificationCode [1];
				}
				
				$consumer->setVerificationCode($verificationCode);
				$access = $consumer->requestAccessToken();
				
				$token = Mage::helper('spreadtheword/services_' . $service)->getExtractedToken($access);
				$token = serialize($token);
			
			} else
				$consumer->authorizationRedirect();
			
			Mage::helper('spreadtheword/services_' . $service)->getOauthContacts_v_2($token, $clientId, $clientSecret);
		
		} catch (Exception $e) {
			$session->addNotice(Mage::helper('spreadtheword')->__('There is a problem with the service connection or the service provider secure token has expired. Please try to invite your friends in a few minutes.'));
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
		}
	}
}