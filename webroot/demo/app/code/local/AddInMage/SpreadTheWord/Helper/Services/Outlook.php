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

class AddInMage_SpreadTheWord_Helper_Services_Outlook extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = Mage::helper('spreadtheword')->__('Outlook Online');
		$service_data ['type'] = 'mail';
		$service_data ['specific_view'] = false;
		$service_data ['oauth_support'] = true;
		$service_data ['controller'] = 'oauth2';
		$service_data ['popup_size'] = array ('width' => '1010', 'height' => '640');
		
		return $service_data;
	
	}

	public function getLiveUrl()
	{
		
		Mage::getSingleton('customer/session')->setStwStore(serialize(Mage::app()->getStore()));
		$oauth_settings = Mage::helper('spreadtheword')->getOauthData('outlook');
		$oauth_consumer = $oauth_settings ['oauth_consumer'];
		$oauth_secret = $oauth_settings ['oauth_secret'];
		$token = ('appid=' . $oauth_consumer . '&ts=' . time());
		$app = urlencode(($token) . '&sig=' . urlencode(base64_encode(hash_hmac('sha256', $token, substr(hash('sha256', 'SIGNATURE' . $oauth_secret, true), 0, 16), true))));
		
		$microsoft_live = 'https://consent.live.com/Delegation.aspx?RU=' . Mage::getUrl('spreadtheword/live') . '&ps=Contacts.View&pl=' . Mage::getBaseUrl() . '&app=' . $app;
		return $microsoft_live;
	
	}

	public function getContactData($consentToken)
	{
		$consentToken = urldecode($consentToken);
		$parts = explode('&', trim($consentToken));
		
		foreach ($parts as $part) {
			$va = explode('=', $part, 2);
			
			if (count($va) == 2) {
				$key = $va [0];
				$value = urldecode($va [1]);
				if ($key == 'delt') $delt = $value;
				else 
					if ($key == 'lid') $lid = $value;
					else 
						if ($key == 'eact') {
							$oauth_settings = Mage::helper('spreadtheword')->getOauthData('outlook');
							$secret = $oauth_settings ['oauth_secret'];
							$token = base64_decode($value);
							$cryptkey = substr(hash('sha256', 'ENCRYPTION' . $secret, true), 0, 16);
							parse_str(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $cryptkey, substr($token, 16), MCRYPT_MODE_CBC, substr($token, 0, 16)), $parsedToken);
							$delt = $parsedToken ['delt'];
							$lid = $parsedToken ['lid'];
						}
			
			} else {
				$error_message = $this->__('Unable to connect to Windows Live Service. Please try again in a few minutes.');
				Mage::getSingleton('core/session')->addError($error_message);
				echo '<style>body {display:none}</style>
				<script type="text/javascript">
				if(window.opener){window.opener.location= "' . Mage::getUrl("spreadtheword") . '";window.close();}
				else window.location= "' . Mage::getUrl("spreadtheword") . '";
				</script>';
				return;
			}
		}
		if ($delt && $lid) {
			try {
				$validator = new Zend_Validate_EmailAddress();
				$data = new Zend_Http_Client('https://livecontacts.services.live.com/users/@L@' . $lid . '/rest/invitationsbyemail');
				$data->setConfig(array ('timeout' => 180));
				$data->setHeaders(array ('Accept-Encoding: gzip', 'Content-Type: application/xml; charset=UTF-8', 'Authorization: DelegatedToken dt="' . $delt . '"'));
				$response = $data->request(Zend_Http_Client::GET);
				
				$xml = $response->getBody();
				if (function_exists('mb_convert_encoding')) $xml = @mb_convert_encoding($xml, 'utf-8', 'utf-8');
				else 
					if (function_exists('iconv')) $xml = @iconv('utf-8', 'utf-8', $xml);
				
				$contacts = Zend_Json::fromXml($xml);
				$contacts = Zend_Json::decode($contacts);
				$string_to_lower = new Zend_Filter_StringToLower();
				$string_to_lower->setEncoding('UTF-8');
				$helper = Mage::helper('spreadtheword');
				$contact_list = array ();
				if ($contacts ['LiveContacts'] ['Contacts'] ['Contact'] [0] ['PreferredEmail']) {
					foreach ($contacts ['LiveContacts'] ['Contacts'] ['Contact'] as $contact_data) {
						
						$c = array ();
						
						if (isset($contact_data ['PreferredEmail'])) $c ['id'] = $string_to_lower->filter($contact_data ['PreferredEmail']);
						
						$c ['name'] = '';
						if (isset($contact_data ['Profiles'] ['Personal'] ['FirstName'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($contact_data ['Profiles'] ['Personal'] ['FirstName']));
						if (isset($contact_data ['Profiles'] ['Personal'] ['FirstName']) && isset($contact_data ['Profiles'] ['Personal'] ['LastName'])) $c ['name'] .= ' ';
						if (isset($contact_data ['Profiles'] ['Personal'] ['LastName'])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($contact_data ['Profiles'] ['Personal'] ['LastName']));
						
						if (empty($c ['name'])) $c ['name'] = $c ['id'];
						if (! empty($c ['name']) && ! empty($c ['id']) && $validator->isValid($c ['id'])) $contact_list [] = $c;
					}
				
				}
				
				Mage::getSingleton('customer/session')->setCurrentService(array ('service' => 'Hotmail', 'type' => 'mail', 'code' => 'outlook'));
				$contact_list = array_values(array_filter($contact_list));
				return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'mail', 'outlook');
			} catch (Exception $e) {
				$error_message = $this->__('Unable to connect to Windows Live Service. Please try again in a few minutes.');
				Mage::getSingleton('core/session')->addError($error_message);
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
				Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
				return;
			}
		} else {
			$error_message = $this->__('Unable to connect to Windows Live Service. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			echo '<style>body {display:none}</style>
			<script type="text/javascript">
			if(window.opener){window.opener.location= "' . Mage::getUrl("spreadtheword") . '";window.close();}
			else window.location= "' . Mage::getUrl("spreadtheword") . '";
			</script>';
			return;
		}
	}

	public function getOauthContacts_v_2($token)
	{
		try {
			$session = Mage::getSingleton('customer/session');
			if ($session->isLoggedIn()) $session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
			else {
				$token = unserialize($token);
				$client = new Zend_Http_Client();
				$client->setConfig(array ('timeout' => 180));
				$client->setUri('https://apis.live.net/v5.0/me/');
				$client->setParameterGet(array ('access_token' => $token ['access_token']));
				$response = $client->request(Zend_Http_Client::GET);
				$profile_info = Zend_Json::decode($response->getBody());
				$session->setCurrentSenderData(array ('name' => $profile_info ['first_name'] . ' ' . $profile_info ['last_name'], 'email' => $profile_info ['emails'] ['preferred'], 'can_send' => true));
			}
			
			Mage::app()->getFrontController()
				->getResponse()
				->setRedirect(Mage::getUrl('*/live'));
		} catch (Exception $e) {
			$error_message = $this->__('Unable to connect to Windows Live Service. Please try again in a few minutes.');
			Mage::getSingleton('core/session')->addError($error_message);
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
		}
	}

	public function getConfiguration_v_2($clientId, $clientSecret, $service)
	{
		$config = array ('callbackUrl' => Mage::getUrl('spreadtheword') . 'oauth2?service=' . $service, 'authorizeEndPoint' => 'https://oauth.live.com/authorize', 'tokenEndPoint' => 'https://oauth.live.com/token', 'requestedRights' => 'wl.emails wl.basic', 'responseType' => 'code', 'clientId' => $clientId, 'grandType' => 'authorization_code', 'clientSecret' => $clientSecret);
		return $config;
	}

	public function getExtractedToken($access)
	{
		$array = Zend_Json::decode($access);
		
		if (! $array ['access_token']) return Mage::helper('spreadtheword')->handleError($this->__('Connection error. Please try again later.'), 'error');
		
		if ($array ['access_token']) return $array;
	}
}