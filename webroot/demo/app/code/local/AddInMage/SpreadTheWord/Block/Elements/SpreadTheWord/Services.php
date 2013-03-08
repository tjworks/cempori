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

class AddInMage_SpreadTheWord_Block_Elements_SpreadTheWord_Services extends Mage_Core_Block_Template
{
	protected $_services;

	protected function _prepareLayout()
	{
		$services = $this->getServices();
		
		foreach ($services as $key => $service) {
			$service_data = Mage::helper('spreadtheword/services_' . $service)->getInfo();
			$this->_services [$service] = $service_data;
			
			if (isset($service_data ['ext']) && $service_data ['ext']) 
				$this->getLayout()
				->getBlock('head')
				->addCss('css/addinmage/spreadtheword/ext/' . $service . '/' . $service . '.css');
		}
		$manual_invitation_enabled = Mage::getStoreConfig('addinmage_spreadtheword/services/manual', Mage::app()->getStore());
		if ($manual_invitation_enabled) {
			$block_manual = $this->getLayout()
				->createBlock('spreadtheword/elements_specificServices_manual', 'manual-invitation');
			$this->setChild('manual-invitation', $block_manual);
		}
		return parent::_prepareLayout();
	}

	public function getServices()
	{
		
		$services = array ();
		$service_list = array ('social', 'mail', 'file');
		
		foreach ($service_list as $service_type) {
			$service_enabled = Mage::getStoreConfig('addinmage_spreadtheword/services/' . $service_type, Mage::app()->getStore());
			$service_providers = Mage::getStoreConfig('addinmage_spreadtheword/services/' . $service_type . '_services', Mage::app()->getStore());
			if ($service_enabled && ! empty($service_providers)) {
				$providers = explode(',', $service_providers);
				foreach ($providers as $provider => $name) {
					array_push($services, $name);
				}
			}
		}
		if ($this->manualInvitationAllowed()) $services [] = 'manual';
		return $services;
	}

	public function getOnclickAction($service)
	{
		$data = $this->_services [$service];
		$action = "window.open(";
		$action .= "'" . Mage::getUrl('spreadtheword') . $data ['controller'] . "?service=" . $service . "',";
		$action .= "'" . $service . "_oauth',";
		$action .= "'width=" . $data ['popup_size'] ['width'] . ",height=" . $data ['popup_size'] ['height'] . ",toolbar=no,location=yes'";
		$action .= ")";
		$action .= ".focus();";
		
		return $action;
	
	}

	public function getOnclickManualAction()
	{
		$action = "stwOpenView('manual'); ";
		$action .= "stwLoadManualView('" . Mage::getSingleton('core/session')->getFormKey() . "');";
		
		return $action;
	
	}

	public function getServicesPerLine()
	{
		return (int) Mage::getStoreConfig('addinmage_spreadtheword/display/services_per_line', Mage::app()->getStore());
	}

	public function getServicesPerRow()
	{
		return (int) Mage::getStoreConfig('addinmage_spreadtheword/display/services_per_row', Mage::app()->getStore());
	}

	public function manualInvitationAllowed()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/services/manual', Mage::app()->getStore());
	}

	public function isSpecific($service)
	{
		if ($service == 'manual') return false;
		$specific_view = $this->_services [$service];
		return $specific_view ['specific_view'];
	}

	public function getSpecificView($service)
	{
		
		$service_data = $this->_services [$service];
		if ($service_data ['type'] == 'file') 
		return $this->getLayout()
			->createBlock('spreadtheword/elements_specificServices_specific', 'specific_' . $service, array ('service' => $service))
			->setTemplate('addinmage/spreadtheword/elements/specific-services/file-uploading.phtml')
			->toHtml();
		if (isset($service_data ['ext']) && $service_data ['ext']) 
		return $this->getLayout()
			->createBlock('spreadtheword/elements_specificServices_' . $service, 'specific_' . $service)
			->setTemplate('addinmage/spreadtheword/elements/specific-services/' . $service . '.phtml')
			->toHtml();
		return $this->getLayout()
			->createBlock('spreadtheword/elements_specificServices_specific', 'specific_' . $service)
			->setTemplate('addinmage/spreadtheword/elements/specific-services/' . $service . '.phtml')
			->toHtml();
	}
}