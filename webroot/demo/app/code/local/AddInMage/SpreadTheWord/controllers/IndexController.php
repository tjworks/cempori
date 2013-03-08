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


class AddInMage_SpreadTheWord_IndexController extends Mage_Core_Controller_Front_Action
{
	protected $_sh;

	public function preDispatch()
	{
		parent::preDispatch();
		if (! Mage::getSingleton('customer/session')->isLoggedIn()) {
			$access_level = Mage::getStoreConfig('addinmage_spreadtheword/general/access', Mage::app()->getStore());
			if ($access_level == 'customer') {
				Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());
				$this->_redirect('customer/account/login');
			}
		}
	}

	public function indexAction()
	{
		
		$this->_processConfiguration();
		$session = Mage::getSingleton('customer/session');
		$session->unsFriends();
		$session->unsStwSuccess();
		$session->unsStwCaptcha();
		$this->loadLayout();
		$this->renderLayout();
	}

	protected function _processConfiguration()
	{
		$session = Mage::getSingleton('core/session');
		$session->unsStwConfig();
		$session->unsStwConfigHash();
		$rule = Mage::helper('spreadtheword')->getRule();
		
		if (is_object($rule)) {
			if (! $this->_testConfiguration($rule)) {
				$session->setStwConfig($rule);
				$session->setStwConfigHash(md5($rule->toString()));
			} else {
				$session->setStwConfig('noaction');
				$session->setStwConfigHash(md5('noaction'));
			}
		} else {
			$session->setStwConfig('noaction');
			$session->setStwConfigHash(md5('noaction'));
		}
	}

	protected function _testConfiguration($rule_obj)
	{
		$sales_rules = array ();
		$config = $rule_obj->getConfiguration();
		$rule_id = $rule_obj->getId();
		$rule_name = $rule_obj->getRuleName();
		
		if (! empty($config)) {
			$config = unserialize($config);
			if (isset($config ['manual_discount']) && ! empty($config ['manual_discount'])) $sales_rules [] = $config ['manual_discount'];
			if (isset($config ['rules'])) {
				foreach ($config ['rules'] as $participant) {
					if (isset($participant ['fixed_discount'])) $sales_rules [] = $participant ['fixed_discount'];
					if (isset($participant ['dynamic_discount'])) $sales_rules [] = $participant ['dynamic_discount'];
					if (isset($participant ['dynamic_levels_discount'])) {
						foreach ($participant ['dynamic_levels_discount'] as $level) {
							if (isset($level ['rule'])) $sales_rules [] = $level ['rule'];
						}
					}
				}
			}
			if (isset($config ['options'])) {
				foreach ($config ['options'] as $options) {
					foreach ($options as $option) {
						if (isset($option ['discount_rule'])) $sales_rules [] = $option ['discount_rule'];
						if (isset($option ['values'])) {
							foreach ($option ['values'] as $discount_level) {
								if (isset($discount_level ['discount_rule'])) $sales_rules [] = $discount_level ['discount_rule'];
							}
						}
					}
				}
			}
		}
		
		if (! empty($sales_rules)) {
			$sales_rules = array_unique($sales_rules);
			list ($month, $day, $year) = explode('/', date('m/d/Y', Mage::app()->getLocale()
				->storeTimeStamp()));
			$now = mktime(0, 0, 0, $month, $day, $year);
			$rules_to_unset = array ();
			foreach ($sales_rules as $sales_rule) {
				$coupon = Mage::getModel('salesrule/rule')->load($sales_rule);
				if ($coupon->getToDate()) {
					list ($exp_year, $exp_month, $exp_day) = explode('-', $coupon->getToDate());
					$expiring_timestamp = mktime(23, 59, 59, $exp_month, $exp_day, $exp_year);
					if ($expiring_timestamp < $now) {
						$rules_to_unset [] = $sales_rule;
					}
				}
			}
			if (! empty($rules_to_unset)) {
				$helper = Mage::helper('spreadtheword');
				$rule_obj->setConflicts(true);
				$codes = '';
				foreach ($rules_to_unset as $rule) {
					$sales_rule = Mage::getModel('salesrule/rule')->load($rule);
					if (! next($rules_to_unset)) $codes .= $helper->__('coupon code "%s" (id #%s)', $sales_rule->getName(), $sales_rule->getId());
					else $codes .= $helper->__('coupon code "%s" (id #%s), ', $sales_rule->getName(), $sales_rule->getId());
				}
				$errors = array ($helper->__('The following discounts associated with this rule has expired: %s. Please check Shopping Cart Price Rule configuration.', $codes));
				$rule_obj->setErrors(serialize($errors));
				$rule_obj->save();
				if (Mage::getStoreConfig('addinmage_spreadtheword/advanced/expire_notifications')) $helper->expireNotification($helper->__('The following coupon codes have expired: %s. Please check Shopping Cart Price Rule configuration.', $codes));
				$path = 'addinmage_spreadtheword/behaviour/rules';
				$rule_in_use = Mage::getModel('core/config_data')->getCollection()
					->addFilter('path', $path)
					->addFilter('value', $rule_obj->getId());
				if ($rule_in_use) {
					foreach ($rule_in_use as $rule_in_config) {
						$rule_in_config->delete();
					}
				}
				return true;
			}
		}
		return false;
	}
}