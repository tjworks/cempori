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

class AddInMage_SpreadTheWord_Model_Configuration_AllowedMailServices extends Mage_Core_Model_Config_Data
{

	public static function getServices()
	{
		$services = Mage::helper('spreadtheword')->getServices();
		$services_array = array ();
		
		if (! isset($services ['mail'])) return $services_array;
		
		foreach ($services ['mail'] as $service) {
			if (class_exists('AddInMage_SpreadTheWord_Helper_Services_' . ucfirst($service))) {
				$service_data = Mage::helper('spreadtheword/services_' . $service)->getInfo();
				$service_exist = array ('value' => $service, 'label' => $service_data ['service_name']);
				if ($service_data ['type'] == 'mail') $services_array [] = $service_exist;
			}
		}
		return $services_array;
	}

	public function toOptionArray()
	{
		return $this->getServices();
	}
}