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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

	public function __construct()
	{
		$this->_objectId = 'id';
		$this->_controller = 'adminhtml_rules_rules';
		$this->_blockGroup = 'spreadtheword';
		parent::__construct();
		
		$app = $this->getApplication();
		
		if ((bool) ! Mage::getSingleton('adminhtml/session')->getNewRule()) {
			$this->_updateButton('save', 'label', $this->__('Save'));
			$this->_updateButton('save', 'onclick', 'if (editForm.submit()) {disableElements(\'save\')}');
			
			$this->_addButton('save_and_continue', array ('label' => $this->__('Save and Continue Edit'), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save'), - 5);
			
			if (isset($app) && $this->_getInUse($app->getId())) {
				$this->_addButton('in_use', array ('label' => $this->__('This rule is in use'), 'class' => 'rule-in-use disabled'), - 5);
			}
			
			$this->_formScripts [] = 'function saveAndContinueEdit() {' . 'if (editForm.submit($(\'edit_form\').action + \'back/edit/\')) {disableElements(\'save\')};}';
		
		} else {
			$this->removeButton('save');
			$this->removeButton('delete');
		}
		
		if (isset($app) && $this->_getInUse($app->getId(), $check_for_notice = true)) {
			$this->removeButton('delete');
		
		}
		$this->removeButton('reset');
	}

	public function getApplication()
	{
		return Mage::registry('current_rule');
	}

	public function _getInUse($id, $check_for_notice = false)
	{
		$path = 'addinmage_spreadtheword/behaviour/rules';
		$config = Mage::getModel('core/config_data');
		$configData = $config->getCollection()
			->addFilter('path', $path)
			->addFilter('value', $id)
			->getFirstItem()
			->getConfigId();
		if ($configData) {
			if ($check_for_notice) {
				$scope = $config->load($configData);
				switch ($scope->getScope()) {
				case 'default':
				$area = $this->__('default configuration');
				break;
				case 'stores':
				$store_name = Mage::app()->getStore($scope->getScopeId())
					->getName();
				$website_name = Mage::app()->getStore($scope->getScopeId())
					->getWebsite()
					->getName();
				$area = '"' . $website_name . ' / ' . $store_name . '"';
				break;
				case 'websites':
				$area = '"' . Mage::app()->getWebsite($scope->getScopeId())
					->getName() . '"';
				break;
				}
				if (in_array($scope->getScope(), array ('websites', 'stores'))) Mage::getSingleton('adminhtml/session')->addNotice($this->__('This rule is currently used in %s. Be careful when editing it. If a conflict is detected, all stores where this rule is used will inherit the default configuration rule.', $area));
				else Mage::getSingleton('adminhtml/session')->addNotice($this->__('This rule is currently used in %s. Be careful when editing it. If a conflict is detected, the STW extension will switch to the default mode "Invitation Only".', $area));
			
			}
			return true;
		}
		return false;
	}

	protected function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function getHeaderText()
	{
		$app = $this->getApplication();
		if ($app && $app->getId()) {
			return $this->__('Edit Rule "%s"', $this->htmlEscape($app->getRuleName()));
		} else {
			return $this->__('New Rule');
		}
	}
}
