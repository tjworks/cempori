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

class AddInMage_SpreadTheWord_Model_Configuration_DeletePeriod extends Mage_Core_Model_Config_Data
{

	public function toOptionArray()
	{
		return array (array ('value' => '1 days', 'label' => Mage::helper('spreadtheword')->__('1 day')), array ('value' => '2 days', 'label' => Mage::helper('spreadtheword')->__('2 days')), array ('value' => '3 days', 'label' => Mage::helper('spreadtheword')->__('3 days')), array ('value' => '4 days', 'label' => Mage::helper('spreadtheword')->__('4 days')), array ('value' => '5 days', 'label' => Mage::helper('spreadtheword')->__('5 days')), array ('value' => '6 days', 'label' => Mage::helper('spreadtheword')->__('6 days')), array ('value' => '1 weeks', 'label' => Mage::helper('spreadtheword')->__('1 week')), array ('value' => '2 weeks', 'label' => Mage::helper('spreadtheword')->__('2 weeks')), array ('value' => '3 weeks', 'label' => Mage::helper('spreadtheword')->__('3 weeks')), array ('value' => '1 months', 'label' => Mage::helper('spreadtheword')->__('1 month')), array ('value' => '2 months', 'label' => Mage::helper('spreadtheword')->__('2 months')), array ('value' => '3 months', 'label' => Mage::helper('spreadtheword')->__('3 months')), array ('value' => '4 months', 'label' => Mage::helper('spreadtheword')->__('4 months')), array ('value' => '5 months', 'label' => Mage::helper('spreadtheword')->__('5 months')), array ('value' => '6 months', 'label' => Mage::helper('spreadtheword')->__('6 months')));
	}

}