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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Edit_Tab_General extends AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

	protected function _prepareForm()
	{
		$model = $this->getApplication();
		
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('adm_stw_');
		$fieldset = $form->addFieldset('general_fieldset', array ('legend' => $this->__('Rule Information')));
		
		if ($model->getId()) {
			$fieldset->addField('id', 'hidden', array ('name' => 'id'));
		}
		
		$fieldset->addField('rule_name', 'text', array ('name' => 'rule_name', 'label' => $this->__('Rule Name'), 'title' => $this->__('Rule Name'), 'maxlength' => '255', 'required' => true));
		
		$fieldset->addField('show_rule_mode', 'select', array ('name' => 'show_rule_mode', 'label' => $this->__('Rule Mode'), 'title' => $this->__('Rule Mode'), 'values' => array ($model->getRuleMode() => $model->getModeTitle()), 'disabled' => true));
		
		$fieldset->addField('rule_mode', 'hidden', array ('name' => 'rule_mode', 'value' => $model->getRuleMode()));
		
		$form->setValues($model->getFormData());
		$this->setForm($form);
		return parent::_prepareForm();
	}

	public function getTabLabel()
	{
		return $this->__('General');
	}

	public function getTabTitle()
	{
		return $this->__('General');
	}

	public function canShowTab()
	{
		return (bool) ! Mage::getSingleton('adminhtml/session')->getNewRule();
	}

	public function isHidden()
	{
		return false;
	}
}
