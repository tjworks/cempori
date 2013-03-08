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

class AddInMage_SpreadTheWord_Helper_Services_Twitter extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = 'Twitter';
		$service_data ['type'] = 'social';
		$service_data ['specific_view'] = false;
		$service_data ['oauth_support'] = true;
		$service_data ['controller'] = 'oauth1';
		$service_data ['popup_size'] = array ('width' => '700', 'height' => '600');
		
		return $service_data;
	
	}

	public function getConfiguration_v_1($consumer_key, $consumer_secret, $service)
	{
		$config = array ('requestScheme' => AddInMage_Oauth::REQUEST_SCHEME_HEADER, 'callbackUrl' => Mage::getUrl("spreadtheword") . 'oauth1?service=' . $service, 'userAuthorizationUrl' => 'https://api.twitter.com/oauth/authorize', 'requestTokenUrl' => 'http://api.twitter.com/oauth/request_token', 'accessTokenUrl' => 'https://api.twitter.com/oauth/access_token', 'version' => '1.0', 'signatureMethod' => 'HMAC-SHA1', 'consumerKey' => $consumer_key, 'consumerSecret' => $consumer_secret);
		
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

	public function getRedirect($consumer, $domain = null, $mobile = null)
	{
		$consumer->redirect();
	}

	public function sendInvitations($recipients, $message)
	{
		$session = Mage::getSingleton('customer/session');
		$data = $session->getStwClientData();
		$token = $data [0];
		$config = $data [1];
		$success = true;
		$failed = array ();
		$codes = array ();
		$client = $token->getHttpClient($config);
		$msg = $message ['body'];
		$recipients_count = count($recipients);
		try {
			
			$client->setConfig(array ('keepalive' => true, 'timeout' => 180));
			$client->setUri('https://api.twitter.com/1/direct_messages/new.json');
			$client->setMethod(Zend_Http_Client::POST);
			
			foreach ($recipients as $recipient) {
				$client->setParameterPost(array ('user_id' => $recipient, 'text' => $msg [$recipient], 'wrap_links' => true));
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

	public function getOauthContacts_v_1($token, $configuration)
	{
		try {
			$session = Mage::getSingleton('customer/session');
			$client = $token->getHttpClient($configuration);
			$client->setConfig(array ('timeout' => 180));
			$session->setStwClientData(array ($token, $configuration));
			
			$client->setUri('http://api.twitter.com/1/account/rate_limit_status.json');
			$response = $client->request(Zend_Http_Client::GET);
			$limit_data = Zend_Json::decode($response->getBody());
			
			$remaining_hits = $limit_data ['remaining_hits'];
			$reset_time_in_seconds = $limit_data ['reset_time_in_seconds'];
			$now = time();
			$zeroing_time = round(($reset_time_in_seconds - $now) / 60);
			$string_to_lower = new Zend_Filter_StringToLower('UTF-8');
			$string_to_lower->setEncoding('UTF-8');
			$helper = Mage::helper('spreadtheword');
			$limit_message = $this->__('You have exceeded Twitter limit for sending direct messages. Please try again in %s minutes.', $zeroing_time);
			if ($remaining_hits < 2) {
				Mage::getSingleton('core/session')->addNotice($limit_message);
				Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
				return;
			}
			
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			else $session->setCurrentSenderData(array ('can_send' => false));
			
			$client->setUri('http://api.twitter.com/1/followers/ids.json');
			$client->setParameterGet(array ('stringify_ids' => 'true'));
			$response = $client->request(Zend_Http_Client::GET);
			
			$followers = Zend_Json::decode($response->getBody());
			$followers = $followers ['ids'];
			
			$followers_count = count($followers);
			
			$contact_list = array ();
			
			// Current Twitter Rest API limit
			$users_lookup_limit = 100;
			
			if ($followers_count !== 0 && $followers_count <= $users_lookup_limit) {
				$client->setUri('http://api.twitter.com/1/users/lookup.json');
				$client->setParameterGet(array ('user_id' => implode(',', $followers)));
				$response = $client->request(Zend_Http_Client::GET);
				$contacts = Zend_Json::decode($response->getBody());
				
				foreach ($contacts as $contact) {
					$c = array ();
					
					$c ['id'] = $contact ['id_str'];
					
					if (isset($contact ['name'])) {
						$c ['name'] = '';
						$full_name = explode(' ', $contact ['name']);
						
						if (isset($full_name [0])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [0]));
						
						if (isset($full_name [0]) && isset($full_name [1])) $c ['name'] .= ' ';
						
						if (isset($full_name [1])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [1]));
					} 

					else
						$c ['name'] = $helper->firstToUpper($string_to_lower->filter($contact ['screen_name']));
					
					if (isset($contact ['screen_name'])) $c ['link'] = 'http://www.twitter.com/' . $contact ['screen_name'];
					
					if (isset($contact ['profile_image_url'])) $c ['picture'] = $contact ['profile_image_url'];
					
					$contact_list [] = $c;
				}
			}
			if ($followers_count !== 0 && $followers_count > $users_lookup_limit) {
				$ft = round($followers_count / $users_lookup_limit);
				$reserved_hits = $remaining_hits - $ft;
				
				if ($reserved_hits >= $ft) {
					$contacts_stack = array ();
					for($i = 1; $i <= $ft; $i ++) {
						
						$from = ($i * $users_lookup_limit) - $users_lookup_limit;
						$to = $users_lookup_limit;
						$limited_array = array_slice($followers, $from, $to);
						$client->setUri('http://api.twitter.com/1/users/lookup.json');
						$client->setParameterGet(array ('user_id' => implode(',', $limited_array)));
						$response = $client->request(Zend_Http_Client::GET);
						$contacts = Zend_Json::decode($response->getBody());
						array_push($contacts_stack, $contacts);
					}
					
					foreach ($contacts_stack as $contacts_array) {
						foreach ($contacts_array as $contact_data) {
							$c = array ();
							
							$c ['id'] = $contact_data ['id_str'];
							
							if (isset($contact_data ['name'])) {
								$c ['name'] = '';
								$full_name = explode(' ', $contact_data ['name']);
								
								if (isset($full_name [0])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [0]));
								
								if (isset($full_name [0]) && isset($full_name [1])) $c ['name'] .= ' ';
								
								if (isset($full_name [1])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [1]));
							} 

							else
								$c ['name'] = $helper->firstToUpper($string_to_lower->filter($contact_data ['screen_name']));
							
							if (isset($contact_data ['screen_name'])) $c ['link'] = 'http://www.twitter.com/' . $contact_data ['screen_name'];
							
							if (isset($contact_data ['profile_image_url'])) $c ['picture'] = $contact_data ['profile_image_url'];
							
							$contact_list [] = $c;
						}
					}
				} else {
					Mage::getSingleton('core/session')->addNotice($limit_message);
					Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
					return;
				}
			}
			
			Mage::getSingleton('customer/session')->setCurrentService(array ('service' => 'Twitter', 'type' => 'social', 'code' => 'twitter'));
			$contact_list = array_values(array_filter($contact_list));
			
			return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'social', 'twitter');
		
		} catch (Exception $e) {
			$error_message = $this->__('Twitter is over capacity. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
	}
}