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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Edit_Tab_Custom_Rules_Sender extends AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	protected $_rule = 'sender';

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('addinmage/spreadtheword/edit/options.phtml');
	}

	protected function _prepareLayout()
	{
		$this->setChild('add_button', $this->getLayout()
			->createBlock('adminhtml/widget_button')
			->setData(array (
					'label' => $this->__('Add Special Service Rule Condition'), 
					'class' => 'add', 
					'id' => 'add_new_defined_option_' . $this->_rule, 
					'title' => $this->__('Add a new condition to the rule for a particular service.'))));
		
		$this->setChild('options_box', $this->getLayout()
			->createBlock('spreadtheword/adminhtml_rules_rules_edit_tab_options_option')
			->setData('_rule', $this->_rule));
		return parent::_prepareLayout();
	}

	public function getOptionsBoxHtml()
	{
		return $this->getChildHtml('options_box');
	}

	public function getAddButtonHtml()
	{
		return $this->getChildHtml('add_button');
	}

	public function getTabLabel()
	{
		return $this->__('Service Targeting For Senders');
	}

	public function getTabTitle()
	{
		return $this->__('Service Targeting For Senders');
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
