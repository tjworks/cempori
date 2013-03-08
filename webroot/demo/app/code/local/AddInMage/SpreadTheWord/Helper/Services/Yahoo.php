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

class AddInMage_SpreadTheWord_Helper_Services_Yahoo extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = $this->__('Yahoo Mail');
		$service_data ['type'] = 'mail';
		$service_data ['specific_view'] = false;
		$service_data ['oauth_support'] = true;
		$service_data ['controller'] = 'oauth1';
		$service_data ['popup_size'] = array ('width' => '500', 'height' => '300');
		
		return $service_data;
	
	}

	public function getRedirect($consumer, $domain = null, $mobile = null)
	{
		$consumer->redirect();
	}

	public function getConfiguration_v_1($consumer_key, $consumer_secret, $service)
	{
		$config = array ('requestScheme' => AddInMage_Oauth::REQUEST_SCHEME_HEADER, 'callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth1?service=' . $service, 'userAuthorizationUrl' => 'https://api.login.yahoo.com/oauth/v2/request_auth', 'requestTokenUrl' => 'https://api.login.yahoo.com/oauth/v2/get_request_token', 'accessTokenUrl' => 'https://api.login.yahoo.com/oauth/v2/get_token', 'version' => '1.0', 'signatureMethod' => 'HMAC-SHA1', 'consumerKey' => $consumer_key, 'consumerSecret' => $consumer_secret);
		return $config;
	}

	public function getToken($consumer)
	{
		return $consumer->getRequestToken();
	}

	public function getAccessToken($consumer, $query_data, $request_token)
	{
		return $consumer->getAccessToken($query_data, $request_token, null, null);
	}

	public function getOauthContacts_v_1($token, $configuration)
	{
		try {
			$validator = new Zend_Validate_EmailAddress();
			
			$client = $token->getHttpClient($configuration);
			$session = Mage::getSingleton('customer/session');
			
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			else {
				$guid = $token->xoauth_yahoo_guid;
				
				$client->setUri('http://social.yahooapis.com/v1/user/' . $guid . '/profile');
				$client->setParameterGet(array ('format' => 'json'));
				$client->setConfig(array ('timeout' => 180, 'ssl_verify_peer' => false, 'ssl_verify_host' => false));
				$response_user = $client->request(Zend_Http_Client::GET);
				$user_data = Zend_Json::decode($response_user->getBody());
				
				$name = (isset($user_data ['profile'] ['givenName']) && isset($user_data ['profile'] ['familyName'])) ? $user_data ['profile'] ['givenName'] . ' ' . $user_data ['profile'] ['familyName'] : false;
				$email1 = (isset($user_data ['profile'] ['emails'] [0] ['handle'])) ? $user_data ['profile'] ['emails'] [0] ['handle'] : false;
				$email2 = (isset($user_data ['profile'] ['ims']) && ($user_data ['profile'] ['ims'] [0] ['type'] == 'YAHOO' || $user_data ['profile'] ['ims'] [0] ['type'] == 'YAHOO!')) ? $user_data ['profile'] ['ims'] [0] ['handle'] . '@yahoo.com' : false;
				if ($email2) $email = $email2;
				elseif ($email1) $email = $email1;
				else $email = false;
				
				if ($email) $session->setCurrentSenderData(array ('name' => $name, 'email' => $email, 'can_send' => true));
				else $session->setCurrentSenderData(array ('name' => $name, 'can_send' => false));
			}
			
			$client = $token->getHttpClient($configuration);
			$client->setUri('http://social.yahooapis.com/v1/user/me/contacts');
			$client->setParameterGet(array ('format' => 'json'));
			$client->setConfig(array ('timeout' => 180, 'ssl_verify_peer' => false, 'ssl_verify_host' => false));
			$response = $client->request(Zend_Http_Client::GET);
			$contacts = Zend_Json::decode($response->getBody());
			
			$string_to_lower = new Zend_Filter_StringToLower('UTF-8');
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			$contact_list = array ();
			foreach ($contacts ['contacts'] ['contact'] as $contact_info) {
				$c = array ();
				
				if (isset($contact_info ['fields'] ['0'] ['value'])) $c ['id'] = $string_to_lower->filter($contact_info ['fields'] ['0'] ['value']);
				
				$c ['name'] = '';
				if (isset($contact_info ['fields'] ['1'] ['value'] ['givenName'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($contact_info ['fields'] ['1'] ['value'] ['givenName']));
				if (isset($contact_info ['fields'] ['1'] ['value'] ['givenName']) && isset($contact_info ['fields'] ['1'] ['value'] ['familyName'])) $c ['name'] .= ' ';
				if (isset($contact_info ['fields'] ['1'] ['value'] ['familyName'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($contact_info ['fields'] ['1'] ['value'] ['familyName']));
				
				if (empty($c ['name'])) $c ['name'] = $c ['id'];
				if (! empty($c ['name']) && ! empty($c ['id']) && $validator->isValid($c ['id'])) $contact_list [] = $c;
			
			}
			Mage::getSingleton('customer/session')->setCurrentService(array ('service' => 'Yahoo', 'type' => 'mail', 'code' => 'yahoo'));
			$contact_list = array_values(array_filter($contact_list));
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'mail', 'yahoo');
		
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to Yahoo Service. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}
}