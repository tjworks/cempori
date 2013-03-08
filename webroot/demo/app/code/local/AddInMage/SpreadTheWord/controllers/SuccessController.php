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

class AddInMage_SpreadTheWord_SuccessController extends Mage_Core_Controller_Front_Action
{

	public function preDispatch()
	{
		parent::preDispatch();
		if (! Mage::getSingleton('customer/session')->isLoggedIn()) {
			$access_level = Mage::getStoreConfig('addinmage_spreadtheword/general/access', Mage::app()->getStore());
			if ($access_level == 'customer') {
				Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());
				$this->_redirect('customer/account/login');
			}
		}
	}

	public function indexAction()
	{
		$session = Mage::getSingleton('customer/session');
		if (! $session->getStwSuccess()) {
			return $this->_redirect('*/friends');}
		$this->loadLayout();
		$this->renderLayout();
		$session->unsFriends();
	}

	public function downloadAction()
	{
		$session = Mage::getSingleton('customer/session');
		$stw_data = $session->getStwData();
		if (! $session->getStwSuccess() && ! isset($stw_data ['discount_data'])) {return $this->_redirect('*');}
		
		$this->loadLayout(false);
		$this->renderLayout();
		
		$file = $this->__('discount_information.txt');
		$discount_information = fopen($file, 'w');
		
		$output = $this->__('%s Discount Information:', Mage::app()->getStore()
			->getUrl()) . "\n\n";
		$output .= $this->__('Discount Amount: %s', $stw_data ['discount_data'] ['amount']) . "\n";
		$output .= $this->__('Discount Code: %s', $stw_data ['discount_data'] ['code']) . "\n";
		
		if (isset($stw_data ['discount_data'] ['coupon_expire'])) $output .= $this->__('Expiration Date: %s', $stw_data ['discount_data'] ['coupon_expire']) . "\n";
		
		if (isset($stw_data ['discount_data'] ['discount_have_conditions'])) $output .= $this->__('Other Discount Terms:') . ' ' . $this->__('please contact the store owner for more information.') . "\n";
		
		$output .= "\n";
		$output .= $this->__('Thank You for inviting your friends!');
		
		fwrite($discount_information, $output);
		fclose($discount_information);
		
		$session->unsFriends();
		$session->unsStwData();
		$session->unsStwSuccess();
		
		$this->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Pragma', 'public', true)
			->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
			->setHeader('Content-type', 'text/plain; charset=UTF-8')
			->setHeader('Content-Length', filesize($file))
			->setHeader('Content-Disposition', 'attachment; filename=' . $file);
		$this->getResponse()->clearBody();
		$this->getResponse()->sendHeaders();
		readfile($file);
		exit(0);
	}
}