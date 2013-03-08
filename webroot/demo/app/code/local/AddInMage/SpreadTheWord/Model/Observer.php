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

class AddInMage_SpreadTheWord_Model_Observer
{

	public function addMassAction($observer)
	{
		if (Mage::getStoreConfig('addinmage_spreadtheword/behaviour/delete') == 'delete_man') 

		if ($observer->getEvent()
			->getBlock() instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction) {
			$secure = Mage::app()->getStore()
				->isCurrentlySecure() ? 'true' : 'false';
			if ($observer->getEvent()
				->getBlock()
				->getRequest()
				->getControllerName() == 'customer') {
				$periods = Mage::getModel('spreadtheword/configuration_deleteperiod')->toOptionArray();
				array_unshift($periods, array ('label' => '', 'value' => ''));
				$observer->getEvent()
					->getBlock()
					->addItem('delete_non_active', array ('label' => Mage::helper('spreadtheword')->__('Delete non-activated accounts'), 'url' => Mage::helper('adminhtml')->getUrl('spreadtheword/adminhtml_customer/deleteNonActive', Mage::app()->getStore()
					->isCurrentlySecure() ? array ('_secure' => 1) : array ()), 'additional' => array ('visibility' => array ('name' => 'period', 'type' => 'select', 'class' => 'required-entry', 'label' => Mage::helper('spreadtheword')->__('Period'), 'values' => $periods))));
			}
		}
	
	}

	public function trackLinks($observer)
	{
		$track_data = $observer->getControllerAction()
			->getRequest()
			->getParam('t');
		if ($track_data && ! $this->isBot()) {
			$model = Mage::getModel('spreadtheword/friends');
			$action = substr($track_data, 0, 1);
			$user_track_link = substr($track_data, 1);
			if (is_numeric($action) && in_array($action, array (0, 1, 2)) && in_array(strlen($user_track_link), array (6, 7))) {
				$link_traked = $model->trackLink($user_track_link);
				if ($link_traked) {
					switch ($action) {
					case 0:
					$observer->getControllerAction()
						->getResponse()
						->setRedirect(Mage::getBaseUrl());
					break;
					case 1:
					$observer->getControllerAction()
						->getResponse()
						->setRedirect(Mage::getUrl('spreadtheword'));
					break;
					case 2:
					$observer->getControllerAction()
						->getResponse()
						->setRedirect(Mage::getUrl('checkout/cart/'));
					break;
					}
				}
			}
		}
	}

	public function clearExpNotice($observer)
	{
		$session = Mage::getSingleton('adminhtml/session');
		if (Mage::getStoreConfig('addinmage_spreadtheword/advanced/expire_notifications')) $session->unsExpNotice();
	
	}

	public function check_coupon_on_edit($observer)
	{
		$coupon = $observer->getControllerAction()
			->getRequest()
			->getParam('id');
		
		if ($coupon && $this->isInUse($coupon)) {
			$model = Mage::getModel('salesrule/rule')->load($coupon)
				->getData();
			$protected_fields = array ();
			if (isset($model ['rule_id'])) $protected_fields ['rule_id'] = $model ['rule_id'];
			if (isset($model ['discount_amount'])) $protected_fields ['discount_amount'] = (int) $model ['discount_amount'];
			$session = Mage::getSingleton('adminhtml/session');
			$session->setProtectedFields($protected_fields);
			$session->unsExpNotice();
		}
	}

	public function check_coupon_on_delete($observer)
	{
		$affected_rules = Mage::getSingleton('adminhtml/session')->getAffectedRules();
		if ($affected_rules) {
			$this->_processAffected($affected_rules);
			Mage::getSingleton('adminhtml/session')->unsExpNotice();
		}
	}

	public function check_coupon_on_save($observer)
	{
		$session = Mage::getSingleton('adminhtml/session');
		$affected_rules = $session->getAffectedRules();
		$protected_fields = $session->getProtectedFields();
		$post = $observer->getRequest()
			->getPost();
		$process_affected = false;
		if ($affected_rules && $protected_fields && $post) {
			$session->unsExpNotice();
			if ($post ['is_active'] !== '1') $process_affected = true;
			if ((int) $post ['discount_amount'] !== $protected_fields ['discount_amount'] || empty($post ['discount_amount'])) $process_affected = true;
			if ($post ['coupon_type'] !== '2') $process_affected = true;
			if (empty($post ['coupon_code'])) $process_affected = true;
			if ($process_affected) $this->_processAffected($affected_rules);
		
		}
	}

	private function _processAffected($affected_rules)
	{
		$affected_collection = Mage::getModel('spreadtheword/rules')->getCollection()
			->addFieldToFilter('id', array ('in' => $affected_rules));
		if (count($affected_collection) !== 0) {
			
			$helper = Mage::helper('spreadtheword');
			
			foreach ($affected_collection as $affected_rule) {
				$affected_rule->setConflicts(true);
				$errors = array ($helper->__('Some important coupon code fields linked to this rule have been changed or the linked coupon code has been deleted. Please check all discounts linked to this rule.'));
				$affected_rule->setErrors(serialize($errors));
			}
			
			$affected_collection->save();
			
			$path = 'addinmage_spreadtheword/behaviour/rules';
			$config = Mage::getModel('core/config_data');
			$used_rules = $config->getCollection()
				->addFilter('path', $path)
				->addFieldToFilter('value', array ('in' => $affected_rules));
			
			if ($used_rules) {
				foreach ($used_rules as $rule) {
					$rule->delete();
				}
			}
			$session = Mage::getSingleton('adminhtml/session');
			$session->unsAffectedRules();
			$session->unsgetProtectedFields();
			$session->addNotice($helper->__('All affected Spread The Word rules were disabled. Please check the extension rules and their configuration.'));
		}
	}

	private function isInUse($coupon)
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
					
					if (isset($config ['manual_discount']) && ! empty($config ['manual_discount']) && $config ['manual_discount'] == $coupon) $sales_rules [] = $rule_id;
					if (isset($config ['rules'])) {
						foreach ($config ['rules'] as $participant) {
							if (isset($participant ['fixed_discount']) && $participant ['fixed_discount'] == $coupon) $sales_rules [] = $rule_id;
							if (isset($participant ['dynamic_discount']) && $participant ['dynamic_discount'] == $coupon) $sales_rules [] = $rule_id;
							if (isset($participant ['dynamic_levels_discount'])) {
								foreach ($participant ['dynamic_levels_discount'] as $level) {
									if (isset($level ['rule']) && $level ['rule'] == $coupon) $sales_rules [] = $rule_id;
								}
							}
						}
					}
					if (isset($config ['options'])) {
						foreach ($config ['options'] as $options) {
							foreach ($options as $option) {
								if (isset($option ['discount_rule']) && $option ['discount_rule'] == $coupon) $sales_rules [] = $rule_id;
								if (isset($option ['values'])) {
									foreach ($option ['values'] as $discount_level) {
										if (isset($discount_level ['discount_rule']) && $discount_level ['discount_rule'] == $coupon) $sales_rules [] = $rule_id;
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
			$sales_rules = array_unique($sales_rules);
			$keys = array_keys($sales_rules);
			$last = $keys [sizeof($sales_rules) - 1];
			$affected_rules = '';
			foreach ($sales_rules as $index => $rule_id) {
				$name = $model->load($rule_id)
					->getRuleName();
				if ($index == $last) $affected_rules .= $helper->__('rule "' . $name . '" (rule id#' . $rule_id . ').');
				else $affected_rules .= $helper->__('rule "' . $name . '" (rule id#' . $rule_id . '), ');
			}
			$notice_message = $helper->__('This discount is linked to the following Spread The Word extension rules: %s', $affected_rules);
			$notice_message .= '<br/>' . $helper->__('Deleting or changing Coupon, Status or Discount Amount fields will disable all active rules associated with this discount and switch Spread The Word to the default mode ("Invitation Only").');
			$session = Mage::getSingleton('adminhtml/session');
			$session->addNotice($notice_message);
			$session->setAffectedRules($sales_rules);
			return true;
		}
		return false;
	}

	private function isBot()
	{
		$userAgent = Mage::helper('core/http')->getHttpUserAgent();
		$ignoreAgents = Mage::getConfig()->getNode('global/ignore_user_agents');
		if ($ignoreAgents) {
			$ignoreAgents = $ignoreAgents->asArray();
			if (in_array($userAgent, $ignoreAgents)) {return true;}
			return false;
		}
		return false;
	}

	public function checkForStwOrder($observer)
	{
		$session = Mage::getSingleton('customer/session');
		$stw_sales_id = $session->getStwSalesId();
		$order = $observer->getOrder();
		if ($stw_sales_id) {
			$order->setStwSalesId($stw_sales_id);
			Mage::helper('spreadtheword')->refreshStwCustomerId($stw_sales_id);
		} else {
			$stw_customer_id = Mage::helper('spreadtheword')->getStwCustomerId();
			if ($stw_customer_id) $order->setStwSalesId($stw_customer_id);
		}
	}

	public function checkForRealDiscount($observer)
	{
		$post = $observer->getControllerAction()
			->getRequest();
		$code = $post->getParam('coupon_code');
		$session = Mage::getSingleton('customer/session');
		
		$user_type = substr($code, 0, 1);
		$discount_code = substr($code, 1);
		
		if (is_numeric($user_type) && in_array($user_type, array (0, 1)) && in_array(strlen($discount_code), array (6))) {
			switch ($user_type) {
			case 0:
			$type = 'sender';
			break;
			case 1:
			$type = 'friend';
			break;
			}
			
			if ($session->getStwSalesId()) {
				$data = Mage::getModel('spreadtheword/sales')->getResource()
					->getRealDiscountData($discount_code);
				Mage::helper('spreadtheword')->refreshStwCustomerId($session->getStwSalesId());
			} else {
				$data = Mage::getModel('spreadtheword/sales')->getResource()
					->getRealDiscountData($discount_code, $type);
				$stw_customer_id = Mage::helper('spreadtheword')->getStwCustomerId();
				if ($stw_customer_id) $session->setStwSalesId($stw_customer_id);
			}
			if ($data) {
				$post->setParam('coupon_code', $data ['real_discount']);
				if (! $session->getStwSalesId()) {
					$session->setStwSalesId($data ['id']);
					Mage::helper('spreadtheword')->refreshStwCustomerId($data ['id']);
				}
			}
		}
	}
}