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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Edit_Tab_To_Sender extends AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	protected $_pages;

	public function __construct()
	{
		
		parent::__construct();
		$this->setShowGlobalIcon(true);
	}

	protected function _prepareForm()
	{
		
		$form = new Varien_Data_Form();
		$helper = Mage::helper('spreadtheword');
		$this->setForm($form);
		
		$data = $this->getApplication()
			->getFormData();
		
		$fieldset = $form->addFieldset('discount_to_sender', array ('legend' => $this->__('Discount for Senders')));
		
		$discount_rules = $this->getDiscountRules();
		$discount_options = $this->getDiscountOptions();
		
		$dependence = $this->getLayout()
			->createBlock('adminhtml/widget_form_element_dependence');
		
		$discount_type = $fieldset->addField('conf[rules][sender][discount_type]', 'select', array ('name' => 'conf[rules][sender][discount_type]', 'label' => $this->__('Discount Type'), 'title' => $this->__('Discount Type'), 'values' => $discount_options, 'note' => $this->__('Select the discount type for the senders.'), 'required' => true));
		
		$fixed_discount = $fieldset->addField('conf[rules][sender][fixed_discount]', 'select', array ('name' => 'conf[rules][sender][fixed_discount]', 'label' => $this->__('Fixed Discount'), 'title' => $this->__('Fixed Discount'), 'values' => $discount_rules, 'note' => $this->__('Please select from existing discount rules.'), 'required' => true));
		
		$dynamic_discount = $fieldset->addField('conf[rules][sender][dynamic_discount]', 'select', array ('name' => 'conf[rules][sender][dynamic_discount]', 'label' => $this->__('Maximum Discount'), 'title' => $this->__('Maximum Discount'), 'values' => $discount_rules, 'note' => $this->__('Specify the maximum discount amount.'), 'required' => true));
		
		$amount_type = $fieldset->addField('conf[rules][sender][amount_type]', 'select', array ('name' => 'conf[rules][sender][amount_type]', 'label' => $this->__('Amount Type'), 'title' => $this->__('Amount Type'), 'style' => 'margin-bottom:15px', 'values' => array (array ('value' => '0', 'label' => $this->__('Please Select Amount Type')), array ('value' => 'amount_percent', 'label' => $this->__('Percent')), array ('value' => 'amount_fixed', 'label' => $this->__('Fixed'))), 'required' => true));
		
		$fieldset->addField('conf[rules][sender][dynamic_levels_discount]', 'text', array ('name' => 'conf[rules][sender][dynamic_levels_discount]', 'label' => $this->__('Dynamic Discount Levels')));
		
		$form->getElement('conf[rules][sender][dynamic_levels_discount]')
			->setRenderer($this->getLayout()
			->createBlock('spreadtheword/adminhtml_rules_rules_elements_dynamicLevels')
			->setData('_to', 'sender'));
		
		$this->setChild('form_after', $dependence->addFieldMap($discount_type->getHtmlId(), $discount_type->getName())
			->addFieldMap($fixed_discount->getHtmlId(), $fixed_discount->getName())
			->addFieldMap($amount_type->getHtmlId(), $amount_type->getName())
			->addFieldMap($dynamic_discount->getHtmlId(), $dynamic_discount->getName())
			->addFieldDependence($fixed_discount->getName(), $discount_type->getName(), 'fixed')
			->addFieldDependence($dynamic_discount->getName(), $discount_type->getName(), 'dynamic')
			->addFieldDependence($amount_type->getName(), $discount_type->getName(), 'dynamic_levels'));
		
		$form->setValues($data);
		$this->setForm($form);
		return parent::_prepareForm();
	}

	public function getTabLabel()
	{
		
		return $this->__('Discount for Senders');
	}

	public function getTabTitle()
	{
		return $this->__('Discount for Senders');
	}

	public function canShowTab()
	{
		return (bool) ! Mage::getSingleton('adminhtml/session')->getNewRule() && $this->getApplication()
			->getRuleMode() == 'action_d_sen' || $this->getApplication()
			->getRuleMode() == 'action_d_all';
	}

	public function isHidden()
	{
		return false;
	}
}

