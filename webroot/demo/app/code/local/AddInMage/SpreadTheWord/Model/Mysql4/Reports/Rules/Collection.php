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

class AddInMage_SpreadTheWord_Model_Mysql4_Reports_Rules_Collection extends AddInMage_SpreadTheWord_Model_Mysql4_Reports_Collection_Abstract
{
	protected $_inited = false;

	public function __construct()
	{
		parent::_construct();
		$this->setModel('spreadtheword/report_item');
		$this->_resource = Mage::getResourceModel('spreadtheword/reports')->init('spreadtheword/friends');
		$this->setConnection($this->getResource()
			->getReadConnection());
		$this->_applyFilters = false;
	}

	protected function _getSelectedColumns()
	{
		$selectedColumns = array ();
		
		if ('invited' == $this->_rating_condition || 'returned' == $this->_rating_condition) {
			if (! $this->isTotals()) {
				$selectedColumns ['stw_rule'] = 'e.stw_rule';
				$selectedColumns ['rule_name'] = 'r.rule_name';
				$selectedColumns ['rule_mode'] = 'r.rule_mode';
				if ('invited' == $this->_rating_condition) $selectedColumns ['invited'] = 'COUNT(e.invited)';
				else $selectedColumns ['returned'] = 'COUNT(e.invite_link_used)';
			} else {
				if ('invited' == $this->_rating_condition) $selectedColumns = $this->getAggregatedColumns();
				else $selectedColumns ['returned'] = 'SUM(e.invite_link_used)';
			}
		}
		
		if ('profit' == $this->_rating_condition || 'orders' == $this->_rating_condition) {
			if (! $this->isTotals()) {
				$selectedColumns ['stw_rule'] = 'g.stw_rule';
				$selectedColumns ['rule_name'] = 'r.rule_name';
				$selectedColumns ['rule_mode'] = 'r.rule_mode';
				if ('profit' == $this->_rating_condition) $selectedColumns ['total_profit_amount'] = 'IFNULL(SUM((e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate), 0)';
				else $selectedColumns ['orders_count'] = 'COUNT(e.entity_id)';
			} else {
				if ('profit' == $this->_rating_condition) $selectedColumns ['total_profit_amount'] = 'IFNULL(SUM((e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate), 0)';
				else $selectedColumns ['orders_count'] = 'COUNT(e.entity_id)';
			}
		}
		return $selectedColumns;
	}

	protected function _initSelect()
	{
		if ($this->_inited) return $this;
		
		$columns = $this->_getSelectedColumns();
		$mainTable = $this->getResource()
			->getMainTable();
		
		if ('profit' == $this->_rating_condition || 'orders' == $this->_rating_condition) $time_index = 'e.updated_at';
		else $time_index = ('invited' == $this->_rating_condition) ? 'e.invited_time' : 'e.invite_link_used_time';
		
		if ('invited' == $this->_rating_condition || 'returned' == $this->_rating_condition) {
			
			$selectRules = $this->getConnection()
				->select()
				->from($this->getTable('spreadtheword/rules'), array ('id' => 'id', 'rule_name' => 'rule_name', 'rule_mode' => 'rule_mode'));
			
			$select = $this->getSelect()
				->from(array ('e' => $mainTable), $columns)
				->joinLeft(array ('r' => $selectRules), 'e.stw_rule = r.id', array ());
			
			$this->_applyStoresFilter();
			$this->_applyVisitorsFilter();
			
			if ('invited' == $this->_rating_condition) $select->where('e.invited > 0');
			
			if ('returned' == $this->_rating_condition) $select->where('e.invite_link_used > 0');
		
		}
		
		if ('profit' == $this->_rating_condition || 'orders' == $this->_rating_condition) {
			
			$selectStwItem = $this->getConnection()
				->select()
				->from($this->getTable('spreadtheword/sales'), array ('id' => 'id', 'stw_rule' => 'stw_rule', 'source' => 'source', 'user_type' => 'user_type'));
			
			$selectRules = $this->getConnection()
				->select()
				->from($this->getTable('spreadtheword/rules'), array ('id' => 'id', 'rule_name' => 'rule_name', 'rule_mode' => 'rule_mode'));
			
			$select = $this->getSelect()
				->from(array ('e' => $this->getTable('sales/order')), $columns)
				->join(array ('g' => $selectStwItem), 'e.stw_sales_id = g.id', array ());
			
			$select->joinLeft(array ('r' => $selectRules), 'g.stw_rule = r.id', array ());
			$select->where('e.state NOT IN (?)', array (Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_NEW));
			
			$this->_applyStoresFilterOnSales();
			$this->_applyOrderStatusFilter();
			$this->_applyOrderSideFilter();
			
			$select->where('e.stw_sales_id IS NOT NULL');
		}
		
		$this->_applyServiceFilter();
		$this->_applyRuleTypeFilter();
		
		if ($this->_to !== null) {
			$select->where("DATE($time_index) <= DATE(?)", $this->_to);
		}
		
		if ($this->_from !== null) {
			$select->where("DATE($time_index) >= DATE(?)", $this->_from);
		}
		
		if (! $this->isTotals()) {
			if ('invited' == $this->_rating_condition) $select->group('stw_rule')
				->order('invited DESC');
			if ('returned' == $this->_rating_condition) $select->group('stw_rule')
				->order('returned DESC');
			if ('profit' == $this->_rating_condition) $select->group('stw_rule')
				->order('total_profit_amount DESC');
			if ('orders' == $this->_rating_condition) $select->group('stw_rule')
				->order('orders_count DESC');
		}
		
		$this->_inited = true;
		
		return $this;
	}

	protected function _applyOrderStatusFilter()
	{
		if (is_null($this->_orderStatus)) {return $this;}
		$orderStatus = $this->_orderStatus;
		if (! is_array($orderStatus)) {
			$orderStatus = array ($orderStatus);
		}
		$this->getSelect()
			->where('status IN(?)', $orderStatus);
		return $this;
	}

	protected function _applyStoresFilterOnSales()
	{
		$nullCheck = false;
		$storeIds = $this->_storesIds;
		
		if (! is_array($storeIds)) {
			$storeIds = array ($storeIds);
		}
		
		$storeIds = array_unique($storeIds);
		
		if ($index = array_search(null, $storeIds)) {
			unset($storeIds [$index]);
			$nullCheck = true;
		}
		
		if ($nullCheck) {
			$this->getSelect()
				->where('store_id IN(?) OR store_id IS NULL', $storeIds);
		} elseif ($storeIds [0] != '') {
			$this->getSelect()
				->where('store_id IN(?)', $storeIds);
		}
		
		return $this;
	}

	public function load($printQuery = false, $logQuery = false)
	{
		if ($this->isLoaded()) {return $this;}
		$this->_initSelect();
		$this->setApplyFilters(false);
		return parent::load($printQuery, $logQuery);
	}
}