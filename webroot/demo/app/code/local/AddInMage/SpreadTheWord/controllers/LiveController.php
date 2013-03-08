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


class AddInMage_SpreadTheWord_LiveController extends Mage_Core_Controller_Front_Action
{

	private function _checkAccessDenied()
	{
		$message = Mage::helper('spreadtheword')->__('You denied access to your friend list. You need to allow access in order to invite your friends. Please try again.');
		if ($this->getRequest()
			->getParam('ResponseCode') && $this->getRequest()
			->getParam('ResponseCode') == 'RequestRejected') {
			Mage::getSingleton('core/session')->addNotice($message);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			die();
		}
	}

	public function indexAction()
	{
		$this->loadLayout(false);
		$this->renderLayout();
		$consentToken = $this->getRequest()
			->getParam('ConsentToken');
		
		$this->_checkAccessDenied();
		
		if (! Mage::helper('spreadtheword')->checkProvider('outlook', 'live')) {
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
			return;
		}
		if (! $consentToken) $this->_redirectUrl(Mage::helper('spreadtheword/services_outlook')->getLiveUrl());
		
		if ($consentToken) {
			$session = Mage::getSingleton('customer/session');
			Mage::app()->setCurrentStore(unserialize($session->getStwStore()));
			$session->unsStwStore();
			$contacts_data = Mage::helper('spreadtheword/services_outlook')->getContactData($consentToken);
		}
	
	}
}