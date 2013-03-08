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

class AddInMage_SpreadTheWord_Helper_Services_Yammer extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = 'Yammer';
		$service_data ['type'] = 'social';
		$service_data ['specific_view'] = false;
		$service_data ['oauth_support'] = true;
		$service_data ['controller'] = 'oauth2';
		$service_data ['popup_size'] = array ('width' => '800', 'height' => '470');
		
		return $service_data;
	
	}

	public function getConfiguration_v_1($consumer_key, $consumer_secret, $service)
	{
		$config = array ('requestScheme' => AddInMage_Oauth::REQUEST_SCHEME_HEADER, 'callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth1?service=' . $service, 'userAuthorizationUrl' => 'https://www.yammer.com/oauth/authorize', 'requestTokenUrl' => 'https://www.yammer.com/oauth/request_token', 'accessTokenUrl' => 'https://www.yammer.com/oauth/access_token', 'version' => '1.0', 'signatureMethod' => 'HMAC-SHA1', 'consumerKey' => $consumer_key, 'consumerSecret' => $consumer_secret);
		
		return $config;
	}

	public function getConfiguration_v_2($clientId, $clientSecret, $service)
	{
		$config = array ('callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth2?service=' . $service, 'authorizeEndPoint' => 'https://www.yammer.com/dialog/oauth', 'tokenEndPoint' => 'https://www.yammer.com/oauth2/access_token.json', 'responseType' => 'code', 'clientId' => $clientId, 'grandType' => 'authorization_code', 'clientSecret' => $clientSecret);
		
		return $config;
	}

	public function getExtractedToken($access)
	{
		return $access;
	}

	public function getToken($consumer)
	{
		return $consumer->getRequestToken();
	}

	public function getAccessToken($consumer, $query_data, $request_token)
	{
		return $consumer->getAccessToken($query_data, $request_token, null, null);
	}

	public function getRedirect($consumer, $domain = null, $mobile = null)
	{
		Mage::getSingleton('core/session')->setOauthVerifierProcess(array ('service' => 'yammer'));
		echo '<script type="text/javascript">
				window.opener.location= "' . Mage::getUrl("spreadtheword/oauthverifier") . '";
				window.location= "' . $consumer->getRedirectUrl() . '";
				</script>';
	
	}

	public function getOauthContacts_v_1($token, $configuration, $data)
	{
		try {
			
			$session = Mage::getSingleton('customer/session');
			
			$validator = new Zend_Validate_EmailAddress();
			$client = $token->getHttpClient($configuration);
			$client->setConfig(array ('timeout' => 180));
			$session->setStwClientData($client);
			$client->setUri('https://www.yammer.com/api/v1/oauth/tokens.json');
			$response = $client->request(Zend_Http_Client::GET);
			
			$user_networks = Zend_Json::decode($response->getBody());
			
			foreach ($user_networks as $network) {
				if ($network ['token'] == $data) $user_id = $network ['user_id'];
			}
			
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			else {
				$client->setUri('https://www.yammer.com/api/v1/users/' . $user_id . '.json');
				$response_user = $client->request(Zend_Http_Client::GET);
				$user_data = Zend_Json::decode($response_user->getBody());
				$session->setCurrentSenderData(array ('name' => $user_data ['full_name'], 'email' => $user_data ['contact'] ['email_addresses'] [0] ['address'], 'can_send' => true));
			}
			
			$client->setUri('https://www.yammer.com/api/v1/users.json');
			$response = $client->request(Zend_Http_Client::GET);
			$network_users = Zend_Json::decode($response->getBody());
			
			$string_to_lower = new Zend_Filter_StringToLower('UTF-8');
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			$contact_list = array ();
			foreach ($network_users as $user) {
				$c = array ();
				if ($user ['id'] !== $user_id) {
					$c = array ();
					
					$c ['id'] = $user ['id'];
					
					if (isset($user ['full_name'])) {
						$c ['name'] = '';
						$full_name = explode(' ', $user ['full_name']);
						
						if (isset($full_name [0])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [0]));
						
						if (isset($full_name [0]) && isset($full_name [1])) $c ['name'] .= ' ';
						
						if (isset($full_name [1])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [1]));
					}
					
					if (isset($user ['web_url'])) $c ['link'] = $user ['web_url'];
					if (isset($user ['mugshot_url'])) $c ['picture'] = (substr($user ['mugshot_url'], strrpos($user ['mugshot_url'], '/') + 1) !== 'no_photo_small.gif') ? $user ['mugshot_url'] : null;
					
					if (! empty($c ['name']) && ! empty($c ['id'])) $contact_list [] = $c;
				}
			}
			Mage::getSingleton('customer/session')->setCurrentService(array ('service' => 'Yammer', 'type' => 'social', 'code' => 'yammer'));
			$contact_list = array_values(array_filter($contact_list));
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'social', 'yammer');
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to Yammer Service. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}

	public function getOauthContacts_v_2($token)
	{
		try {
			
			$session = Mage::getSingleton('customer/session');
			
			$token = unserialize($token);
			$token = Zend_Json::decode($token);
			$access_token = $token ['access_token'] ['token'];
			$user_id = $token ['user'] ['id'];
			$client = new Zend_Http_Client();
			$client->setConfig(array ('timeout' => 180));
			$validator = new Zend_Validate_EmailAddress();
			
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			else {
				$client->setUri('https://www.yammer.com/api/v1/users/' . $user_id . '.json');
				$client->setParameterGet(array ('access_token' => $access_token));
				$response_user = $client->request(Zend_Http_Client::GET);
				$user_data = Zend_Json::decode($response_user->getBody());
				$session->setCurrentSenderData(array ('name' => $user_data ['full_name'], 'email' => $user_data ['contact'] ['email_addresses'] [0] ['address'], 'can_send' => true));
			}
			
			$session->setStwClientData($access_token);
			
			$client->setUri('https://www.yammer.com/api/v1/users.json');
			$client->setParameterGet(array ('access_token' => $access_token));
			$response_contacts = $client->request(Zend_Http_Client::GET);
			$network_users = Zend_Json::decode($response_contacts->getBody());
			
			$string_to_lower = new Zend_Filter_StringToLower('UTF-8');
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			
			$contact_list = array ();
			foreach ($network_users as $user) {
				
				$c = array ();
				if ($user ['id'] !== $user_id) {
					$c = array ();
					
					$c ['id'] = $user ['id'];
					
					if (isset($user ['full_name'])) {
						$c ['name'] = '';
						$full_name = explode(' ', $user ['full_name']);
						
						if (isset($full_name [0])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [0]));
						
						if (isset($full_name [0]) && isset($full_name [1])) $c ['name'] .= ' ';
						
						if (isset($full_name [1])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [1]));
					}
					
					if (isset($user ['web_url'])) $c ['link'] = $user ['web_url'];
					if (isset($user ['mugshot_url'])) $c ['picture'] = (substr($user ['mugshot_url'], strrpos($user ['mugshot_url'], '/') + 1) !== 'no_photo_small.gif') ? $user ['mugshot_url'] : null;
					
					if (! empty($c ['name']) && ! empty($c ['id'])) $contact_list [] = $c;
				}
			}
			
			Mage::getSingleton('customer/session')->setCurrentService(array ('service' => 'Yammer', 'type' => 'social', 'code' => 'yammer'));
			$contact_list = array_values(array_filter($contact_list));
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'social', 'yammer');
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to Yammer Service. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}

	public function sendInvitations($recipients, $message)
	{
		$service_data = $this->getInfo();
		if ($service_data ['controller'] == 'oauth1') $this->sendInvitations_v_1($recipients, $message);
		if ($service_data ['controller'] == 'oauth2') $this->sendInvitations_v_2($recipients, $message);
	
	}

	public function sendInvitations_v_2($recipients, $message)
	{
		$session = Mage::getSingleton('customer/session');
		$access_token = $session->getStwClientData();
		$success = true;
		$failed = array ();
		$codes = array ();
		$recipients_count = count($recipients);
		$msg = $message ['body'];
		try {
			$client = new Zend_Http_Client();
			$client->setConfig(array ('keepalive' => true, 'timeout' => 180));
			$client->setUri('https://www.yammer.com/api/v1/messages.json?access_token=' . $access_token);
			$client->setMethod(Zend_Http_Client::POST);
			
			foreach ($recipients as $recipient) {
				$client->setParameterPost(array ('access_token' => $access_token, 'direct_to_id' => $recipient, 'body' => $msg [$recipient]));
				$response = $client->request();
				$codes [] = $response->getStatus();
			}
			foreach ($codes as $key => $code) {
				if ($code !== 201) {
					$failed [] = (string) $recipients [$key];
				}
			
			}
			
			if ($failed && $recipients_count == count($failed)) $success = false;
			$message = (in_array(403, $codes)) ? $this->__('You have exceeded Yammer limit for sending direct messages. Please try again later.') : false;
		} catch (Exception $e) {
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			$success = false;
		}
		$failed_ids = (count($failed)) ? $failed : false;
		Mage::register('stw_direct_data', array ('success' => $success, 'message' => $message, 'skipped' => $failed_ids));
	}

	public function sendInvitations_v_1($recipients, $message)
	{
		$session = Mage::getSingleton('customer/session');
		$success = true;
		$failed = array ();
		$codes = array ();
		$recipients_count = count($recipients);
		$msg = $message ['body'];
		try {
			$client = $session->getStwClientData();
			$client->setConfig(array ('keepalive' => true, 'timeout' => 180));
			$client->setUri('https://www.yammer.com/api/v1/messages.json');
			$client->setMethod(Zend_Http_Client::POST);
			
			foreach ($recipients as $recipient) {
				$client->setParameterPost(array ('access_token' => $access_token, 'direct_to_id' => $recipient, 'body' => $msg [$recipient]));
				$response = $client->request();
				$codes [] = $response->getStatus();
			}
			foreach ($codes as $key => $code) {
				if ($code !== 201) {
					$failed [] = (string) $recipients [$key];
				}
			}
			if ($failed && $recipients_count == count($failed)) $success = false;
			$message = (in_array(403, $codes)) ? $this->__('You have exceeded Yammer limit for sending direct messages. Please try again later.') : false;
		} catch (Exception $e) {
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			$success = false;
		}
		$failed_ids = (count($failed)) ? $failed : false;
		Mage::register('stw_direct_data', array ('success' => $success, 'message' => $message, 'skipped' => $failed_ids));
	}
}