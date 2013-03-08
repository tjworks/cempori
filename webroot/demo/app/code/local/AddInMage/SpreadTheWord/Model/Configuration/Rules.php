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

class AddInMage_SpreadTheWord_Model_Configuration_Rules extends Mage_Core_Model_Config_Data
{

	protected function getRuleLabels()
	{
		$rules_list = array ();
		$rules_list [] = array ('value' => 'inv', 'label' => Mage::helper('spreadtheword')->__('Default Mode ("Invitation Only")'));
		$rules = Mage::getModel('spreadtheword/rules')->getCollection()
			->addFilter('errors', '')
			->addFilter('conflicts', '0')
			->getData();
		foreach ($rules as $rule) {
			$rules_list [] = array ('value' => $rule ['id'], 'label' => $rule ['rule_name']);
		}
		return $rules_list;
	}

	public function toOptionArray()
	{
		return $this->getRuleLabels();
	}

	public function getCommentText()
	{
		$rules = Mage::getModel('spreadtheword/rules')->getCollection()
			->addFilter('errors', '')
			->addFilter('conflicts', '0')
			->count();
		if ($rules) return Mage::helper('spreadtheword')->__('Select the invitation rule for the current store.');
		
		$link = Mage::helper('adminhtml')->getUrl('spreadtheword/adminhtml_rules');
		$comment = Mage::helper('spreadtheword')->__('No valid rules found. The extension will be running in the default mode ("Invitation Only"). Please <a target="_blank" href="' . $link . '">use this link</a> to create a rule. All rules must be enabled and not have a conflict marker.');
		
		return $comment;
	}
}