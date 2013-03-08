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


class AddInMage_SpreadTheWord_Oauth1Controller extends Mage_Core_Controller_Front_Action
{

	private function _checkAccessDenied()
	{
		$message = Mage::helper('spreadtheword')->__('You denied access to your friend list. You need to allow access in order to invite your friends. Please try again.');
		if ($this->getRequest()
			->getParam('oauth_problem') && $this->getRequest()
			->getParam('oauth_problem') == 'user_refused' || $this->getRequest()
			->getParam('denied')) {
			Mage::getSingleton('core/session')->addNotice($message);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			die();
		}
	}

	public function indexAction()
	{
		
		$this->_checkAccessDenied();
		$session = Mage::getSingleton('core/session');
		$oauth_process_data = Mage::getSingleton('core/session')->getOauthVerifier();
		$request_token = $session->getData($oauth_process_data ['service'] . '_request_token');
		
		$service = $this->getRequest()
			->getParam('service');
		$mobile = $this->getRequest()
			->getParam('mobile');
		$domain = $this->getRequest()
			->getParam('domain');
		
		if ($oauth_process_data && $request_token) {
			$service = $oauth_process_data ['service'];
		}
		
		if (! $service) return $this->_redirect('*');
		
		if (! Mage::helper('spreadtheword')->checkProvider($service, $this->getRequest()
			->getControllerName())) return $this->_redirect('*');
		
		$oauth_settings = Mage::helper('spreadtheword')->getOauthData($service);
		
		$consumer_key = $oauth_settings ['oauth_consumer'];
		$consumer_secret = $oauth_settings ['oauth_secret'];
		
		$configuration = Mage::helper('spreadtheword/services_' . $service)->getConfiguration_v_1($consumer_key, $consumer_secret, $service);
		
		$consumer = new AddInMage_Oauth_Consumer($configuration);
		
		if ($oauth_process_data && $request_token) {
			
			$data = unserialize($request_token);
			$token_data = $data->getToken();
			try {
				$token = $consumer->getAccessToken(array ('oauth_verifier' => $oauth_process_data ['oauth_verifier'], 'oauth_token' => $token_data), unserialize($request_token));
			} catch (Exception $e) {
				$session->addError(Mage::helper('spreadtheword')->__('This verification code is wrong. Please try again.'));
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
				$this->_redirect('*');
			}
			$session->setData($service . '_access_token', serialize($token));
			Mage::getSingleton('core/session')->unsOauthVerifier();
			$session->unsetData($service . '_request_token');
		}
		
		try {
			
			$token = null;
			
			if ($session->getData($service . '_access_token')) {
				$token = unserialize($session->getData($service . '_access_token'));
			} else 
				if ($this->getRequest()
					->getParam('oauth_token') && $session->getData($service . '_request_token')) {
					$token = Mage::helper('spreadtheword/services_' . $service)->getAccessToken($consumer, $this->getRequest()
						->getParams(), unserialize($session->getData($service . '_request_token')));
					$session->setData($service . '_access_token', serialize($token));
					$session->unsetData($service . '_request_token');
				}
			if (! $token) {
				$token = Mage::helper('spreadtheword/services_' . $service)->getToken($consumer);
				$session->setData($service . '_request_token', serialize($token));
				$token = Mage::helper('spreadtheword/services_' . $service)->getRedirect($consumer, $domain, $mobile);
			
			} else {
				$data = $token->getToken();
				$session->unsetData($service . '_request_token');
				$session->unsetData($service . '_access_token');
				
				Mage::helper('spreadtheword/services_' . $service)->getOauthContacts_v_1($token, $configuration, $data);
			}
		
		} catch (Exception $e) {
			$session->addNotice(Mage::helper('spreadtheword')->__('There is a problem with the service connection or the service provider secure token has expired. Please try to invite your friends in a few minutes.'));
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
		}
	}
}