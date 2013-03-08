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

class AddInMage_SpreadTheWord_Helper_Services_Linkedin extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = 'Linkedin';
		$service_data ['type'] = 'social';
		$service_data ['specific_view'] = false;
		$service_data ['oauth_support'] = true;
		$service_data ['controller'] = 'oauth1';
		$service_data ['popup_size'] = array ('width' => '700', 'height' => '600');
		
		return $service_data;
	
	}

	public function getConfiguration_v_1($consumer_key, $consumer_secret, $service)
	{
		$config = array (
				'requestScheme' => AddInMage_Oauth::REQUEST_SCHEME_HEADER, 
				'callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth1?service=' . $service, 
				'userAuthorizationUrl' => 'https://api.linkedin.com/uas/oauth/authenticate', 
				'requestTokenUrl' => 'https://api.linkedin.com/uas/oauth/requestToken', 
				'accessTokenUrl' => 'https://api.linkedin.com/uas/oauth/accessToken', 
				'version' => '1.0', 
				'signatureMethod' => 'HMAC-SHA1', 
				'consumerKey' => $consumer_key, 
				'consumerSecret' => $consumer_secret
		);
		
		return $config;
	}

	public function getToken($consumer)
	{
		return $consumer->getRequestToken(array('scope' => 'r_network w_messages r_emailaddress r_basicprofile'));
	}

	public function getAccessToken($consumer, $query_data, $request_token)
	{
		return $consumer->getAccessToken($query_data, $request_token, null, null);
	}

	public function getRedirect($consumer, $domain = null, $mobile = null)
	{
		$consumer->redirect();
	}

	public function sendInvitations($recipients, $message)
	{
		
		$session = Mage::getSingleton('customer/session');
		$recipients_count = count($recipients);
		$failed = array ();
		$subject = $message ['subject'];
		$msg = $message ['body'];
		
		$success = true;
		
		$post = array ();
		foreach ($recipients as $recipient) {
			$post [] = array ("recipients" => array ("values" => array (array ('person' => array ('_path' => '/people/' . $recipient)))), "subject" => $subject, "body" => $msg [$recipient]);
		}
		
		try {
			$client = $session->getStwClientData();
			$client->setUri('http://api.linkedin.com/v1/people/~/mailbox');
			$client->setConfig(array ('keepalive' => true, 'timeout' => 180));
			
			$client->setMethod(Zend_Http_Client::POST);
			$client->setHeaders('Content-Type', 'application/json');
			$responses = array ();
			foreach ($post as $data) {
				$client->setRawData(Zend_Json_Encoder::encode($data), 'application/json');
				$response = $client->request();
				
				if ($response->getStatus() !== 201) $success = false;
				$responses [] = $response->getStatus();
			
			}
			
			$codes = array ();
			foreach ($responses as $key => $data) {
				if ($data !== 201) {
					$failed [] = (string) $recipients [$key];
					$codes [] = $data;
				}
			}
			
			if ($failed && $recipients_count == count($failed)) $success = false;
			
			$message = (in_array(403, $codes)) ? $this->__('You have exceeded LinkedIn limit for sending direct messages. Please try again later.') : false;
			$session->setStwSkippedReason($message);
		} catch (Exception $e) {
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			$success = false;
		}
		$failed_ids = (count($failed)) ? $failed : false;
		Mage::register('stw_direct_data', array ('success' => $success, 'message' => $message, 'skipped' => $failed_ids));
	}

	public function getOauthContacts_v_1($token, $configuration)
	{
		try {
			
			$session = Mage::getSingleton('customer/session');
			
			$client = $token->getHttpClient($configuration);
			$client->setConfig(array ('timeout' => 180));
			$session->setStwClientData($client);
			
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			else {
				$client->setUri('http://api.linkedin.com/v1/people/~');
				$client->setParameterGet(array ('format' => 'json'));
				$response_user = $client->request(Zend_Http_Client::GET);
				$user_data = Zend_Json::decode($response_user->getBody());
				$session->setCurrentSenderData(array ('name' => $user_data ['firstName'] . ' ' . $user_data ['lastName'], 'can_send' => false));
			}
			
			$client->setUri('http://api.linkedin.com/v1/people/~/connections:(id,first-name,last-name,picture-url,public-profile-url)');
			$client->setConfig(array ('timeout' => 180));
			$client->setParameterGet(array ('format' => 'json'));
			$response_contacts = $client->request(Zend_Http_Client::GET);
			$contacts = Zend_Json::decode($response_contacts->getBody());
			
			$contact_list = array ();
			$string_to_lower = new Zend_Filter_StringToLower();
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			
		
			foreach ($contacts ['values'] as $contact) {
				
				$c = array ();
				
				if (isset($contact ['id'])) $c ['id'] = $contact ['id'];
				
				$c ['name'] = '';
				if (isset($contact ['firstName'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($contact ['firstName']));
				if (isset($contact ['firstName']) && isset($contact ['lastName'])) $c ['name'] .= ' ';
				if (isset($contact ['lastName'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($contact ['lastName']));
				
				if (isset($contact ['publicProfileUrl'])) $c ['link'] = $contact ['publicProfileUrl'];
				if (isset($contact ['pictureUrl'])) $c ['picture'] = $contact ['pictureUrl'];
				
				$contact_list [] = $c;
			}
			
			Mage::getSingleton('customer/session')->setCurrentService(array ('service' => 'LinkedIn', 'type' => 'social', 'code' => 'linkedin'));
			$contact_list = array_values(array_filter($contact_list));
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'social', 'linkedin');
		
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to LinkedIn Service. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}
}