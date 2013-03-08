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

class AddInMage_SpreadTheWord_Model_Configuration_AllowedFileTypes extends Mage_Core_Model_Config_Data
{

	public static function getTypeList()
	{
		$types = Mage::helper('spreadtheword')->getServices();
		$file_types_array = array ();
		
		if (! isset($types ['file'])) return $file_types_array;
		
		foreach ($types ['file'] as $type) {
			if (class_exists('AddInMage_SpreadTheWord_Helper_Services_' . ucfirst($type))) {
				$file_type_data = Mage::helper('spreadtheword/services_' . $type)->getInfo();
				$type_exist = array ('value' => $type, 'label' => $file_type_data ['service_name']);
				if ($file_type_data ['type'] == 'file') $file_types_array [] = $type_exist;
			}
		}
		return $file_types_array;
	}

	public function toOptionArray()
	{
		return $this->getTypeList();
	}
}