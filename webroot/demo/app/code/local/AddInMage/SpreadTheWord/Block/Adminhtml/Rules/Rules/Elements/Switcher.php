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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Elements_Switcher extends Mage_Adminhtml_Block_Template
{

	protected function _prepareLayout()
	{
		$this->setTemplate('addinmage/spreadtheword/switcher.phtml');
		return parent::_prepareLayout();
	}

	public function getSaveUrl()
	{
		$website = $this->getRequest()->getParam('website');
		$store = $this->getRequest()->getParam('store');
		return Mage::helper('adminhtml')->getUrl('spreadtheword/adminhtml_rules/useDefault', array ('website' => $website, 'store' => $store, 'use_parent' => true));
	}

	public function getLabel()
	{
		$website = $this->getRequest()->getParam('website');
		$store = $this->getRequest()->getParam('store');
		
		if ($store && $website) {
			$label = Mage::helper('adminhtml')->__('Use Website');
		}
		if (! $store && $website) {
			$label = Mage::helper('adminhtml')->__('Use Default');
		}
		
		return $label;
	}

	public function canShow()
	{
		
		$website = $this->getRequest()->getParam('website');
		$store = $this->getRequest()->getParam('store');
		$path = 'addinmage_spreadtheword/behaviour/rules';
		
		if ($website || $store) {
			
			if ($store && $website) {
				$param = 'stores';
				$scopeId = Mage::app()->getStore($store)->getId();
			}
			if (! $store && $website) {
				$param = 'websites';
				$scopeId = Mage::app()->getWebsite($website)->getId();
			}
			
			$config = Mage::getModel('core/config_data');
			$configData = $config->getCollection()
				->addFilter('path', $path)
				->addFilter('scope', $param)
				->addFilter('scope_id', $scopeId)
				->getFirstItem()->getConfigId();
		
		}
		
		$can_show = (($store && $website && $configData) || (! $store && $website && $configData)) ? true : false;
		return $can_show;
	}

	public function getStoreSelectOptions()
	{
		$curWebsite = $this->getRequest()->getParam('website');
		$curStore = $this->getRequest()->getParam('store');
		
		$storeModel = Mage::getSingleton('adminhtml/system_store');
		$url = Mage::getModel('adminhtml/url');
		
		$options = array ();
		$options ['default'] = array ('label' => Mage::helper('adminhtml')->__('Default Config'), 'url' => $url->getUrl('*/*/*'), 'selected' => ! $curWebsite && ! $curStore, 'style' => 'background:#ccc; font-weight:bold;');
		
		foreach ($storeModel->getWebsiteCollection() as $website) {
			$websiteShow = false;
			foreach ($storeModel->getGroupCollection() as $group) {
				if ($group->getWebsiteId() != $website->getId()) {
					continue;
				}
				$groupShow = false;
				foreach ($storeModel->getStoreCollection() as $store) {
					if ($store->getGroupId() != $group->getId()) {
						continue;
					}
					if (! $websiteShow) {
						$websiteShow = true;
						$options ['website_' . $website->getCode()] = array ('label' => $website->getName(), 'url' => $url->getUrl('*/*/*', array ('website' => $website->getCode())), 'selected' => ! $curStore && $curWebsite == $website->getCode(), 'style' => 'padding-left:16px; background:#DDD; font-weight:bold;');
					}
					if (! $groupShow) {
						$groupShow = true;
						$options ['group_' . $group->getId() . '_open'] = array ('is_group' => true, 'is_close' => false, 'label' => $group->getName(), 'style' => 'padding-left:32px;');
					}
					$options ['store_' . $store->getCode()] = array ('label' => $store->getName(), 'url' => $url->getUrl('*/*/*', array ('website' => $website->getCode(), 'store' => $store->getCode())), 'selected' => $curStore == $store->getCode(), 'style' => '');
				}
				if ($groupShow) {
					$options ['group_' . $group->getId() . '_close'] = array ('is_group' => true, 'is_close' => true);
				}
			}
		}
		
		return $options;
	}

}
