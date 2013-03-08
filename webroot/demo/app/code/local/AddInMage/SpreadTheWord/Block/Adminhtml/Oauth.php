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


class AddInMage_SpreadTheWord_Block_Adminhtml_Oauth extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected $_addRowButtonHtml = array ();

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$this->setElement($element);
		$html = $this->_getAddRowButtonHtml('allowed_services_with_oauth_support', 'allowed_services', $this->__('Add OAuth provider settings'));
		$html .= '<div id="allowed_services" style="display:none">';
		$html .= $this->_getRowTemplateHtml();
		$html .= '</div>';
		
		$html .= '<ul id="allowed_services_with_oauth_support" style="padding-top:10px">';
		if ($this->_getValue('services')) {
			foreach ($this->_getValue('services') as $i => $f) {
				if ($i) {
					$html .= $this->_getRowTemplateHtml($i);
				}
			}
		}
		$html .= '</ul>';
		
		return $html;
	}

	protected function _getRowTemplateHtml($i = 0)
	{
		$selector = "'li'";
		
		$html = '<li>';
		$html .= '<select style="background-color: #D2DAE0; padding: 2px;" name="' . $this->getElement()
			->getName() . '[services][]">';
		$html .= '<option value="">' . $this->__('* Select service provider') . '</option>';
		foreach ($this->getServices() as $carrierCode => $service) {
			$html .= '<optgroup label="' . $service ['title'] . '" style="border-top:solid 1px black; margin-top:3px;">';
			foreach ($service ['services'] as $methodCode => $method) {
				$code = $methodCode;
				$html .= '<option value="' . $code . '" ' . $this->_getSelected('services/' . $i, $code) . ' style="background:white;">' . $method ['title'] . '</option>';
			}
			$html .= '</optgroup>';
		}
		$html .= '</select>';
		
		$html .= '<div style="margin:5px 0 10px;">';
		
		$html .= '<input class="input-text" style="width:274px;" name="' . $this->getElement()
			->getName() . '[oauth_consumer][]" value="' . $this->_getValue('oauth_consumer/' . $i) . '"/> ';
		$html .= '<p class="note"><span>' . $this->__('OAuth Consumer Key') . '</span></p>';
		
		$html .= '<input class="input-text" style="width:274px;" name="' . $this->getElement()
			->getName() . '[oauth_secret][]" value="' . $this->_getValue('oauth_secret/' . $i) . '"/> ';
		$html .= '<p class="note"><span>' . $this->__('OAuth Consumer Secret') . '</span> <a href="javascript:void(0)" onclick="Element.remove($(this).up(' . $selector . '))">' . $this->__('Remove settings') . '</a></p>';
		
		$html .= '</div>';
		$html .= '</li>';
		
		return $html;
	}

	protected function getServices()
	{
		$services_data = array ();
		$mail_services = Mage::getModel('spreadtheword/configuration_allowedMailServices')->toOptionArray();
		$social_services = Mage::getModel('spreadtheword/configuration_allowedSocialServices')->toOptionArray();
		
		$services_data ['mail'] = array ('title' => $this->__('Email Services'), 'services' => array ());
		
		foreach ($mail_services as $service) {
			$service_data = Mage::helper('spreadtheword/services_' . $service ['value'])->getInfo();
			if ($service_data ['oauth_support']) $services_data ['mail'] ['services'] [$service ['value']] = array ('title' => $service ['label']);
		}
		
		$services_data ['social'] = array ('title' => $this->__('Social Networks'), 'services' => array ());
		
		foreach ($social_services as $service) {
			$service_data = Mage::helper('spreadtheword/services_' . $service ['value'])->getInfo();
			if ($service_data ['oauth_support']) $services_data ['social'] ['services'] [$service ['value']] = array ('title' => $service ['label']);
		}
		
		return $services_data;
	}

	protected function _getValue($key)
	{
		return $this->getElement()->getData('value/' . $key);
	}

	protected function _getSelected($key, $value)
	{
		return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
	}

	protected function _getAddRowButtonHtml($container, $template, $title = 'Add')
	{
		if (! isset($this->_addRowButtonHtml [$container])) {
			$this->_addRowButtonHtml [$container] = $this->getLayout()
				->createBlock('adminhtml/widget_button')
				->setType('button')
				->setClass('add')
				->setLabel($this->__($title))
				->setOnClick("Element.insert($('" . $container . "'), {top: $('" . $template . "').innerHTML})")
				->toHtml();
		}
		
		return $this->_addRowButtonHtml [$container];
	}
}
