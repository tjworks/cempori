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


class AddInMage_SpreadTheWord_Block_Elements_Friends_Top extends Mage_Core_Block_Template
{
	protected $_mode;
	protected $_sender_configuration;
	protected $_friends_configuration;
	protected $_sender_custom;
	protected $_friends_custom;
	protected $_sender_js;
	protected $_friends_js;
	protected $_mem_config;

	protected function _prepareLayout()
	{
		$config = Mage::getSingleton('core/session')->getStwConfig();
		if (! is_object($config)) $this->_mode = 'noaction';
		else {
			$this->_mode = $config->getRuleMode();
			$configuration = unserialize($config->getConfiguration());
			$this->_sender_configuration = (isset($configuration ['rules'] ['sender'])) ? $configuration ['rules'] ['sender'] : false;
			$this->_friends_configuration = (isset($configuration ['rules'] ['friends'])) ? $configuration ['rules'] ['friends'] : false;
			$this->_sender_custom = (isset($configuration ['options'] ['sender'])) ? $this->helper('spreadtheword')
				->extractRightCustomRule($configuration ['options'] ['sender']) : false;
			$this->_friends_custom = (isset($configuration ['options'] ['friends'])) ? $this->helper('spreadtheword')
				->extractRightCustomRule($configuration ['options'] ['friends']) : false;
		}
		Mage::register('stw_current_mode', $this->_mode);
		return parent::_prepareLayout();
	}

	public function getCurrentService()
	{
		$service_data = Mage::getSingleton('customer/session')->getCurrentService();
		return $service_data ['service'];
	}

	public function getMode()
	{
		return $this->_mode;
	}

	public function canShowDiscountContainer()
	{
		if ($this->_mode == 'noaction') return false;
		return true;
	}

	public function getDiscountText()
	{
		if ($this->_mode == 'action_d_frd') return $this->__('Discount for your friends:');
		else return $this->__('Your discount:');
	}

	public function getDiscountData($for)
	{
		
		$data = array ();
		$obj_custom = ($for == 'sender') ? $this->_sender_custom : $this->_friends_custom;
		$obj_default = ($for == 'sender') ? $this->_sender_configuration : $this->_friends_configuration;
		$fixed_desc = ($for == 'sender') ? $this->__('Get the discount above by inviting your friends!') : $this->__('Give your friends the discount above by inviting them!');
		$dynamic_all_desc = ($for == 'sender') ? $this->__('The more friends you invite - the bigger discount you get!') : $this->__('The more friends you invite - the bigger discount they get!');
		if ($obj_custom) {
			switch ($obj_custom ['discount_type']) {
			case 'fixed':
			$dsc = Mage::getModel('salesrule/rule')->load($obj_custom ['discount_rule']);
			$data ['discount'] = Mage::helper('spreadtheword')->formatDiscount($dsc->getDiscountAmount(), $dsc->getSimpleAction());
			$data ['description'] = $fixed_desc;
			if ($for == 'sender') $this->_mem_config ['sender'] = array ('mode' => 'fixed', 'data' => $obj_custom ['discount_rule']);
			else $this->_mem_config ['friends'] = array ('mode' => 'fixed', 'data' => $obj_custom ['discount_rule']);
			break;
			case 'dynamic':
			$dsc = Mage::getModel('salesrule/rule')->load($obj_custom ['discount_rule']);
			$discount = (int) $dsc->getDiscountAmount();
			$amount_type = Mage::helper('spreadtheword')->getDiscountAmountType($dsc->getSimpleAction());
			$data ['description'] = $dynamic_all_desc;
			$data ['description'] .= $this->__(' The maximum discount is %s.', Mage::helper('spreadtheword')->formatDiscount($dsc->getDiscountAmount(), $dsc->getSimpleAction()));
			$data ['discount'] = Mage::helper('spreadtheword')->formatDiscount(0, $dsc->getSimpleAction());
			if ($for == 'sender') {
				$this->_sender_js = array ('mode' => 'dynamic', 'data' => $discount, 'amount_type' => $amount_type);
				$this->_mem_config ['sender'] = array ('mode' => 'dynamic', 'data' => $obj_custom ['discount_rule']);
			} else {
				$this->_friends_js = array ('mode' => 'dynamic', 'data' => $discount, 'amount_type' => $amount_type);
				$this->_mem_config ['friends'] = array ('mode' => 'dynamic', 'data' => $obj_custom ['discount_rule']);
			}
			break;
			case 'dynamic_levels':
			$last_el = end($obj_custom ['discount_rule']);
			$dsc = Mage::getModel('salesrule/rule')->load($last_el ['discount_rule']);
			$discount = (int) $dsc->getDiscountAmount();
			$amount_type = Mage::helper('spreadtheword')->getDiscountAmountType($dsc->getSimpleAction());
			$data ['discount'] = Mage::helper('spreadtheword')->formatDiscount(0, $dsc->getSimpleAction());
			$data ['description'] = $dynamic_all_desc;
			$data ['description'] .= $this->__(' The maximum discount is %s.', Mage::helper('spreadtheword')->formatDiscount($dsc->getDiscountAmount(), $dsc->getSimpleAction()));
			if ($for == 'sender') {
				$this->_sender_js = array ('mode' => 'dynamic_levels', 'data' => $obj_custom ['discount_rule'], 'amount_type' => $amount_type);
				$this->_mem_config ['sender'] = $this->_sender_js;
			} else {
				$this->_friends_js = array ('mode' => 'dynamic_levels', 'data' => $obj_custom ['discount_rule'], 'amount_type' => $amount_type);
				$this->_mem_config ['friends'] = $this->_friends_js;
			}
			break;
			}
			return $data;
		} else {
			switch ($obj_default ['discount_type']) {
			case 'fixed':
			$dsc = Mage::getModel('salesrule/rule')->load($obj_default ['fixed_discount']);
			$data ['discount'] = Mage::helper('spreadtheword')->formatDiscount($dsc->getDiscountAmount(), $dsc->getSimpleAction());
			$data ['description'] = $fixed_desc;
			if ($for == 'sender') $this->_mem_config ['sender'] = array ('mode' => 'fixed', 'data' => $obj_default ['fixed_discount']);
			else $this->_mem_config ['friends'] = array ('mode' => 'fixed', 'data' => $obj_default ['fixed_discount']);
			break;
			case 'dynamic':
			$dsc = Mage::getModel('salesrule/rule')->load($obj_default ['dynamic_discount']);
			$discount = (int) $dsc->getDiscountAmount();
			$amount_type = Mage::helper('spreadtheword')->getDiscountAmountType($dsc->getSimpleAction());
			$data ['discount'] = Mage::helper('spreadtheword')->formatDiscount(0, $dsc->getSimpleAction());
			$data ['description'] = $dynamic_all_desc;
			$data ['description'] .= $this->__(' The maximum discount is %s.', Mage::helper('spreadtheword')->formatDiscount($dsc->getDiscountAmount(), $dsc->getSimpleAction()));
			if ($for == 'sender') {
				$this->_sender_js = array ('mode' => 'dynamic', 'data' => $discount, 'amount_type' => $amount_type);
				$this->_mem_config ['sender'] = array ('mode' => 'dynamic', 'data' => $obj_default ['dynamic_discount']);
			} else {
				$this->_friends_js = array ('mode' => 'dynamic', 'data' => $discount, 'amount_type' => $amount_type);
				$this->_mem_config ['friends'] = array ('mode' => 'dynamic', 'data' => $obj_default ['dynamic_discount']);
			}
			break;
			case 'dynamic_levels':
			$last_el = end($obj_default ['dynamic_levels_discount']);
			$dsc = Mage::getModel('salesrule/rule')->load($last_el ['rule']);
			$discount = (int) $dsc->getDiscountAmount();
			$amount_type = Mage::helper('spreadtheword')->getDiscountAmountType($dsc->getSimpleAction());
			$data ['discount'] = Mage::helper('spreadtheword')->formatDiscount(0, $dsc->getSimpleAction());
			$data ['description'] = $dynamic_all_desc;
			$data ['description'] .= $this->__(' The maximum discount is %s.', Mage::helper('spreadtheword')->formatDiscount($dsc->getDiscountAmount(), $dsc->getSimpleAction()));
			if ($for == 'sender') {
				$this->_sender_js = array ('mode' => 'dynamic_levels', 'data' => $obj_default ['dynamic_levels_discount'], 'default' => true, 'amount_type' => $amount_type);
				$this->_mem_config ['sender'] = $this->_sender_js;
			} else {
				$this->_friends_js = array ('mode' => 'dynamic_levels', 'data' => $obj_default ['dynamic_levels_discount'], 'default' => true, 'amount_type' => $amount_type);
				$this->_mem_config ['friends'] = $this->_friends_js;
			}
			break;
			}
			return $data;
		}
	}

	public function getDiscountJs()
	{
		$data = '';
		$hash = '$H';
		
		$config = Mage::app()->getLocale()
			->getJsPriceFormat();
		$coreHelper = Mage::helper('core');
		$format = $coreHelper->jsonEncode($config);
		
		if ($this->_sender_js) {
			if ($this->_sender_js ['mode'] == 'dynamic') {
				$data .= "var discount_sender_container = $('adm-stw-discount-sender');";
				if ($this->_sender_js ['amount_type'] == 'percent') {
					$data .= "var senderPercentTemplate = new Template('#{percent}%');";
				} else {
					$data .= "var senderPriceFormat = {$format};";
				}
				$data .= "var stw_sender_discount = Math.floor((selected_friends/total_friends)*" . $this->_sender_js ['data'] . ");";
				$data .= "if (parseInt(discount_sender_container.innerHTML) != stw_sender_discount) {";
				if ($this->_sender_js ['amount_type'] == 'percent') {
					$data .= "var s_data= {percent:stw_sender_discount};";
					$data .= "discount_sender_container.update(senderPercentTemplate.evaluate(s_data));";
				} else {
					$data .= "discount_sender_container.update(formatCurrency(stw_sender_discount,senderPriceFormat));";
				}
				$data .= "}";
			} elseif ($this->_sender_js ['mode'] == 'dynamic_levels') {
				$pairs = array ();
				$model = Mage::getModel('salesrule/rule');
				if (isset($this->_sender_js ['default'])) {
					foreach ($this->_sender_js ['data'] as $pair) {
						$pairs [] = $pair ['percent'] . ':' . (int) $model->load($pair ['rule'])
							->getDiscountAmount();
					}
				} else {
					foreach ($this->_sender_js ['data'] as $pair) {
						$pairs [] = $pair ['percent'] . ':' . (int) $model->load($pair ['discount_rule'])
							->getDiscountAmount();
					}
				}
				
				$data .= "var discount_sender_container = $('adm-stw-discount-sender');";
				if ($this->_sender_js ['amount_type'] == 'percent') {
					$data .= "var senderPercentTemplate = new Template('#{percent}%');";
				} else {
					$data .= "var senderPriceFormat = {$format};";
				}
				$data .= "stw_sender_selected_perc = Math.floor((selected_friends/total_friends)*100);";
				$data .= "var stw_sender_discount_levels = " . $hash . "({0:0," . implode(',', $pairs) . "});";
				$data .= "stw_sender_discount_levels.each(function(s_pair) {";
				$data .= "if(stw_sender_selected_perc >= s_pair.key && parseInt(discount_sender_container.innerHTML) != stw_sender_selected_perc){";
				if ($this->_sender_js ['amount_type'] == 'percent') {
					$data .= "var s_data= {percent:s_pair.value};";
					$data .= "discount_sender_container.update(senderPercentTemplate.evaluate(s_data));";
				} else {
					$data .= "discount_sender_container.update(formatCurrency(s_pair.value,senderPriceFormat));";
				}
				$data .= "}";
				$data .= "});";
			}
		}
		if ($this->_friends_js) {
			if ($this->_friends_js ['mode'] == 'dynamic') {
				$data .= "var discount_friends_container = $('adm-stw-discount-friends');";
				if ($this->_friends_js ['amount_type'] == 'percent') {
					$data .= "var friendsPercentTemplate = new Template('#{percent}%');";
				} else {
					$data .= "var friendsPriceFormat = {$format};";
				}
				$data .= "var stw_friends_discount = Math.floor((selected_friends/total_friends)*" . $this->_friends_js ['data'] . ");";
				$data .= "if (parseInt(discount_friends_container.innerHTML) != stw_friends_discount) {";
				if ($this->_friends_js ['amount_type'] == 'percent') {
					$data .= "var f_data= {percent:stw_friends_discount};";
					$data .= "discount_friends_container.update(friendsPercentTemplate.evaluate(f_data));";
				} else {
					$data .= "discount_friends_container.update(formatCurrency(stw_friends_discount,friendsPriceFormat));";
				}
				$data .= "}";
			} elseif ($this->_friends_js ['mode'] == 'dynamic_levels') {
				$pairs = array ();
				$model = Mage::getModel('salesrule/rule');
				if (isset($this->_friends_js ['default'])) {
					foreach ($this->_friends_js ['data'] as $pair) {
						$pairs [] = $pair ['percent'] . ':' . (int) $model->load($pair ['rule'])
							->getDiscountAmount();
					}
				} else {
					foreach ($this->_friends_js ['data'] as $pair) {
						$pairs [] = $pair ['percent'] . ':' . (int) $model->load($pair ['discount_rule'])
							->getDiscountAmount();
					}
				}
				$data .= "var discount_friends_container = $('adm-stw-discount-friends');";
				if ($this->_friends_js ['amount_type'] == 'percent') {
					$data .= "var friendsPercentTemplate = new Template('#{percent}%');";
				} else {
					$data .= "var friendsPriceFormat = {$format};";
				}
				$data .= "stw_friends_selected_perc = Math.floor((selected_friends/total_friends)*100);";
				$data .= "var stw_friends_discount_levels = " . $hash . "({0:0," . implode(',', $pairs) . "});";
				$data .= "stw_friends_discount_levels.each(function(f_pair) {";
				$data .= "if(stw_friends_selected_perc >= f_pair.key && parseInt(discount_friends_container.innerHTML) != stw_friends_selected_perc){";
				if ($this->_friends_js ['amount_type'] == 'percent') {
					$data .= "var f_data= {percent:f_pair.value};";
					$data .= "discount_friends_container.update(friendsPercentTemplate.evaluate(f_data));";
				} else {
					$data .= "discount_friends_container.update(formatCurrency(f_pair.value,friendsPriceFormat));";
				}
				$data .= "}";
				$data .= "});";
			}
		}
		return $data;
	}

	public function _memConfig()
	{
		$this->_mem_config ['mode'] = $this->_mode;
		Mage::getSingleton('customer/session')->setCurrentDiscountData($this->_mem_config);
	}
}