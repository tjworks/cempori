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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Filter_Form extends Mage_Adminhtml_Block_Widget_Form
{
	
	protected $_groupingOptions = array ();
	
	protected $_visualizationBasePontOptions = array ();
	
	protected $_fieldVisibility = array ();
	
	protected $_groupingVisibility = false;
	
	protected $_dynamicsVisibility = false;
	
	protected $_ratingConditionsVisibility = false;
	
	protected $_orderStatusesVisibility = false;
	
	protected $_ruleTypesVisibility = false;
	
	protected $_sumUpDataVisibility = false;
	
	protected $_visualizationBasePoint = false;
	
	protected $_orderPlacedByVisibility = false;
	
	protected $_fieldOptions = array ();

	
	public function setFieldVisibility($fieldId, $visibility)
	{
		$this->_fieldVisibility [$fieldId] = (bool) $visibility;
	}

	public function setGroupingVisibility($visibility)
	{
		$this->_groupingVisibility = (bool) $visibility;
	}

	public function setDynamicsVisibility($visibility)
	{
		$this->_dynamicsVisibility = (bool) $visibility;
	}

	public function setRatingConditions($visibility)
	{
		$this->_ratingConditionsVisibility = (bool) $visibility;
	}

	public function setOrderStatusesVisibility($visibility)
	{
		$this->_orderStatusesVisibility = (bool) $visibility;
	}

	public function setRuleTypesVisibility($visibility)
	{
		$this->_ruleTypesVisibility = (bool) $visibility;
	}

	public function setSumUpDataPerPeriodVisibility($visibility)
	{
		$this->_sumUpDataVisibility = (bool) $visibility;
	}

	public function setOrderSideVisibility($visibility)
	{
		$this->_orderPlacedByVisibility = (bool) $visibility;
	}

	public function setVisualizationPointVisibility($visibility)
	{
		$this->_visualizationBasePoint = (bool) $visibility;
	}

	public function getFieldVisibility($fieldId, $defaultVisibility = true)
	{
		if (! array_key_exists($fieldId, $this->_fieldVisibility)) {return $defaultVisibility;}
		return $this->_fieldVisibility [$fieldId];
	}

	public function setFieldOption($fieldId, $option, $value = null)
	{
		if (is_array($option)) {
			$options = $option;
		} else {
			$options = array ($option => $value);
		}
		if (! array_key_exists($fieldId, $this->_fieldOptions)) {
			$this->_fieldOptions [$fieldId] = array ();
		}
		foreach ($options as $k => $v) {
			$this->_fieldOptions [$fieldId] [$k] = $v;
		}
	}

	public function addGroupingOption($key, $value)
	{
		$this->_groupingOptions [$key] = $this->__($value);
		return $this;
	}

	public function addVisualizationBasePontOption($key, $value)
	{
		$this->_visualizationBasePontOptions [$key] = $this->__($value);
		return $this;
	}

	protected function _prepareForm()
	{
		$actionUrl = $this->getUrl('*/*/');
		$form = new Varien_Data_Form(array ('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get'));
		$htmlIdPrefix = 'adm_stw_report_';
		$form->setHtmlIdPrefix($htmlIdPrefix);
		$fieldset = $form->addFieldset('base_fieldset', array ('legend' => Mage::helper('spreadtheword')->__('Filter')));
		$fieldset->addClass('no-display');
		$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
		
		$fieldset->addField('store_ids', 'hidden', array ('name' => 'store_ids'));
		
		if ($this->_groupingVisibility) {
			$grouping = $fieldset->addField('group_by', 'select', array (
					'name' => 'group_by', 
					'options' => $this->_groupingOptions, 
					'label' => Mage::helper('spreadtheword')->__('Group By'), 
					'title' => Mage::helper('spreadtheword')->__('Grouping Condition')));
		}
		
		if ($this->_ratingConditionsVisibility) {
			$rating_conditions = $fieldset->addField('rating_conditions', 'select', array (
					'name' => 'rating_conditions', 
					'onchange' => 'stwSwitchFilters($F(\'' . $htmlIdPrefix . 'rating_conditions\'))', 
					'label' => Mage::helper('spreadtheword')->__('Rating Condition'), 
					'title' => Mage::helper('spreadtheword')->__('Rating Condition'), 
					'options' => array (
							'invited' => Mage::helper('spreadtheword')->__('Invited Friends'), 
							'returned' => Mage::helper('spreadtheword')->__('Responded Invitees'), 
							'profit' => Mage::helper('spreadtheword')->__('Profit'), 
							'orders' => Mage::helper('spreadtheword')->__('Number of Orders'))));
		}
		
		$period = $fieldset->addField('period_type', 'select', array (
				'name' => 'period_type', 
				'options' => array (
						'day' => Mage::helper('reports')->__('Day'), 
						'month' => Mage::helper('reports')->__('Month'), 
						'year' => Mage::helper('reports')->__('Year')), 
				'label' => Mage::helper('reports')->__('Period'), 
				'title' => Mage::helper('reports')->__('Period')));
		
		$fieldset->addField('from', 'date', array (
				'name' => 'from', 
				'format' => $dateFormatIso, 
				'image' => $this->getSkinUrl('images/grid-cal.gif'), 
				'label' => Mage::helper('reports')->__('From'), 
				'title' => Mage::helper('reports')->__('From'), 
				'required' => true));
		
		$fieldset->addField('to', 'date', array (
				'name' => 'to', 
				'format' => $dateFormatIso, 
				'image' => $this->getSkinUrl('images/grid-cal.gif'), 
				'label' => Mage::helper('reports')->__('To'), 
				'title' => Mage::helper('reports')->__('To'), 
				'required' => true));
		
		$dependence = $this->getLayout()
			->createBlock('adminhtml/widget_form_element_dependence');
		
		if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset && $this->_orderStatusesVisibility) {
			
			$statuses = Mage::getModel('sales/order_config')->getStatuses();
			$values = array ();
			foreach ($statuses as $code => $label) {
				if (false === strpos($code, 'pending')) {
					$values [] = array ('label' => Mage::helper('reports')->__($label), 'value' => $code);
				}
			}
			
			$fieldset->addField('show_order_statuses', 'select', array (
					'name' => 'show_order_statuses', 
					'label' => Mage::helper('reports')->__('Order Status'), 
					'options' => array (
							'0' => Mage::helper('reports')->__('Any'), 
							'1' => Mage::helper('reports')->__('Specified')), 
					'note' => Mage::helper('reports')->__('Applies to Any of the Specified Order Statuses')), 'to');
			
			$fieldset->addField('order_statuses', 'multiselect', array (
					'name' => 'order_statuses', 
					'values' => $values, 'display' => 'none'), 
					'show_order_statuses');
			
			if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
				$this->setChild('form_after', $dependence->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
					->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
					->addFieldDependence('order_statuses', 'show_order_statuses', '1'));
			}
		}
		
		if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset && $this->getFieldVisibility('services')) {
			$fieldset->addField('show_services', 'select', array (
					'name' => 'show_services', 
					'label' => Mage::helper('reports')->__('Services'), 
					'options' => array (
							'0' => Mage::helper('reports')->__('All Services'), 
							'1' => Mage::helper('reports')->__('Specified'))));
			
			$fieldset->addField('services', 'multiselect', array (
					'name' => 'services', 
					'values' => Mage::helper('spreadtheword')->getServiceArray(true), 
					'display' => 'none'), 'show_services');
			
			if ($this->getFieldVisibility('show_services') && $this->getFieldVisibility('services')) {
				$this->setChild('form_after', $dependence->addFieldMap("{$htmlIdPrefix}show_services", 'show_services')
					->addFieldMap("{$htmlIdPrefix}services", 'services')
					->addFieldDependence('services', 'show_services', '1'));
			}
		}
		
		if ($this->_ruleTypesVisibility) {
			$rule_mode = array ('0' => Mage::helper('spreadtheword')->__('All Rule Types'));
			$rule_mode += Mage::helper('spreadtheword')->getRuleModes();
			$fieldset->addField('rule_type', 'select', array (
					'name' => 'rule_type', 
					'options' => $rule_mode, 
					'label' => Mage::helper('spreadtheword')->__('Invitation Rule Types'), 
					'title' => Mage::helper('spreadtheword')->__('Invitation Rule Types')));
		}
		
		if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset && $this->getFieldVisibility('visitor_type')) {
			
			$fieldset->addField('show_cutomer_types', 'select', array (
					'name' => 'show_cutomer_types', 
					'label' => Mage::helper('reports')->__('Customer Types'), 
					'options' => array (
							'0' => Mage::helper('reports')->__('All Types'), 
							'1' => Mage::helper('reports')->__('Specified'))));
			
			$fieldset->addField('visitor_type', 'multiselect', array (
					'name' => 'visitor_type', 
					'values' => array (array (
							'value' => 'customer', 
							'label' => Mage::helper('spreadtheword')->__('Customers')), 
							array (
									'value' => 'visitor', 
									'label' => Mage::helper('spreadtheword')->__('Visitors'))), 
					'display' => 'none'), 'show_cutomer_types');
			
			if ($this->getFieldVisibility('show_cutomer_types') && $this->getFieldVisibility('visitor_type')) {
				$this->setChild('form_after', $dependence->addFieldMap("{$htmlIdPrefix}show_cutomer_types", 'show_cutomer_types')
					->addFieldMap("{$htmlIdPrefix}visitor_type", 'visitor_type')
					->addFieldDependence('visitor_type', 'show_cutomer_types', '1'));
			}
		}
		
		$fieldset->addField('show_empty_rows', 'select', array (
				'name' => 'show_empty_rows', 
				'options' => array (
						'1' => Mage::helper('reports')->__('Yes'), 
						'0' => Mage::helper('reports')->__('No')), 
				'label' => Mage::helper('reports')->__('Empty Rows'), 
				'title' => Mage::helper('reports')->__('Empty Rows')));
		
		$fieldset->addField('detailed_information', 'select', array (
				'name' => 'detailed_information', 
				'label' => Mage::helper('spreadtheword')->__('Show Detailed Information'), 
				'title' => Mage::helper('spreadtheword')->__('Show Detailed Information'), 
				'options' => array (
						'1' => Mage::helper('reports')->__('Yes'), 
						'0' => Mage::helper('reports')->__('No'))));
		
		if ($this->_dynamicsVisibility) {
			$dynamics = $fieldset->addField('show_dynamics', 'select', array (
					'name' => 'show_dynamics', 
					'label' => Mage::helper('spreadtheword')->__('Show Trend'), 
					'title' => Mage::helper('spreadtheword')->__('Show Trend'), 
					'options' => array (
							'1' => Mage::helper('spreadtheword')->__('Yes'), 
							'0' => Mage::helper('spreadtheword')->__('No'))));
		}
		
		if ($this->_sumUpDataVisibility) {
			$fieldset->addField('sum_up_data_per_period', 'select', array (
					'name' => 'sum_up_data_per_period', 
					'label' => Mage::helper('spreadtheword')->__('Sum Up Data Per Period'), 
					'title' => Mage::helper('spreadtheword')->__('Sum Up Data Per Period'), 
					'options' => array (
							'1' => Mage::helper('reports')->__('Yes'), 
							'0' => Mage::helper('reports')->__('No'))));
		}
		
		if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset && $this->_orderPlacedByVisibility) {
			
			$fieldset->addField('show_buyer_types', 'select', array (
					'name' => 'show_buyer_types', 
					'label' => Mage::helper('reports')->__('Buyer'), 
					'options' => array (
							'0' => Mage::helper('reports')->__('Any'), 
							'1' => Mage::helper('reports')->__('Specified'))));
			
			$fieldset->addField('order_placed_by', 'multiselect', array (
					'name' => 'order_placed_by', 
					'values' => array (array (
							'value' => 'sender', 
							'label' => Mage::helper('spreadtheword')->__('Invitation sender')), 
							array (
									'value' => 'friend', 
									'label' => Mage::helper('spreadtheword')->__('Invitee'))), 
					'display' => 'none'), 'show_buyer_types');
			
			if ($this->getFieldVisibility('show_buyer_types') && $this->getFieldVisibility('order_placed_by')) {
				$this->setChild('form_after', $dependence->addFieldMap("{$htmlIdPrefix}show_buyer_types", 'show_buyer_types')
					->addFieldMap("{$htmlIdPrefix}order_placed_by", 'order_placed_by')
					->addFieldDependence('order_placed_by', 'show_buyer_types', '1'));
			}
		
		}
		
		if ($this->_visualizationBasePoint) {
			$fieldset->addField('visualization_base_point', 'select', array (
					'name' => 'visualization_base_point', 
					'label' => Mage::helper('spreadtheword')->__('Visualization Condition'), 
					'title' => Mage::helper('spreadtheword')->__('Visualization Condition'), 
					'options' => $this->_visualizationBasePontOptions));
		}
		
		if ($this->_groupingVisibility) {
			$this->setChild('form_after', $dependence->addFieldMap($grouping->getHtmlId(), $grouping->getName())
				->addFieldMap($period->getHtmlId(), $period->getName())
				->addFieldMap($dynamics->getHtmlId(), $dynamics->getName())
				->addFieldDependence($period->getName(), $grouping->getName(), 'period')
				->addFieldDependence($dynamics->getName(), $grouping->getName(), 'period'));
		}
		
		$form->setUseContainer(true);
		$this->setForm($form);
		
		return parent::_prepareForm();
	}

	protected function _initFormValues()
	{
		$this->getForm()
			->addValues($this->getFilterData()
			->getData());
		return parent::_initFormValues();
	}

	protected function _beforeToHtml()
	{
		$result = parent::_beforeToHtml();
		
		$fieldset = $this->getForm()
			->getElement('base_fieldset');
		
		if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {
		
			foreach ($fieldset->getElements() as $field) {
				if (! $this->getFieldVisibility($field->getId())) {
					$fieldset->removeField($field->getId());
				}
			}
			
			foreach ($this->_fieldOptions as $fieldId => $fieldOptions) {
				$field = $fieldset->getElements()
					->searchById($fieldId);
				
				if ($field) {
					foreach ($fieldOptions as $k => $v) {
						$field->setDataUsingMethod($k, $v);
					}
				}
			}
		}
		
		return $result;
	}
}