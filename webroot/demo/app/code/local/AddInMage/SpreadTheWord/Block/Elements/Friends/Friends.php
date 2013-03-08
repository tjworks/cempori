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


class AddInMage_SpreadTheWord_Block_Elements_Friends_Friends extends Mage_Core_Block_Template
{

	const XML_PATH_DIRECT_SIMPLE 			= 'addinmage_spreadtheword/message/direct_message_simple';
	const XML_PATH_DIRECT_DISCOUNT 			= 'addinmage_spreadtheword/message/direct_message_discount';
	const XML_PATH_DIRECT_TWITTER_SIMPLE 	= 'addinmage_spreadtheword/message/direct_twitter_simple';
	const XML_PATH_DIRECT_TWITTER_DISCOUNT 	= 'addinmage_spreadtheword/message/direct_twitter_discount';

	public function getAlphaIndex()
	{
		$friends = Mage::getSingleton('customer/session')->getFriends();
		
		$alpha_index = array ();
		
		$first_letters = array ();
		foreach ($friends as $friend) {
			$first_letters [] = mb_substr($friend ['friend_name'], 0, 1, 'UTF-8');
		}
		
		$script = Mage::helper('spreadtheword')->getAlphaMode(array_unique($first_letters));
		$alpha_validator = new Zend_Validate_Regex('/^[\p{' . $script . '}]$/u');
		
		$email_validator = new Zend_Validate_EmailAddress();
		foreach ($friends as $friend) {
			$first_letter = mb_substr($friend ['friend_name'], 0, 1, 'UTF-8');
			
			$friend_data = array ();
			foreach ($friend as $key => $value) {
				$friend_data [$key] = $value;
			}
			
			if ($email_validator->isValid($friend_data ['friend_name']) || ! $alpha_validator->isValid($first_letter)) $alpha_index ['#'] [] = $friend_data;
			else $alpha_index [$first_letter] [] = $friend_data;
		}
		
		if (isset($alpha_index ['#'])) {
			$end = $alpha_index ['#'];
			unset($alpha_index ['#']);
			if (! empty($end)) $alpha_index ['#'] = $end;
		}
		return $alpha_index;
	}

	public function selectAll()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/contact/select_all', Mage::app()->getStore());
	}

	public function markCustomers()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/contact/mark_already_customers', Mage::app()->getStore());
	}

	public function showPicture()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/contact/show_picture', Mage::app()->getStore());
	}

	public function markInvited()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/contact/mark_already_invited', Mage::app()->getStore());
	}

	public function getCurrentService()
	{
		return Mage::getSingleton('customer/session')->getCurrentService();
	}

	public function isDiscountMode()
	{
		$mode = Mage::registry('stw_current_mode');
		return ($mode == 'noaction' || $mode == 'action_d_sen') ? false : true;
	}

	public function getDirectTemplate()
	{
		
		$service = $this->getCurrentService();
		if ($service ['code'] == 'twitter') {
			$templateCode = Mage::getStoreConfig(($this->isDiscountMode()) ? self::XML_PATH_DIRECT_TWITTER_DISCOUNT : self::XML_PATH_DIRECT_TWITTER_SIMPLE, Mage::app()->getStore());
		} else
			$templateCode = Mage::getStoreConfig(($this->isDiscountMode()) ? self::XML_PATH_DIRECT_DISCOUNT : self::XML_PATH_DIRECT_SIMPLE, Mage::app()->getStore());
		
		$template = Mage::getModel('core/email_template')->load($templateCode);
		$template->loadDefault($templateCode, Mage::app()->getLocale()
			->getDefaultLocale());
		$template_data = $template->getData();
		return $template_data ['template_text'];
	}
}