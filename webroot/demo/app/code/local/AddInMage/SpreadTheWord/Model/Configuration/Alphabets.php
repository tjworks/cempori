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

class AddInMage_SpreadTheWord_Model_Configuration_Alphabets extends Mage_Core_Model_Config_Data
{

	public function getAllowedScripts()
	{
		$scripts = Zend_Locale::getTranslationList('Script', 'en');
		if (version_compare(Mage::getVersion(), '1.4.2.0') >= 0) {
			foreach ($scripts as $script => $value) {
				try {
					new Zend_Validate_Regex('/^[\p{' . $value . '}]$/u');
				} catch (Exception $e) {
					unset($scripts [$script]);
				}
			}
			unset($scripts ['Zyyy']);
			return $scripts;
		} else {
			$allowed = array ('Arab', 'Armn', 'Beng', 'Bopo', 'Brai', 'Buhd', 'Cher', 'Cyrl', 'Deva', 'Ethi', 'Geor', 'Grek', 'Gujr', 'Guru', 'Hani', 'Hang', 'Hano', 'Hebr', 'Hira', 'Qaai', 'Knda', 'Kana', 'Khmr', 'Laoo', 'Latn', 'Limb', 'Mlym', 'Mong', 'Mymr', 'Ogam', 'Orya', 'Runr', 'Sinh', 'Syrc', 'Tglg', 'Tagb', 'Taml', 'Telu', 'Thaa', 'Thai', 'Tibt', 'Yiii');
			$allowed_scripts = array_fill_keys($allowed, 0);
			
			foreach ($scripts as $key => $value) {
				if (isset($allowed_scripts [$key])) $allowed_scripts [$key] = $value;
			}
			
			return $allowed_scripts;
		
		}
	
	}

	public function getUnicodeScripts()
	{
		$allowed_scripts = $this->getAllowedScripts();
		foreach ($allowed_scripts as $name => $value) {
			$allowed_scripts [$name] = Zend_Locale::getTranslation($name, 'script', Mage::app()->getLocale()
				->getLocale());
		}
		return $allowed_scripts;
	}

	public function toOptionArray()
	{
		return $this->getUnicodeScripts();
	}
}