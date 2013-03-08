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

class AddInMage_SpreadTheWord_Helper_Data extends Mage_Core_Helper_Abstract
{

	const XML_PATH_RULES = 'addinmage_spreadtheword/behaviour/rules';

	public function getServices()
	{
		$services = array ();
		$service_model = Mage::getModel('spreadtheword/services')->getCollection();
		foreach ($service_model as $service) {
			if ($service->getType() == 'social') $services ['social'] [] = $service->getName();
			if ($service->getType() == 'mail') $services ['mail'] [] = $service->getName();
			if ($service->getType() == 'file') $services ['file'] [] = $service->getName();
		}
		return $services;
	}

	public function getOauthData($service)
	{
		$oauth_data = Mage::getStoreConfig('addinmage_spreadtheword/oauth/oauth_data', Mage::app()->getStore());
		$oauth_data = unserialize($oauth_data);
		$oauth_settings = array ();
		foreach ($oauth_data ['services'] as $i => $service_name) {
			if (! $i || ! $service_name) {
				continue;
			}
			$oauth_consumer = $oauth_data ['oauth_consumer'] [$i];
			$oauth_secret = $oauth_data ['oauth_secret'] [$i];
			$oauth_settings [$service_name] = array ('oauth_consumer' => $oauth_consumer, 'oauth_secret' => $oauth_secret);
		}
		return array ('oauth_consumer' => $oauth_settings [$service] ['oauth_consumer'], 'oauth_secret' => $oauth_settings [$service] ['oauth_secret']);
	}

	public function getServiceArray($for_report = false)
	{
		$service_groups = $this->getServices();
		
		if ($for_report) $service_groups ['manual'] = array ('manual');
		
		$services = array ();
		foreach ($service_groups as $group) {
			foreach ($group as $service) {
				if (class_exists('AddInMage_SpreadTheWord_Helper_Services_' . ucfirst($service))) {
					$service_data = Mage::helper('spreadtheword/services_' . $service)->getInfo();
					$services [] = array ('value' => $service, 'label' => $service_data ['service_name']);
				}
			}
		}
		return $services;
	}

	public function getPeriods()
	{
		return array (array ('value' => '2days', 'label' => Mage::helper('spreadtheword')->__('2 days')), array ('value' => '5day', 'label' => Mage::helper('spreadtheword')->__('5 days')), array ('value' => '1week', 'label' => Mage::helper('spreadtheword')->__('1 week')), array ('value' => '2week', 'label' => Mage::helper('spreadtheword')->__('2 weeks')), array ('value' => '3week', 'label' => Mage::helper('spreadtheword')->__('3 weeks')), array ('value' => '1month', 'label' => Mage::helper('spreadtheword')->__('1 month')), array ('value' => '2month', 'label' => Mage::helper('spreadtheword')->__('2 months')));
	}

	public function refreshStwCustomerId($id)
	{
		if (Mage::getStoreConfig('addinmage_spreadtheword/tracking/lifetime_tracking', Mage::app()->getStore())) {
			$session = Mage::getSingleton('customer/session');
			if ($session->isLoggedIn()) {
				$customer_id = $session->getCustomer()
					->getId();
				$customer = Mage::getModel('customer/customer')->load($customer_id);
				$customer->setStwCustomerId($id)
					->save();
			}
		}
	}

	public function getStwCustomerId()
	{
		if (Mage::getStoreConfig('addinmage_spreadtheword/tracking/lifetime_tracking', Mage::app()->getStore())) {
			$session = Mage::getSingleton('customer/session');
			if ($session->isLoggedIn()) {
				$customer_id = $session->getCustomer()
					->getId();
				$customer = Mage::getModel('customer/customer')->load($customer_id);
				return ($customer->getStwCustomerId()) ? $customer->getStwCustomerId() : false;
			}
		}
		return false;
	}

	public function getMaxChars()
	{
		return (int) Mage::getStoreConfig('addinmage_spreadtheword/email/max_chars', Mage::app()->getStore());
	}

	public function getDirectMaxChars()
	{
		return (int) Mage::getStoreConfig('addinmage_spreadtheword/message/max_chars_direct', Mage::app()->getStore());
	}

	public function inviteCustomers()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/behaviour/invite_customers', Mage::app()->getStore());
	}

	public function inviteAlreadyInvited()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/behaviour/invite_invited', Mage::app()->getStore());
	}

	public function getMaxRecipients()
	{
		return (int) Mage::getStoreConfig('addinmage_spreadtheword/services/max_recipients', Mage::app()->getStore());
	}

	public function handleError($message, $type)
	{
		switch ($type) {
		case 'success':
		Mage::getSingleton('core/session')->addSuccess($message);
		break;
		case 'notice':
		Mage::getSingleton('core/session')->addNotice($message);
		break;
		case 'error':
		Mage::getSingleton('core/session')->addError($message);
		break;
		}
	}

	public function getMaxUpload($user_print = false)
	{
		$max_upload = $this->returnBytes(ini_get('upload_max_filesize'));
		$max_post = $this->returnBytes(ini_get('post_max_size'));
		$memory_limit = $this->returnBytes(ini_get('memory_limit'));
		$upload_mb = min($max_upload, $max_post, $memory_limit);
		if ($user_print) return $upload_mb / 1024 / 1024;
		return $upload_mb;
	}

	private function returnBytes($val)
	{
		$val = trim($val);
		if (substr($val, - 1, 1) == "M") $val = substr($val, 0, strlen($val) - 1) * 1024 * 1024;
		elseif (substr($val, - 1, 1) == "K") $val = substr($val, 0, strlen($val) - 1) * 1024;
		elseif (substr($val, - 1, 1) == "G") $val = substr($val, 0, strlen($val) - 1) * 1024 * 1024 * 1024;
		return $val;
	}

	public function checkProvider($service, $controller)
	{
		if (! @class_exists('AddInMage_SpreadTheWord_Helper_Services_' . ucfirst($service))) {
			$message = $this->__('Sorry, this service is not supported.');
			Mage::getSingleton('core/session')->addError($message);
			return false;
		}
		
		$service_data = Mage::getModel('AddInMage_SpreadTheWord_Helper_Services_' . ucfirst($service))->getInfo();
		$service_type_enabled = Mage::getStoreConfig('addinmage_spreadtheword/services/' . $service_data ['type'], Mage::app()->getStore());
		$services = Mage::getStoreConfig('addinmage_spreadtheword/services/' . $service_data ['type'] . '_services', Mage::app()->getStore());
		$services = explode(',', $services);
		//28
		if ($service_type_enabled && in_array($service, $services)) {
			if ($service_data ['controller'] !== $controller && $controller !== 'live') {
				if ($service_data ['controller'] == 'file') $message = $this->__('Sorry, this service is not supporteda');
				
				else $message = $this->__('Service does not support this type of authorization!');
				Mage::getSingleton('core/session')->addNotice($message);
				return false;
			}
			
			return true;
		} else {
			$message = $this->__('Sorry, this service is temporarily not supported!');
			Mage::getSingleton('core/session')->addError($message);
			return false;
		}
	}

	public function firstToUpper($string, $e = 'utf-8')
	{
		if (function_exists('mb_strtoupper') && function_exists('mb_substr') && ! empty($string)) {
			$string = mb_strtolower($string, $e);
			$upper = mb_strtoupper($string, $e);
			preg_match('#(.)#us', $upper, $matches);
			if (! empty($matches [1])) $string = $matches [1] . mb_substr($string, 1, mb_strlen($string, $e), $e);
		} else {
			$string = ucfirst($string);
		}
		return $string;
	}

	public function processWindows($url)
	{
		echo '
    		<style>body {display:none}</style>
    		<script type="text/javascript">
    		//document.domain=document.domain;
			if(window.opener){window.opener.location= "' . $url . '";window.close();}
			else window.location= "' . $url . '";
			</script>';
		return;
	}

	public function getAlphaMode($letters)
	{
		$mode = Mage::getStoreConfig('addinmage_spreadtheword/contact/alpha_index', Mage::app()->getStore());
		$scripts = Zend_Locale::getTranslationList('Script', 'en');
		
		switch ($mode) {
		case 'store':
		$locale = Mage::app()->getLocale()
			->getDefaultLocale();
		if (strpos($locale, '_') !== false) {
			$locale = substr($locale, 0, strpos($locale, '_'));
		}
		if (! Zend_Locale::isLocale($locale)) $locale = 'en';
		$list = Zend_Locale_Data::getList('en', 'ScriptToLanguage');
		$ls = explode(' ', $list [$locale]);
		$script = $scripts [$ls [0]];
		break;
		case 'browser':
		$locale = new Zend_Locale(Zend_Locale::BROWSER);
		$locale = $locale->toString();
		if (strpos($locale, '_') !== false) {
			$locale = substr($locale, 0, strpos($locale, '_'));
		}
		if (! Zend_Locale::isLocale($locale)) $locale = 'en';
		$list = Zend_Locale_Data::getList('en', 'ScriptToLanguage');
		$ls = explode(' ', $list [$locale]);
		$script = $scripts [$ls [0]];
		break;
		case 'specific':
		$alphabet = Mage::getStoreConfig('addinmage_spreadtheword/contact/alphabets', Mage::app()->getStore());
		$script = $scripts [$alphabet];
		break;
		case 'smart':
		$script = $this->getScriptInSmartMode($letters);
		break;
		}
		return $script;
	}

	public function getScriptInSmartMode($letters)
	{
		$allowed_scripts = Mage::getModel('spreadtheword/configuration_alphabets')->getAllowedScripts();
		$allowed_scripts = array_flip($allowed_scripts);
		foreach ($allowed_scripts as $value => $script) {
			$i = 0;
			foreach ($letters as $letter) {
				if (preg_match('/^[\p{' . $value . '}]$/u', $letter)) $i ++;
			}
			$allowed_scripts [$value] = $i;
		}
		$sc = array_search(max($allowed_scripts), $allowed_scripts);
		if (max($allowed_scripts) == 0) $sc = 'Latin';
		return $sc;
	}

	public function getRuleMode($get_rule_object = false)
	{
		$selected_rule = Mage::getStoreConfig(self::XML_PATH_RULES, Mage::app()->getStore());
		if ($get_rule_object) {return Mage::getModel('spreadtheword/rules')->load($selected_rule);}
		$rule_mode = Mage::getModel('spreadtheword/rules')->load($selected_rule)
			->getRuleMode();
		if ($selected_rule == 'inv' || ! $rule_mode) return 'inv';
		return $rule_mode;
	}

	public function getRule()
	{
		$selected_rule = Mage::getStoreConfig(self::XML_PATH_RULES, Mage::app()->getStore());
		$rule_mode = Mage::getModel('spreadtheword/rules')->load($selected_rule);
		if ($selected_rule == 'inv' || ! $selected_rule || ! $rule_mode || $rule_mode->getRuleMode() == 'noaction') return 'inv';
		return $rule_mode;
	}

	public function getRuleModes($to_options = false)
	{
		$modes = array ('noaction' => $this->__('Invitation only'), 'action_d_sen' => $this->__('Invitation sender gets discount'), 'action_d_frd' => $this->__('Invited friends get discount'), 'action_d_all' => $this->__('Senders and invitees get discount'));
		
		if ($to_options) {
			$options = array ();
			if (count($modes) > 1) {
				$options [] = array ('value' => '', 'label' => $this->__('Please Select Rule Mode'));
			}
			foreach ($modes as $type => $label) {
				$options [] = array ('value' => $type, 'label' => $label);
			}
			return $options;
		} else
			return $modes;
	}

	public function getDiscountOptions($to_options = false)
	{
		$discounts = array ('fixed' => $this->__('Fixed discount'), 'dynamic' => $this->__('Dynamic discount'), 'dynamic_levels' => $this->__('Dynamic discount with pre-defined levels'));
		
		if ($to_options) {
			$options = array ();
			if (count($discounts) > 1) {
				$options [] = array ('value' => '', 'label' => $this->__('Please Select Discount Type'));
			}
			foreach ($discounts as $type => $label) {
				$options [] = array ('value' => $type, 'label' => $label);
			}
			return $options;
		} else
			return $discounts;
	}

	public function getDiscountRules($to_options = false, $validation = false, $format = false)
	{
		$sales_rules = Mage::getModel('salesrule/rule')->getCollection();
		$rules = $sales_rules->getData();
		$format_helper = Mage::helper('core');
		$coupons_list = array ();
		foreach ($rules as $rule) {
			$rule_id = $rule ['rule_id'];
			if (! $validation) {
				$rule_name = $rule ['name'];
				if ($rule ['is_active'] && $rule ['coupon_type'] == Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC && ! empty($rule ['discount_amount']) && $rule ['simple_action'] !== Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION) {
					if (! $format) $coupons_list [] = array ('value' => $rule_id, 'label' => $rule_name);
					else {
						
						switch ($rule ['simple_action']) {
						case Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION:
						case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION:
						$coupons_list [] = array ('value' => $rule_id, 'label' => str_pad('[' . (int) $rule ['discount_amount'] . '%] ', 12, '-') . ' ' . $rule_name);
						break;
						case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
						case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION:
						case Mage_SalesRule_Model_Rule::CART_FIXED_ACTION:
						$coupons_list [] = array ('value' => $rule_id, 'label' => str_pad('[' . $format_helper->currency($rule ['discount_amount'], true, false) . '] ', 12, '-') . ' ' . $rule_name);
						break;
						}
					}
				}
			
			} else
				$coupons_list [] = $rule_id;
		}
		
		if ($to_options) {
			$options = array ();
			foreach ($coupons_list as $coupon) {
				$options [] = array ('value' => $coupon ['value'], 'label' => $coupon ['label']);
			}
			foreach ($options as $key => $row) {
				$label [$key] = $row ['label'];
				$value [$key] = $row ['value'];
			}
			array_multisort($label, SORT_ASC, $value, SORT_DESC, $options);
			if (count($options) >= 0) array_unshift($options, array ('value' => '', 'label' => $this->__('Please Select Discount Configuration')));
			
			return $options;
		} else
			return $coupons_list;
	}

	public function getApplication()
	{
		$model = Mage::registry('current_rule');
		if (! ($model instanceof AddInMage_SpreadTheWord_Model_Rules)) {
			Mage::throwException(Mage::helper('spreadtheword')->__('Rule model not loaded.'));
		}
		
		return $model;
	}

	public function extractRightCustomRule($custom_rules)
	{
		
		$friends = Mage::getSingleton('customer/session')->getFriends();
		$current_service = $friends [0] ['source'];
		$friends_count = count($friends);
		
		$ex_services = array ();
		list ($month, $day, $year) = explode('/', date('m/d/Y', Mage::app()->getLocale()
			->storeTimeStamp()));
		$now = mktime(0, 0, 0, $month, $day, $year);
		
		foreach ($custom_rules as $service) {
			if ($service ['service'] == $current_service) $ex_services [] = $service;
		}
		
		if (count($ex_services) == 1) {
			$in_date_range = null;
			$min_count = null;
			$discount_type = (isset($ex_services [0] ['values'])) ? 'values' : 'discount_rule';
			if (! empty($ex_services [0] ['from_date'])) {
				$in_date_range = (($now >= $ex_services [0] ['from_date']) && ($now <= $ex_services [0] ['to_date'])) ? true : false;
			}
			if (! empty($ex_services [0] ['min_friends'])) {
				$min_count = ($friends_count >= $ex_services [0] ['min_friends']) ? true : false;
			}
			if (($in_date_range && $min_count) || ($in_date_range && is_null($min_count)) || ($min_count && is_null($in_date_range)) || (is_null($min_count) && is_null($in_date_range))) {
				return array ('discount_type' => $ex_services [0] ['discount_type'], 'discount_rule' => $ex_services [0] [$discount_type]);
			} else
				return false;
		} 

		elseif (count($ex_services) > 1) {
			$range_min = array ();
			$range = array ();
			$min = array ();
			$none = array ();
			
			foreach ($ex_services as $service) {
				if (! empty($service ['from_date']) && ! empty($service ['min_friends']) && ($now >= $service ['from_date']) && ($now <= $service ['to_date']) && ($friends_count >= $service ['min_friends'])) $range_min [] = $service;
				if (! empty($service ['from_date']) && empty($service ['min_friends']) && ($now >= $service ['from_date']) && ($now <= $service ['to_date'])) $range [] = $service;
				if (empty($service ['from_date']) && ! empty($service ['min_friends']) && ($friends_count >= $service ['min_friends'])) $min [] = $service;
				if (empty($service ['from_date']) && empty($service ['min_friends'])) $none [] = $service;
			}
			
			if (! empty($range_min)) {
				$max_key = $this->get_max_key($range_min, 'min_friends');
				$discount_type = (isset($range_min [$max_key] ['values'])) ? 'values' : 'discount_rule';
				return array ('discount_type' => $range_min [$max_key] ['discount_type'], 'discount_rule' => $range_min [$max_key] [$discount_type]);
			}
			if (! empty($range)) {
				$discount_type = (isset($range [0] ['values'])) ? 'values' : 'discount_rule';
				return array ('discount_type' => $range [0] ['discount_type'], 'discount_rule' => $range [0] [$discount_type]);
			}
			if (! empty($min)) {
				$max_key = $this->get_max_key($min, 'min_friends');
				$discount_type = (isset($min [$max_key] ['values'])) ? 'values' : 'discount_rule';
				return array ('discount_type' => $min [$max_key] ['discount_type'], 'discount_rule' => $min [$max_key] [$discount_type]);
			}
			if (! empty($none)) {
				$discount_type = (isset($none [0] ['values'])) ? 'values' : 'discount_rule';
				return array ('discount_type' => $none [0] ['discount_type'], 'discount_rule' => $none [0] [$discount_type]);
			}
		} 

		else
			return false;
	}

	protected function get_max_key($array, $key)
	{
		$max = 0;
		foreach ($array as $index => $i) {
			$max = max($max, $i [$key]);
			if ($i [$key] == $max) $p = $index;
		}
		return $p;
	}

	public function stwLog($method, $e)
	{
		$file = 'addinmage/stw.log';
		$environment = "\n";
		$environment .= 'PHP: ' . phpversion();
		$environment .= "\n";
		$environment .= 'MAGENTO: ' . Mage::getVersion();
		$environment .= "\n";
		$environment .= 'TRACE: ' . $method;
		$environment .= "\n";
		$environment .= $e->__toString();
		$environment .= "\n \n";
		Mage::log($environment, null, $file);
	}

	public function expireNotification($problems)
	{
		Mage::getModel('adminnotification/inbox')->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL)
			->setTitle($this->__('Some Spread The Word rules were disabled because their coupon codes expired.'))
			->setDateAdded(gmdate('Y-m-d H:i:s'))
			->setUrl(false)
			->setDescription($problems)
			->save();
	}

	public function getSendEmailReFactoredFlag()
	{
		$send_ref = false;
		if (version_compare(Mage::getVersion(), '1.5.0.1') >= 0) $send_ref = true;
		else $send_ref = false;
		return $send_ref;
	}

	public function isAjaxImplemented()
	{
		$implemented = false;
		if (version_compare(Mage::getVersion(), '1.4.2.0') >= 0) $implemented = true;
		else $implemented = false;
		return $implemented;
	}

	public function isFileTransferImplemented()
	{
		$implemented = false;
		if (version_compare(Mage::getVersion(), '1.4.2.0') >= 0) $implemented = true;
		else $implemented = false;
		return $implemented;
	}

	public function formatDiscount($amount, $simple_action)
	{
		$result = '';
		switch ($simple_action) {
		case Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION:
		case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION:
		$result = (int) $amount . '%';
		break;
		
		case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
		case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION:
		case Mage_SalesRule_Model_Rule::CART_FIXED_ACTION:
		$format_helper = Mage::helper('core');
		$result = $format_helper->currency($amount, true, false);
		break;
		}
		return $result;
	}

	public function formatDiscountByType($amount, $type)
	{
		$result = '';
		switch ($type) {
		case 'percent':
		$result = (int) $amount . '%';
		break;
		
		case 'fixed':
		$format_helper = Mage::helper('core');
		$result = $format_helper->currency($amount, true, false);
		break;
		}
		return $result;
	}

	public function getDiscountAmountType($simple_action)
	{
		$type = '';
		switch ($simple_action) {
		case Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION:
		case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION:
		$type = 'percent';
		break;
		
		case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
		case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION:
		case Mage_SalesRule_Model_Rule::CART_FIXED_ACTION:
		$type = 'fixed';
		break;
		}
		return $type;
	}

	public function getSimpleActionsByType($type)
	{
		$simple_actions = array ();
		switch ($type) {
		case 'percent':
		$simple_actions = array (Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION, Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION);
		break;
		case 'fixed':
		$simple_actions = array (Mage_SalesRule_Model_Rule::BY_FIXED_ACTION, Mage_SalesRule_Model_Rule::TO_FIXED_ACTION, Mage_SalesRule_Model_Rule::CART_FIXED_ACTION);
		break;
		}
		return $simple_actions;
	}
}