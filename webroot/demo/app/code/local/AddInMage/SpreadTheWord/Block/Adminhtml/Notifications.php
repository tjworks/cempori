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

class AddInMage_SpreadTheWord_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{

	public function getExpNotice()
	{
		$model = Mage::getModel('spreadtheword/rules');
		$rules = $model->getCollection()
			->addFilter('errors', '')
			->addFilter('conflicts', '0')
			->addFieldToFilter('rule_mode', array ('neq' => 'noaction'));
		$sales_rules = array ();
		if ($rules->count() !== 0) {
			foreach ($rules as $rule) {
				$config = $rule->getConfiguration();
				$rule_id = $rule->getId();
				$rule_name = $rule->getRuleName();
				if (! empty($config)) {
					$config = unserialize($config);
					if (isset($config ['manual_discount']) && ! empty($config ['manual_discount'])) $sales_rules [$config ['manual_discount']] [] = $rule_id;
					if (isset($config ['rules'])) {
						foreach ($config ['rules'] as $participant) {
							if (isset($participant ['fixed_discount'])) $sales_rules [$participant ['fixed_discount']] [] = $rule_id;
							if (isset($participant ['dynamic_discount'])) $sales_rules [$participant ['dynamic_discount']] [] = $rule_id;
							if (isset($participant ['dynamic_levels_discount'])) {
								foreach ($participant ['dynamic_levels_discount'] as $level) {
									if (isset($level ['rule'])) $sales_rules [$level ['rule']] [] = $rule_id;
								}
							}
						}
					}
					if (isset($config ['options'])) {
						foreach ($config ['options'] as $options) {
							foreach ($options as $option) {
								if (isset($option ['discount_rule'])) $sales_rules [$option ['discount_rule']] [] = $rule_id;
								if (isset($option ['values'])) {
									foreach ($option ['values'] as $discount_level) {
										if (isset($discount_level ['discount_rule'])) $sales_rules [$discount_level ['discount_rule']] [] = $rule_id;
									}
								}
							}
						}
					}
				}
			}
		}
		if (! empty($sales_rules)) {
			$helper = Mage::helper('spreadtheword');
			list ($month, $day, $year) = explode('/', date('m/d/Y', Mage::app()->getLocale()
				->storeTimeStamp()));
			$now = mktime(0, 0, 0, $month, $day, $year);
			$now_plus_2_days = mktime(23, 59, 59, $month, $day + 2, $year);
			
			foreach ($sales_rules as $index => $rule) {
				$sales_rules [$index] = array_unique($rule);
			}
			$detailed_info = array ();
			$rules_to_unset = array ();
			foreach ($sales_rules as $index => $rule) {
				
				$coupon = Mage::getModel('salesrule/rule')->load($index);
				if ($coupon->getToDate()) {
					list ($exp_year, $exp_month, $exp_day) = explode('-', $coupon->getToDate());
					$expiring_timestamp = mktime(23, 59, 59, $exp_month, $exp_day, $exp_year);
					if (($expiring_timestamp >= $now) && ($expiring_timestamp <= $now_plus_2_days)) {
						$notice_expire_date = new Zend_Date($expiring_timestamp);
						$date = $notice_expire_date->toString(Mage::app()->getLocale()
							->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
						$exp_date = (mktime(0, 0, 0, $exp_month, $exp_day, $exp_year) == $now) ? $helper->__('today') : $date;
						$notice_soon = $helper->__('- Discount rule <strong>"%s" (id #%d)</strong> will expire on <strong>%s</strong>. The following Spread The Word rules will be affected: :', $coupon->getName(), $index, $exp_date);
						foreach ($rule as $rule_id) {
							if (! next($rule)) $notice_soon .= $helper->__(' rule #%d.',$rule_id);
							else $notice_soon .= $helper->__(' rule #%d,',$rule_id);
						}
						$detailed_info ['soon'] [] = $notice_soon;
					
					}
					if ($expiring_timestamp < $now) {
						$notice_expire_date = new Zend_Date($expiring_timestamp);
						$date = $notice_expire_date->toString(Mage::app()->getLocale()
							->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
						$notice_expired = $helper->__('- Discount rule <strong>"%s" (id #%d)</strong> expired on <strong>%s</strong>. The following Spread The Word rules were disabled:', $coupon->getName(), $index, $date);
						foreach ($rule as $rule_id) {
							$rules_to_unset [$rule_id] [] = array ('id' => $index, 'name' => $coupon->getName());
							if (! next($rule)) $notice_expired .= $helper->__(' rule #%d.',$rule_id);
							else $notice_expired .= $helper->__(' rule #%d,',$rule_id);
						}
						$detailed_info ['expired'] [] = $notice_expired;
					
					}
				}
			}
			if (! empty($rules_to_unset)) $this->unsetRules($rules_to_unset);
			if (! empty($detailed_info)) {
				Mage::getSingleton('adminhtml/session')->setExpNotice($detailed_info);
				return $detailed_info;
			}
		}
		Mage::getSingleton('adminhtml/session')->setExpNotice(false);
		return false;
	}

	protected function unsetRules($rules_to_unset)
	{
		$ids = array_keys($rules_to_unset);
		$desactivated_collection = Mage::getModel('spreadtheword/rules')->getCollection()
			->addFieldToFilter('id', array ('in' => $ids));
		if (count($desactivated_collection) !== 0) {
			$helper = Mage::helper('spreadtheword');
			try {
				foreach ($desactivated_collection as $rule) {
					$rule->setConflicts(true);
					$codes = '';
					$rc = array_keys($rules_to_unset [$rule->getId()]);
					$last_key = end($rc);
					foreach ($rules_to_unset [$rule->getId()] as $code_index => $code_data) {
						if ($code_index == $last_key) $codes .= $helper->__('coupon code "%s" (id #%s)', $code_data ['name'], $code_data ['id']);
						else $codes .= $helper->__('coupon code "%s" (id #%s), ', $code_data ['name'], $code_data ['id']);
					}
					$errors = array ($helper->__('The following discounts associated with this rule expired: %s. Please check Shopping Cart Price Rules or assign a different discount for the STW rule.', $codes));
					$rule->setErrors(serialize($errors));
				}
				$desactivated_collection->save();
				$path = 'addinmage_spreadtheword/behaviour/rules';
				$config = Mage::getModel('core/config_data');
				$used_rules = $config->getCollection()
					->addFilter('path', $path)
					->addFieldToFilter('value', array ('in' => $ids));
				if ($used_rules) {
					foreach ($used_rules as $rule_in_config) {
						$rule_in_config->delete();
					}
				}
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()
					->addException($e, $e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			} catch (Exception $e) {
				$this->_getSession()
					->addException($e, $this->__('Unable to deaсtivate Spread The Word Rules associated with expired coupon codes.'));
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
	}

	public function getShoppingCartPriceRulesUrl()
	{
		return $this->getUrl('adminhtml/promo_quote');
	}

	public function getSpreadTheWordRulesUrl()
	{
		return $this->getUrl('spreadtheword/adminhtml_rules');
	}

	protected function _toHtml()
	{
		if (Mage::getSingleton('admin/session')->isAllowed('promo/quote') && Mage::getSingleton('admin/session')->isAllowed('addinmage/rules')) {return parent::_toHtml();}
		return '';
	}
}
