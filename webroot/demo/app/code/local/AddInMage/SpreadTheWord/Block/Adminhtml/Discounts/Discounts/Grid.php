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

class AddInMage_SpreadTheWord_Block_Adminhtml_Discounts_Discounts_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('spreadtheword_discounts_grid');
		$this->setDefaultSort('discount_amount');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->_filterVisibility = true;
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('spreadtheword/sales')->getCollection();
		$resource = Mage::getSingleton('core/resource');
		
		$collection->getSelect()
			->join(array (
					'coupon' => $resource->getTableName('salesrule/coupon')), 
					'coupon.code = main_table.real_discount', 
					array ('main_table.id', 'main_table.source', 'main_table.source', 'main_table.user_type', 'coupon.rule_id'));
		
		$collection->getSelect()
			->join(array (
					'rule' => $resource->getTableName('salesrule/rule')), 
					'rule.rule_id = coupon.rule_id', 
					array ('rule.name', 'rule.to_date', 'rule.discount_amount', 'rule.simple_action'));
		
		$collection->getSelect()->where('rule.discount_amount !=0');
		$this->setCollection($collection);
		
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('name', array (
				'header' => $this->__('Discount Name'), 
				'align' => 'left', 
				'index' => 'name', 
				'filter_index' => 'rule.name'));
		
		$this->addColumn('real_discount', array (
				'header' => $this->__('Discount Code'), 
				'align' => 'left', 
				'index' => 'real_discount', 
				'filter_index' => 'main_table.real_discount'));
		
		$this->addColumn('short_discount', array (
				'header' => $this->__('Short Discount Alias'), 
				'align' => 'left', 
				'index' => 'short_discount', 
				'filter_index' => 'main_table.short_discount', 
				'frame_callback' => array ($this, 'decoratePrefix')));
		
		$this->addColumn('discount_amount', array (
				'header' => $this->__('Discount Amount'), 
				'align' => 'left', 
				'index' => 'discount_amount', 
				'type' => 'number', 
				'filter_index' => 'rule.discount_amount', 
				'frame_callback' => array ($this, 'getAmount')));
		
		$this->addColumn('to_date', array (
				'header' => $this->__('Discount Expiration Date'), 
				'align' => 'center', 
				'index' => 'to_date', 
				'type' => 'date', 
				'filter_index' => 'rule.to_date'));
		
		$this->addColumn('source', array (
				'header' => $this->__('Service / Tool'), 
				'index' => 'source', 
				'filter_index' => 'main_table.source', 
				'frame_callback' => array ($this, 'decorateService')));
		
		$this->addColumn('user_type', array (
				'header' => $this->__('Given To'), 
				'index' => 'user_type', 
				'type' => 'options', 
				'options' => array ('sender' => $this->__('Sender'), 'friend' => $this->__('Friend (invitee)')), 
				'filter_index' => 'main_table.user_type'));
		
		if (Mage::getSingleton('admin/session')->isAllowed('promo/quote')) 
			$this->addColumn('action', array (
					'header' => $this->__('Action'), 
					'align' => 'center', 
					'width' => 20, 
					'filter' => false, 
					'index' => 'action', 
					'type' => 'string', 
					'sortable' => false, 
					'frame_callback' => array ($this, 'getRuleUrl')));
		
		return parent::_prepareColumns();
	}


	public function decoratePrefix($value, $row, $column, $isExport)
	{
		return ('sender' == $row->getUserType()) ? '0' . $value : '1' . $value;
	}

	public function decorateService($value, $row, $column, $isExport)
	{
		$value = ucfirst($value);
		
		return $value;
	}

	public function getAmount($value, $row, $column, $isExport)
	{
		return Mage::helper('spreadtheword')->formatDiscount($value, $row->getSimpleAction());
	}

	public function getRuleUrl($value, $row, $column, $isExport)
	{
		return "<a target='_blank' title='{$this->__('See discount information in a new tab')}' href='{$this->getUrl('adminhtml/promo_quote/edit', array('id' => $row->getRuleId()))}'>{$this->__('View')}</a>";
	}

}