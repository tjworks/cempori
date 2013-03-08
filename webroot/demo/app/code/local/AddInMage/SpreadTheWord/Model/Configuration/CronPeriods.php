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

class AddInMage_SpreadTheWord_Model_Configuration_CronPeriods extends Mage_Core_Model_Config_Data
{

	public function toOptionArray()
	{
		return array ('*/5 * * * *' => Mage::helper('adminhtml')->__('5 minutes'), '*/10 * * * *' => Mage::helper('adminhtml')->__('10 minutes'), '*/20 * * * *' => Mage::helper('adminhtml')->__('20 minutes'), '*/30 * * * *' => Mage::helper('adminhtml')->__('30 minutes'), '*/40 * * * *' => Mage::helper('adminhtml')->__('40 minutes'), '*/50 * * * *' => Mage::helper('adminhtml')->__('50 minutes'), '00 * * * *' => Mage::helper('adminhtml')->__('1 Hour'), '00 */2 * * *' => Mage::helper('adminhtml')->__('2 Hours'), '00 */3 * * *' => Mage::helper('adminhtml')->__('3 Hours'), '00 */4 * * *' => Mage::helper('adminhtml')->__('4 Hours'), '00 */5 * * *' => Mage::helper('adminhtml')->__('5 Hours'), '00 */6 * * *' => Mage::helper('adminhtml')->__('6 Hours'), '00 */12 * * *' => Mage::helper('adminhtml')->__('12 Hours'), '00 */24 * * *' => Mage::helper('adminhtml')->__('24 Hours'));
	}
}