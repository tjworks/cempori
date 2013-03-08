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


class AddInMage_SpreadTheWord_Block_Adminhtml_Rules_Rules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('spreadtheword_grid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('spreadtheword/rules')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	public function _getStore()
	{
		$storeId = (int) $this->getRequest()
			->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array ('header' => $this->__('ID'), 'align' => 'right', 'width' => '30px', 'index' => 'id'));
		
		$this->addColumn('rule_name', array ('header' => $this->__('Rule Name'), 'align' => 'left', 'index' => 'rule_name'));
		
		$this->addColumn('rule_mode', array ('header' => $this->__('Rule Mode'), 'type' => 'text', 'index' => 'rule_mode', 'align' => 'left', 'filter' => 'adminhtml/widget_grid_column_filter_select', 'options' => Mage::helper('spreadtheword')->getRuleModes(), 'renderer' => 'spreadtheword/adminhtml_rules_rules_grid_renderer_modes'));
		
		$this->addColumn('action', array ('header' => $this->__('Action'), 'align' => 'center', 'width' => '50', 'type' => 'action', 'filter' => false, 'sortable' => false, 'frame_callback' => array ($this, 'decorateAction')));
		
		$this->addColumn('is_in_use', array ('header' => $this->__('Rule in Use'), 'align' => 'center', 'width' => '80', 'type' => 'options', 'filter' => false, 'sortable' => false, 'frame_callback' => array ($this, 'decorateInUse')));
		
		$this->addColumn('sender_targeting', array ('header' => $this->__('Targeting for Senders'), 'align' => 'center', 'width' => '80', 'type' => 'options', 'index' => 'sender_targeting', 'options' => array (1 => $this->__('Yes'), 0 => $this->__('No')), 'frame_callback' => array ($this, 'decorateSenderTargeting')));
		
		$this->addColumn('friends_targeting', array ('header' => $this->__('Targeting for Friends'), 'align' => 'center', 'width' => '80', 'type' => 'options', 'index' => 'friends_targeting', 'options' => array (1 => $this->__('Yes'), 0 => $this->__('No')), 'frame_callback' => array ($this, 'decorateFriendsTargeting')));
		
		$this->addColumn('conflicts', array ('header' => $this->__('Errors'), 'align' => 'center', 'width' => '80px', 'index' => 'conflicts', 'type' => 'options', 'options' => array (1 => $this->__('Yes'), 0 => $this->__('No')), 'frame_callback' => array ($this, 'decorateError')));
		
		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()
			->setFormFieldName('spreadtheword_massaction_ids');
		
		$this->getMassactionBlock()
			->addItem('delete', array ('label' => $this->__('Delete all rules that are not in use'), 'url' => $this->getUrl('*/*/massDelete'), 'width' => '50', 'confirm' => $this->__('Are you sure you want to delete all selected rules?')));
		return $this;
	}

	public function decorateAction($value, $row, $column, $isExport)
	{
		if ($this->inUseForCurrentStore($row) || $row->getConflicts()) return;
		$website = $this->getRequest()
			->getParam('website');
		$store = $this->getRequest()
			->getParam('store');
		$link = Mage::getUrl('*/*/useIt', array ('id' => $row->getId(), '_current' => false));
		$link = Mage::helper('adminhtml')->getUrl('spreadtheword/adminhtml_rules/useIt', array ('id' => $row->getId(), 'website' => $website, 'store' => $store));
		$class = 'adm-stw-action';
		$caption = $this->__('Use It');
		$title = $this->__('Use this rule for the current store');
		$value = '<a title="' . $title . '" class="' . $class . '" href="' . $link . '">' . $caption . '</a>';
		return $value;
	}

	protected function _check()
	{
		$rules = Mage::getModel('spreadtheword/rules')->getCollection()
			->addFilter('errors', '')
			->addFilter('conflicts', '0')
			->count();
		
		if ($rules) return true;
		
		return false;
	}

	protected function inUseForCurrentStore($row)
	{
		
		$path = 'addinmage_spreadtheword/behaviour/rules';
		$website = $this->getRequest()
			->getParam('website');
		$store = $this->getRequest()
			->getParam('store');
		$param = 'default';
		$scopeId = 0;
		
		if ($store && $website) {
			$param = 'store';
			$scopeId = $store;
		}
		if (! $store && $website) {
			$param = 'website';
			$scopeId = $website;
		}
		$configData = (string) Mage::getConfig()->getNode($path, $param, $scopeId);
		
		$is_in_use = ($configData == $row->getId()) ? true : false;
		return $is_in_use;
	}

	public function decorateError($value, $row, $column, $isExport)
	{
		$class = '';
		switch ($row->getConflicts()) {
		case 0:
		$class = 'adm-stw-grid-no';
		break;
		case 1:
		$class = 'adm-stw-grid-yes';
		break;
		}
		return '<span class="' . $class . '"><span>' . $value . '</span></span>';
	}

	public function decorateSenderTargeting($value, $row, $column, $isExport)
	{
		$class = '';
		switch ($row->getSenderTargeting()) {
		case 0:
		$class = 'adm-stw-grid-no';
		break;
		case 1:
		$class = 'adm-stw-grid-yes';
		break;
		}
		return '<span class="' . $class . '"><span>' . $value . '</span></span>';
	}

	public function decorateFriendsTargeting($value, $row, $column, $isExport)
	{
		$class = '';
		switch ($row->getFriendsTargeting()) {
		case 0:
		$class = 'adm-stw-grid-no';
		break;
		case 1:
		$class = 'adm-stw-grid-yes';
		break;
		}
		return '<span class="' . $class . '"><span>' . $value . '</span></span>';
	}

	public function decorateInUse($value, $row, $column, $isExport)
	{
		$is_in_use = $this->inUseForCurrentStore($row);
		$class = '';
		switch ($is_in_use) {
		case false:
		$class = 'adm-stw-grid-no';
		$value = $this->__('No');
		break;
		case true:
		$class = 'adm-stw-grid-yes';
		$value = $this->__('Yes');
		break;
		}
		return '<span class="' . $class . '"><span>' . $value . '</span></span>';
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array ('id' => $row->getId()));
	}

}