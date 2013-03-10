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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules extends Mage_Adminhtml_Block_Widget_Container
{

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('addinmage/spreadtheword/rules.phtml');
	}

	protected function _prepareLayout()
	{
		$this->_addButton('add_new', array ('label' => Mage::helper('spreadtheword')->__('Add Rule'), 'onclick' => "setLocation('{$this->getUrl('*/*/new')}')", 'class' => 'add'));
		
		$this->setChild('grid', $this->getLayout()
			->createBlock('spreadtheword/adminhtml_rules_rules_grid', 'rules.grid'));
		return parent::_prepareLayout();
	}

	public function getGridHtml()
	{
		return $this->getChildHtml('grid');
	}

	public function isSingleStoreMode()
	{
		if (! Mage::app()->isSingleStoreMode()) return false;
		return true;
	}
}