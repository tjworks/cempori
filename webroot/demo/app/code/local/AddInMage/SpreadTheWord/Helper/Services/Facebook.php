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

class AddInMage_SpreadTheWord_Helper_Services_Facebook extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = 'Facebook';
		$service_data ['type'] = 'social';
		$service_data ['specific_view'] = false;
		$service_data ['oauth_support'] = true;
		$service_data ['controller'] = 'oauth2';
		$service_data ['popup_size'] = array ('width' => '1000', 'height' => '610');
		
		return $service_data;
	
	}

	public function getConfiguration_v_2($clientId, $clientSecret, $service)
	{
		$config = array ('callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth2?service=' . $service, 'authorizeEndPoint' => 'https://graph.facebook.com/oauth/authorize', 'tokenEndPoint' => 'https://graph.facebook.com/oauth/access_token', 'requestedRights' => array ('read_friendlists,publish_stream,email'), 'responseType' => 'code', 'clientId' => $clientId, 'grandType' => 'authorization_code', 'clientSecret' => $clientSecret);
		
		return $config;
	}

	public function getExtractedToken($access)
	{
		$access = urldecode($access);
		$parts = explode('&', trim($access));
		foreach ($parts as $part) {
			$va = explode('=', $part, 2);
			if (count($va) == 2) {
				$key = $va [0];
				$value = urldecode($va [1]);
				if ($key == 'access_token') {
					$access_token = $value;
				}
			}
		}
		return $access_token;
	}

	public function getOauthContacts_v_2($token)
	{
		try {
			
			$session = Mage::getSingleton('customer/session');
			
			$token = unserialize($token);
			$client = new Zend_Http_Client();
			
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			else {
				$client->setUri('https://graph.facebook.com/me/');
				$client->setParameterGet(array ('access_token' => $token, 'fields' => 'id,first_name,last_name,email'));
				$client->setConfig(array ('timeout' => 180));
				$response_user = $client->request(Zend_Http_Client::GET);
				$user_data = Zend_Json::decode($response_user->getBody());
				$session->setCurrentSenderData(array ('name' => $user_data ['first_name'] . ' ' . $user_data ['last_name'], 'email' => $user_data ['email'], 'can_send' => true));
			}
			$session->setStwClientData($token);
			$client->setUri('https://graph.facebook.com/me/friends');
			$client->setConfig(array ('timeout' => 180));
			$client->setParameterGet(array ('access_token' => $token, 'fields' => 'id,first_name,last_name,picture,link,email'));
			$response_contacts = $client->request(Zend_Http_Client::GET);
			$contacts = Zend_Json::decode($response_contacts->getBody());
			
			$string_to_lower = new Zend_Filter_StringToLower('UTF-8');
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			$contact_list = array ();
			
			foreach ($contacts ['data'] as $entry) {
				
				$c = array ();
				
				$c ['id'] = $entry ['id'];
				
				$c ['name'] = '';
				if (isset($entry ['first_name'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($entry ['first_name']));
				if (isset($entry ['first_name']) && isset($entry ['last_name'])) $c ['name'] .= ' ';
				if (isset($entry ['last_name'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($entry ['last_name']));
				
				if (isset($entry ['link'])) $c ['link'] = $entry ['link'];
				
				if (isset($entry ['picture']['data']['url'])) $c ['picture'] = trim((string) $entry ['picture']['data']['url']);
				
				$contact_list [] = $c;
			}
			
			Mage::getSingleton('customer/session')->setCurrentService(array ('service' => 'Facebook', 'type' => 'social', 'code' => 'facebook'));
			$contact_list = array_values(array_filter($contact_list));
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'social', 'facebook');
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to Facebook. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}

	public function sendInvitations($recipients, $message)
	{
		$session = Mage::getSingleton('customer/session');
		$token = $session->getStwClientData();
		$msg = $message ['body'];
		$failed = array ();
		$batch_limit = 50;
		$recipients_count = count($recipients);
		$success = true;
		$batch = array ();
		if ($recipients_count <= $batch_limit) {
			$batch_part = array ();
			foreach ($recipients as $recipient) {
				$batch_part [] = array ('method' => 'POST', 'relative_url' => $recipient . '/feed', 'body' => 'message=' . $msg [$recipient]);
			}
			$batch [] = Zend_Json::encode($batch_part);
		
		}
		
		if ($recipients_count > $batch_limit) {
			$ft = ceil($recipients_count / $batch_limit);
			for($i = 1; $i <= $ft; $i ++) {
				$from = ($i * $batch_limit) - $batch_limit;
				$to = $batch_limit;
				$limited_array = array_slice($recipients, $from, $to);
				$batch_part = array ();
				foreach ($limited_array as $recipient) {
					$batch_part [] = array ('method' => 'POST', 'relative_url' => $recipient . '/feed', 'body' => 'message=' . $msg [$recipient]);
				}
				$batch_part = Zend_Json::encode($batch_part);
				array_push($batch, $batch_part);
			}
		
		}
		
		try {
			$client = new Zend_Http_Client();
			$client->setConfig(array ('keepalive' => true, 'timeout' => 180));
			$client->setUri('https://graph.facebook.com');
			$client->setMethod(Zend_Http_Client::POST);
			
			$responses = array ();
			
			foreach ($batch as $post) {
				$client->setParameterPost(array ('access_token' => $token, 'batch' => $post));
				$response = $client->request();
				
				if ($response->getStatus() !== 200) $success = false;
				
				$responses [] = Zend_Json_Decoder::decode($response->getBody());
			}
			$codes = array ();
			foreach ($responses as $response_data) {
				foreach ($response_data as $key => $data) {
					if ($data ['code'] !== 200) {
						$failed [] = (string) $recipients [$key];
						$codes [] = $data;
					}
				
				}
			}
			if ($failed && $recipients_count == count($failed)) $success = false;
			
			$message = (in_array(403, $codes)) ? $this->__('Some of your friends were skipped for one of the following reasons: you invited them before, some of your friends have disabled wall posts or you have exceeded Facebook limit for sending messages.') : false;
			$session->setStwSkippedReason($message);
		} catch (Exception $e) {
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			$success = false;
		}
		$failed_ids = (count($failed)) ? $failed : false;
		
		Mage::register('stw_direct_data', array ('success' => $success, 'message' => $message, 'skipped' => $failed_ids));
	}
}