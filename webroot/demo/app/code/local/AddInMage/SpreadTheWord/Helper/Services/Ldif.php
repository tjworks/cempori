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

class AddInMage_SpreadTheWord_Helper_Services_Ldif extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		
		$service_data ['service_name'] = Mage::helper('spreadtheword')->__('LDIF Address Book');
		$service_data ['type'] = 'file';
		$service_data ['specific_view'] = true;
		$service_data ['controller'] = 'file';
		$service_data ['extension'] = array ('ldif');
		$service_data ['mime'] = array ('application/octet-stream', 'text/plain');
		
		return $service_data;
	
	}

	public function getContact($file_data)
	{
		$data = Zend_Ldap_Ldif_Encoder::decode($file_data);
		$validator = new Zend_Validate_EmailAddress();
		$string_to_lower = new Zend_Filter_StringToLower();
		$string_to_lower->setEncoding('UTF-8');
		$helper = Mage::helper('spreadtheword');
		$contact_list = array ();
		
		foreach ($data as $entry) {
			$c = array ();
			if (isset($entry ['mail'])) $c ['id'] = $string_to_lower->filter($entry ['mail'] [0]);
			
			$c ['name'] = '';
			if (isset($entry ['givenname'] [0])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($entry ['givenname'] [0]));
			if (isset($entry ['givenname'] [0]) && isset($entry ['sn'] [0])) $c ['name'] .= ' ';
			if (isset($entry ['sn'] [0])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($entry ['sn'] [0]));
			
			if (empty($c ['name'])) $c ['name'] = $c ['id'];
			if (! empty($c ['name']) && ! empty($c ['id']) && $validator->isValid($c ['id'])) 

			$contact_list [] = $c;
		}
		Mage::getSingleton('customer/session')->setCurrentService(array ('service' => $this->__('LDIF Address Book'), 'type' => 'file', 'code' => 'ldif'));
		$contact_list = array_values(array_filter($contact_list));
		return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'file', 'ldif');
	}
}