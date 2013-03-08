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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Edit_Tab_Options_Option extends Mage_Adminhtml_Block_Widget
{
	protected $_values;
	protected $_itemCount = 1;

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('addinmage/spreadtheword/edit/options/option.phtml');
	
	}

	public function getServicesSelectHtml()
	{
		$select = $this->getLayout()
			->createBlock('adminhtml/html_select')
			->setData(array ('id' => $this->getFieldId() . '_{{id}}_service', 'class' => 'select required-option-select'))
			->setName($this->getFieldName() . '[{{id}}][service]')
			->setValue('{{service}}')
			->setOptions($this->getServices());
		
		return $select->getHtml();
	}

	protected function getServices()
	{
		$groups = array (array ('value' => '', 'label' => $this->__('-- Please Select --')));
		
		$mail_services = array (
				'title' => $this->__('E-mail Services'), 
				'data' => Mage::getModel('spreadtheword/configuration_allowedMailServices')->getServices());
		
		$social_services = array (
				'title' => $this->__('Social Networks '), 
				'data' => Mage::getModel('spreadtheword/configuration_allowedSocialServices')->getServices());
		
		$file_services = array (
				'title' => $this->__('Address Books'), 
				'data' => Mage::getModel('spreadtheword/configuration_allowedFileTypes')->getTypeList());
		
		$services = array ($mail_services, $social_services, $file_services);
		
		foreach ($services as $group) {
			$groups [] = array ('label' => $group ['title'], 'value' => $group ['data']);
		}
		return $groups;
	}

	public function getDiscountTypesSelectHtml()
	{
		$select = $this->getLayout()
			->createBlock('adminhtml/html_select')
			->setData(array ('id' => $this->getFieldId() . '_{{id}}_discount_type', 'class' => 'select select-discount-type required-option-select'))
			->setName($this->getFieldName() . '[{{id}}][discount_type]')
			->setValue('{{discount_type}}')
			->setOptions($this->getDiscountTypes());
		
		return $select->getHtml();
	}

	public function getAmountTypesSelectHtml()
	{
		$select = $this->getLayout()
			->createBlock('adminhtml/html_select')
			->setData(array ('id' => $this->getFieldId() . '_{{option_id}}_amount_type', 'class' => 'select select-discount-type required-option-select'))
			->setName($this->getFieldName() . '[{{option_id}}][amount_type]')
			->setValue('{{amount_type}}')
			->setOptions(array (array (
					'value' => '0', 
					'label' => $this->__('-- Please Select --')), 
					array (
							'value' => 'amount_percent', 
							'label' => $this->__('Percent')), 
					array (
							'value' => 'amount_fixed', 
							'label' => $this->__('Fixed'))));
		
		return $select->getHtml();
	}

	protected function getDiscountTypes()
	{
		$groups = array (array (
				'value' => '', 
				'label' => $this->__('-- Please Select --')));
		
		$types = Mage::helper('spreadtheword')->getDiscountOptions();
		foreach ($types as $type => $value) {
			$groups [] = array (
					'label' => $value, 
					'value' => $type);
		}
		return $groups;
	}

	public function getItemCount()
	{
		return $this->_itemCount;
	}

	public function setItemCount($itemCount)
	{
		$this->_itemCount = max($this->_itemCount, $itemCount);
		return $this;
	}

	public function getFieldName()
	{
		return 'conf[options][' . $this->_rule . ']';
	}

	public function getFieldId()
	{
		return 'conf_option_' . $this->_rule;
	}

	protected function _prepareLayout()
	{
		$this->setChild('delete_button', $this->getLayout()
			->createBlock('adminhtml/widget_button')
			->setData(array (
					'label' => $this->__('Delete Condition'), 
					'class' => 'delete delete-product-option ')));
		return parent::_prepareLayout();
	}

	public function getAddButtonId()
	{
		return 'add_new_defined_option_' . $this->_rule;
	}

	public function getDeleteButtonHtml()
	{
		return $this->getChildHtml('delete_button');
	}

	public function getTemplatesHtml()
	{
		$blocks = array ('fixed', 'dynamic', 'dinamicLevels');
		
		foreach ($blocks as $block => $value) {
			$this->setChild($value . '_option_type', $this->getLayout()
				->createBlock('spreadtheword/adminhtml_rules_rules_edit_tab_options_type_' . $value)
				->setData('_rule', $this->_rule));
		}
		
		$templates = $this->getChildHtml('fixed_option_type') . "\n" . $this->getChildHtml('dynamic_option_type') . "\n" . $this->getChildHtml('dinamiclevels_option_type');
		return $templates;
	}

	public function getValues()
	{
		$data = $this->getApplication()
			->getData('conf');
		$values = array ();
		if (! isset($data ['options'] [$this->_rule]) || is_null($data ['options'] [$this->_rule])) return $values;
		
		$optionsArr = array_reverse($data ['options'] [$this->_rule], true);
		
		if (! $this->_values) {
			
			foreach ($optionsArr as $option) {
				
				$this->setItemCount($option ['id']);
				
				$value = array ();
				
				$value ['id'] = $option ['id'];
				$value ['item_count'] = $this->getItemCount();
				$value ['option_id'] = $option ['id'];
				$value ['service'] = $this->htmlEscape($option ['service']);
				$value ['discount_type'] = $option ['discount_type'];
				
				if ($option ['discount_type'] == 'dynamic_levels') {
					
					if (isset($option ['from_date']) && ! empty($option ['from_date'])) $value ['from_date'] = date('d/m/y', $option ['from_date']);
					if (isset($option ['to_date']) && ! empty($option ['to_date'])) $value ['to_date'] = date('d/m/y', $option ['to_date']);
					$value ['amount_type'] = $option ['amount_type'];
					$value ['min_friends'] = $option ['min_friends'];
					
					$i = 0;
					$itemCount = 0;
					foreach ($option ['values'] as $_value) {
						
						$value ['optionValues'] [$i] = array ('item_count' => max($itemCount, $i), 'option_id' => $value ['id'], 'option_type_id' => $i, 'discount_rule' => $_value ['discount_rule'], 'percent' => $_value ['percent']);
						$i ++;
					}
				} else {
					$value ['discount_rule'] = $option ['discount_rule'];
					
					if (isset($option ['from_date']) && ! empty($option ['from_date'])) $value ['from_date'] = date('d/m/y', $option ['from_date']);
					if (isset($option ['to_date']) && ! empty($option ['to_date'])) $value ['to_date'] = date('d/m/y', $option ['to_date']);
					
					$value ['min_friends'] = $option ['min_friends'];
				}
				$values [] = new Varien_Object($value);
			}
			$this->_values = $values;
		}
		return $this->_values;
	}

	public function getApplication()
	{
		return Mage::registry('current_rule');
	}
}
