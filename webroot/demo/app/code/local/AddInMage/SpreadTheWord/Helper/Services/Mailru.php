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

class AddInMage_SpreadTheWord_Helper_Services_Mailru extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = Mage::helper('spreadtheword')->__('Mailru');
		$service_data ['type'] = 'social';
		$service_data ['specific_view'] = false;
		$service_data ['oauth_support'] = true;
		$service_data ['controller'] = 'oauth2';
		$service_data ['popup_size'] = array ('width' => '550', 'height' => '470');
		
		return $service_data;
	
	}

	public function getConfiguration_v_2($clientId, $clientSecret, $service)
	{
		$config = array ('callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth2?service=' . $service, 'authorizeEndPoint' => 'https://connect.mail.ru/oauth/authorize', 'tokenEndPoint' => 'https://connect.mail.ru/oauth/token', 'requestedRights' => array ('stream messages'), 'responseType' => 'code', 'clientId' => $clientId, 'grandType' => 'authorization_code', 'clientSecret' => $clientSecret);
		
		return $config;
	}

	public function getExtractedToken($access)
	{
		return $access;
	}

	public function sendInvitations($recipients, $message)
	{
		$session = Mage::getSingleton('customer/session');
		$data = $session->getStwClientData();
		$success = true;
		$failed = array ();
		$codes = array ();
		$msg = $message ['body'];
		$recipients_count = count($recipients);
		$token = $data ['token'];
		$clientId = $data ['client_id'];
		$clientSecret = $data ['client_secret'];
		try {
			$client = new Zend_Http_Client();
			$client->setConfig(array ('keepalive' => true, 'timeout' => 180));
			$client->setUri('http://www.appsmail.ru/platform/api');
			$client->setMethod(Zend_Http_Client::GET);
			foreach ($recipients as $recipient) {
				$params = 'app_id=' . $clientId . 'format=jsonmessage=' . $msg [$recipient] . 'method=messages.postsecure=1session_key=' . $token . 'uid=' . $recipient;
				$sig = md5($params . $clientSecret);
				
				$client->setParameterGet(array ('app_id' => $clientId, 'format' => 'json', 'message' => $msg [$recipient], 'method' => 'messages.post', 'secure' => '1', 'session_key' => $token, 'sig' => $sig, 'uid' => $recipient)

				);
				
				$response = $client->request();
				$codes [] = $response->getStatus();
			}
			foreach ($codes as $key => $code) {
				if ($code !== 200) {
					$failed [] = (string) $recipients [$key];
				}
			}
			if ($failed && $recipients_count == count($failed)) $success = false;
		} catch (Exception $e) {
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			$success = false;
		}
		
		$failed_ids = (count($failed)) ? $failed : false;
		Mage::register('stw_direct_data', array ('success' => $success, 'message' => false, 'skipped' => $failed_ids));
	}

	public function getOauthContacts_v_2($token, $clientId, $clientSecret)
	{
		try {
			
			$session = Mage::getSingleton('customer/session');
			
			$token = unserialize($token);
			$token = Zend_Json::decode($token);
			$client = new Zend_Http_Client();
			$client->setConfig(array ('timeout' => 180));
			
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			
			else {
				$params = 'app_id=' . $clientId . 'ext=1format=jsonmethod=users.getInfosecure=1session_key=' . $token ['access_token'];
				$sig = md5($params . $clientSecret);
				$client->setUri('http://www.appsmail.ru/platform/api');
				$client->setParameterGet(array ('method' => 'users.getInfo', 'ext' => '1', 'app_id' => $clientId, 'sig' => $sig, 'secure' => '1', 'session_key' => $token ['access_token'], 'format' => 'json'));
				$response_user = $client->request(Zend_Http_Client::GET);
				$user_data = Zend_Json::decode($response_user->getBody());
				
				$session->setCurrentSenderData(array ('name' => $user_data [0] ['first_name'] . ' ' . $user_data [0] ['last_name'], 'email' => $user_data [0] ['email'], 'can_send' => true));
			}
			
			$session->setStwClientData(array ('token' => $token ['access_token'], 'client_id' => $clientId, 'client_secret' => $clientSecret));
			$params = 'app_id=' . $clientId . 'ext=1format=jsonmethod=friends.getsecure=1session_key=' . $token ['access_token'];
			$sig = md5($params . $clientSecret);
			$client->setUri('http://www.appsmail.ru/platform/api');
			$client->setParameterGet(array ('method' => 'friends.get', 'ext' => '1', 'app_id' => $clientId, 'sig' => $sig, 'secure' => '1', 'session_key' => $token ['access_token'], 'format' => 'json'));
			$response_contacts = $client->request(Zend_Http_Client::GET);
			$contacts = Zend_Json::decode($response_contacts->getBody());
			
			$string_to_lower = new Zend_Filter_StringToLower();
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			$contact_list = array ();
			foreach ($contacts as $contact) {
				$c = array ();
				
				if (isset($contact ['uid'])) $c ['id'] = $contact ['uid'];
				
				$c ['name'] = '';
				if (isset($contact ['first_name'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($contact ['first_name']));
				if (isset($contact ['first_name']) && isset($contact ['last_name'])) $c ['name'] .= ' ';
				if (isset($contact ['last_name'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($contact ['last_name']));
				
				if (isset($contact ['link'])) $c ['link'] = $contact ['link'];
				if (isset($contact ['pic_small'])) $c ['picture'] = (substr($contact ['pic_small'], strrpos($contact ['pic_small'], '/') + 1) !== '_avatarsmall') ? $contact ['pic_small'] : null;
				
				$contact_list [] = $c;
			}
			
			Mage::getSingleton('customer/session')->setCurrentService(array ('service' => $this->__('Mailru'), 'type' => 'social', 'code' => 'mailru'));
			$contact_list = array_values(array_filter($contact_list));
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'social', 'mailru');
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to Mailru Service. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}
}