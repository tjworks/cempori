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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Elements_DynamicLevels extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
	
	protected $_element;

	public function __construct()
	{
		$this->setTemplate('addinmage/spreadtheword/edit/dynamic_levels.phtml');
	}

	public function getApplication()
	{
		return Mage::registry('current_rule');
	}

	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);
		return $this->toHtml();
	}

	public function setElement(Varien_Data_Form_Element_Abstract $element)
	{
		$this->_element = $element;
		return $this;
	}

	public function getElement()
	{
		return $this->_element;
	}

	public function getValues()
	{
		$values = array ();
		$data = $this->getApplication()
			->getData('conf/rules/' . $this->_to . '/dynamic_levels_discount');
		
		if (is_array($data)) {
			$values = $data;
		}
		
		return $values;
	}

	public function getAddButtonHtml()
	{
		$button = $this->getLayout()
			->createBlock('adminhtml/widget_button')
			->setData(array ('label' => $this->__('Add Level'), 'onclick' => 'return ' . $this->_to . '_level_Control.addItem();', 'class' => 'add'));
		$button->setName('add_level_item_button');
		$this->setChild('add_button', $button);
		return $this->getChildHtml('add_button');
	}
}
