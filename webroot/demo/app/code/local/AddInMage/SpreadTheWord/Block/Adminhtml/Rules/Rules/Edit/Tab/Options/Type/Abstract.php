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

class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Edit_Tab_Options_Type_Abstract extends Mage_Adminhtml_Block_Widget
{

	public function getDynamicDiscountRulesSelectHtml($field)
	{
		$select = $this->getLayout()
			->createBlock('adminhtml/html_select');
		$rules = Mage::helper('spreadtheword')->getDiscountRules($to_options = true, false, true);
		
		switch ($field) {
			case 'dynamic_levels':
			$select->setData(array ('id' => $this->getFieldId() . '_{{id}}_select_{{select_id}}_discount_rule', 'class' => 'select required-option-select select-rule'))
				->setName($this->getFieldName() . '[{{id}}][values][{{select_id}}][discount_rule]')
				->setValue('{{discount_rule}}');
			break;
			case 'fixed':
			case 'dynamic':
			$select->setData(array ('id' => $this->getFieldId() . '_{{option_id}}_discount_rule', 'class' => 'select required-option-select'))
				->setName($this->getFieldName() . '[{{option_id}}][discount_rule]')
				->setValue('{{discount_rule}}');
			break;
		}
		
		$select->setOptions($rules);
		
		return $select->getHtml();
	}

	public function getFieldName()
	{
		return 'conf[options][' . $this->_rule . ']';
	}

	public function getFieldId()
	{
		return 'conf_option_' . $this->_rule;
	}
}
