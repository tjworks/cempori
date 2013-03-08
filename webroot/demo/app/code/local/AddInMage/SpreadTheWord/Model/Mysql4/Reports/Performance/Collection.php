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

class AddInMage_SpreadTheWord_Model_Mysql4_Reports_Performance_Collection extends AddInMage_SpreadTheWord_Model_Mysql4_Reports_Collection_Abstract
{
	
	protected $_inited = false;


	public function __construct()
	{
		parent::_construct();
		$this->setModel('spreadtheword/report_item');
		$this->_resource = Mage::getResourceModel('spreadtheword/reports')->init('sales/order', 'entity_id');
		$this->setConnection($this->getResource()
			->getReadConnection());
	}

	protected function _applyStoresFilter()
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

	protected function _getSelectedColumns()
	{
		$selectedColumns = array ();
		
		if (! $this->_show_details) {
			$selectedColumns ['total_profit_amount'] = 'IFNULL(SUM((e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate), 0)';
			$selectedColumns ['orders_count'] = 'COUNT(e.entity_id)';
			$selectedColumns ['total_qty_ordered'] = 'IFNULL(SUM(oi.total_qty_ordered), 0)';
			$selectedColumns ['total_qty_invoiced'] = 'IFNULL(SUM(oi.total_qty_invoiced), 0)';
		} else {
			$selectedColumns ['total_profit_amount'] = 'IFNULL(SUM((e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate), 0)';
			$selectedColumns ['orders_count'] = 'SUM(IF(e.stw_sales_id IS NOT NULL,1,0))';
			$selectedColumns ['gross_orders_count'] = 'COUNT(e.entity_id)';
			$selectedColumns ['gross_total_qty_ordered'] = 'IFNULL(SUM(oi.total_qty_ordered), 0)';
			$selectedColumns ['total_qty_ordered'] = 'IFNULL(SUM(IF(e.stw_sales_id IS NOT NULL,(oi.total_qty_ordered),0)), 0)';
			$selectedColumns ['total_profit_amount'] = 'IFNULL(SUM(IF(e.stw_sales_id IS NOT NULL,(e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate,0)), 0)';
			$selectedColumns += array ('gross_profit_per_period' => 'IFNULL(SUM((e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate), 0)');
		}
		
		if (! $this->isTotals()) {
			if ('month' == $this->_period) {
				$this->_periodFormat = 'DATE_FORMAT(e.updated_at, \'%Y-%m\')';
			} elseif ('year' == $this->_period) {
				$this->_periodFormat = 'EXTRACT(YEAR FROM e.updated_at)';
			} else {
				$this->_periodFormat = 'DATE(e.updated_at)';
			}
			if (! $this->isSubTotals()) {
				
				$selectedColumns += array ('time' => $this->_periodFormat, 'order_status' => 'e.status', 'order_id' => 'e.increment_id', 'source' => 'g.source', 'user_type' => 'g.user_type', 'stw_order_id' => 'e.stw_sales_id', 'real_order_id' => 'oi.order_id');
			} else
				$selectedColumns += array ('time' => $this->_periodFormat);
		}
		if ($this->isTotals() || $this->isSubTotals()) {
			if ($this->_show_details) {
				$selectedColumns ['total_profit_amount'] = 'IFNULL(SUM(IF(e.stw_sales_id IS NOT NULL,(e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate,0)), 0)';
				$selectedColumns += array ('gross_profit_per_period' => 'IFNULL(SUM((e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate), 0)');
			}
		}
		
		return $selectedColumns;
	}

	protected function _initSelect()
	{
		if ($this->_inited) {return $this;}
		
		$columns = $this->_getSelectedColumns();
		
		$mainTable = $this->getResource()
			->getMainTable();
		
		$selectOrderItem = $this->getConnection()
			->select()
			->from($this->getTable('sales/order_item'), array ('order_id' => 'order_id', 'total_qty_ordered' => 'SUM(qty_ordered - IFNULL(qty_canceled, 0))', 'total_qty_invoiced' => 'SUM(qty_invoiced)'))
			->where('parent_item_id IS NULL')
			->group('order_id');
		
		$selectStwItem = $this->getConnection()
			->select()
			->from($this->getTable('spreadtheword/sales'), array ('id' => 'id', 'source' => 'source', 'user_type' => 'user_type'));
		
		$select = $this->getSelect()
			->from(array ('e' => $mainTable), $columns)
			->join(array ('oi' => $selectOrderItem), 'oi.order_id = e.entity_id', array ());
		
		$select->joinLeft(array ('g' => $selectStwItem), 'e.stw_sales_id = g.id', array ());
		$select->where('e.state NOT IN (?)', array (Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_NEW));
		
		$this->_applyStoresFilter();
		$this->_applyOrderStatusFilter();
		$this->_applyServiceFilter();
		$this->_applyOrderSideFilter();
		
		if ($this->_to !== null) {
			$select->where('DATE(e.updated_at) <= DATE(?)', $this->_to);
		}
		
		if ($this->_from !== null) {
			$select->where('DATE(e.updated_at) >= DATE(?)', $this->_from);
		}
		
		if (! $this->_show_details) {
			$select->where('e.stw_sales_id IS NOT NULL');
		}
		
		if (! $this->isTotals()) {
			$select->group($this->_periodFormat);
			if ($this->_sum_up_data) $select->group('e.entity_id');
		}
		
		$this->_inited = true;
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