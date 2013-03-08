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

class AddInMage_SpreadTheWord_Helper_Services_Gmail extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = Mage::helper('spreadtheword')->__('Gmail Google');
		$service_data ['type'] = 'mail';
		$service_data ['specific_view'] = false;
		$service_data ['oauth_support'] = true;
		$service_data ['controller'] = 'oauth2';
		$service_data ['popup_size'] = array ('width' => '700', 'height' => '450');
		
		return $service_data;
	
	}

	public function getOauthContacts_v_2($token)
	{
		try {
			$token = unserialize($token);
			$token = Zend_Json::decode($token);
			
			$client = new Zend_Http_Client();
			$client->setConfig(array ('timeout' => 180));
			$session = Mage::getSingleton('customer/session');
			
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			else {
				$client->setUri('https://www.googleapis.com/oauth2/v1/userinfo');
				$client->setParameterGet(array ('access_token' => $token ['access_token'], 'alt' => 'json'));
				$response_user = $client->request(Zend_Http_Client::GET);
				$user_data = Zend_Json::decode($response_user->getBody());
				$session->setCurrentSenderData(array ('name' => $user_data ['name'], 'email' => $user_data ['email'], 'can_send' => true));
			}
			
			$validator = new Zend_Validate_EmailAddress();
			$client->setUri('https://www.google.com/m8/feeds/contacts/default/full');
			$client->setParameterGet(array ('access_token' => $token ['access_token'], 'alt' => 'json', 'max-results' => '10000'));
			$client->setHeaders('GData-Version: 3.0');
			$response = $client->request(Zend_Http_Client::GET);
			$contacts = Zend_Json::decode($response->getBody());
			
			$string_to_lower = new Zend_Filter_StringToLower();
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			$contact_list = array ();
			
			foreach ($contacts ['feed'] ['entry'] as $entry) {
				
				$c = array ();
				
				if (isset($entry ['gd$email'] [0] ['address'])) $c ['id'] = $string_to_lower->filter($entry ['gd$email'] [0] ['address']);
				
				$c ['name'] = '';
				if (isset($entry ['gd$name'] ['gd$givenName'] ['$t'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($entry ['gd$name'] ['gd$givenName'] ['$t']));
				if (isset($entry ['gd$name'] ['gd$givenName'] ['$t']) && isset($entry ['gd$name'] ['gd$familyName'] ['$t'])) $c ['name'] .= ' ';
				if (isset($entry ['gd$name'] ['gd$familyName'] ['$t'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($entry ['gd$name'] ['gd$familyName'] ['$t']));
				
				if (isset($entry ['gContact$website'] [0] ['href'])) $c ['link'] = $entry ['gContact$website'] [0] ['href'];
				
				if (isset($entry ['link'] [0] ['gd$etag'])) $c ['picture'] = urldecode($entry ['link'] [0] ['href']) . '?access_token=' . $token ['access_token'] . '&sz=50';
				
				if (empty($c ['name'])) $c ['name'] = $c ['id'];
				if (! empty($c ['name']) && ! empty($c ['id']) && $validator->isValid($c ['id'])) $contact_list [] = $c;
			}
			$session->setCurrentService(array ('service' => 'Gmail Google', 'type' => 'mail', 'code' => 'gmail'));
			$contact_list = array_values(array_filter($contact_list));
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'mail', 'gmail');
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to Google Gmail Service. Please try again in a few minutes.');
			$session->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}

	public function getOauthContacts_v_1($token, $configuration)
	{
		try {
			$validator = new Zend_Validate_EmailAddress();
			$client = $token->getHttpClient($configuration);
			$client->setConfig(array ('timeout' => 180));
			$gdata = new Zend_Gdata($client);
			$query = new Zend_Gdata_Query('https://www.google.com/m8/feeds/contacts/default/full/?max-results=10000');
			$feed = $gdata->getFeed($query);
			$string_to_lower = new Zend_Filter_StringToLower();
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			$contact_list = array ();
			foreach ($feed as $entry) {
				$xml = simplexml_load_string($entry->getXML());
				$c = array ();
				
				if (isset($entry->title)) {
					$c ['name'] = '';
					$full_name = explode(' ', $entry->title);
					
					if (isset($full_name [0])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [0]));
					
					if (isset($full_name [0]) && isset($full_name [1])) $c ['name'] .= ' ';
					
					if (isset($full_name [1])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [1]));
				}
				
				if (isset($xml->email)) foreach ($xml->email as $e) {
					$c ['id'] = $string_to_lower->filter($e ['address']);
				}
				
				if (empty($c ['name'])) $c ['name'] = $c ['id'];
				if (! empty($c ['name']) && ! empty($c ['id']) && $validator->isValid($c ['id'])) $contact_list [] = $c;
			}
			Mage::getSingleton('customer/session')->setCurrentService(array ('service' => 'Gmail Google', 'type' => 'mail', 'code' => 'gmail'));
			$contact_list = array_values(array_filter($contact_list));
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'mail', 'gmail');
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to Google Gmail Service. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}

	public function getToken($consumer)
	{
		return $consumer->getRequestToken(array ('scope' => 'https://www.google.com/m8/feeds/contacts/default/full/'));
	}

	public function getAccessToken($consumer, $query_data, $request_token)
	{
		return $consumer->getAccessToken($query_data, $request_token, null, null);
	}

	public function getRedirect($consumer, $domain = null, $mobile = null)
	{
		$param = array ();
		
		if ($domain) $param ['hd'] = $domain;
		
		if ($mobile) $param ['btmpl'] = 'mobile';
		
		$param ['hl'] = Mage::app()->getLocale()
			->getDefaultLocale();
		
		$consumer->redirect($param);
	}

	public function getConfiguration_v_2($clientId, $clientSecret, $service)
	{
		$config = array ('callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth2?service=' . $service, 'authorizeEndPoint' => 'https://accounts.google.com/o/oauth2/auth', 'tokenEndPoint' => 'https://accounts.google.com/o/oauth2/token', 'requestedRights' => array ('https://www.google.com/m8/feeds https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email'), 'responseType' => 'code', 'clientId' => $clientId, 'grandType' => 'authorization_code', 'clientSecret' => $clientSecret);
		
		return $config;
	}

	public function getExtractedToken($access)
	{
		return $access;
	}

	public function getConfiguration_v_1($consumer_key, $consumer_secret, $service)
	{
		
		$config = array ('requestScheme' => AddInMage_Oauth::REQUEST_SCHEME_HEADER, 'callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth1?service=' . $service, 'userAuthorizationUrl' => 'https://www.google.com/accounts/OAuthAuthorizeToken', 'requestTokenUrl' => 'https://www.google.com/accounts/OAuthGetRequestToken', 'accessTokenUrl' => 'https://www.google.com/accounts/OAuthGetAccessToken', 'version' => '1.0', 'signatureMethod' => 'HMAC-SHA1', 'consumerKey' => $consumer_key, 'consumerSecret' => $consumer_secret);
		return $config;
	}
}