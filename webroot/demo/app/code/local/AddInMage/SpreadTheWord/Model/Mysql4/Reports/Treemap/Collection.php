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

class AddInMage_SpreadTheWord_Model_Mysql4_Reports_Treemap_Collection extends AddInMage_SpreadTheWord_Model_Mysql4_Reports_Collection_Abstract
{

	public function __construct()
	{
		$this->setModel('spreadtheword/report_item');
		$this->_resource = Mage::getResourceModel('spreadtheword/reports')->init('spreadtheword/friends');
		$this->setConnection($this->getResource()
			->getReadConnection());
		$this->_applyFilters = false;
	}

	protected function _getSelectedColumns()
	{
		if (! $this->_selectedColumns) {
			if ('month' == $this->_period) $this->_periodFormat = "DATE_FORMAT(time, '%Y-%m')";
			elseif ('year' == $this->_period) $this->_periodFormat = 'YEAR(time)';
			else $this->_periodFormat = 'DATE(time)';
			
			if (! $this->isTotals() && ! $this->isSubTotals()) {
				$this->_selectedColumns = array ();
				if ('period' == $this->_grouping) $this->_selectedColumns ['time'] = $this->_periodFormat;
				$this->_selectedColumns ['source_type'] = 'source_type';
				$this->_selectedColumns ['source'] = 'source';
				$this->_selectedColumns ['unique_access'] = 'count(DISTINCT time, source_type)';
			}
			if ($this->isTotals()) {
				$this->_selectedColumns = array ('unique_access' => 'count(DISTINCT time, source_type)');
			}
			if ($this->isSubTotals()) {
				if ('period' == $this->_grouping) $this->_selectedColumns = array ('time' => $this->_periodFormat, 'source_type' => 'source_type', 'source' => 'source', 'unique_access' => 'count(DISTINCT time, source_type)');
				else $this->_selectedColumns = array ('source_type' => 'source_type', 'source' => 'source', 'unique_access' => 'count(DISTINCT time, source_type)');
			}
		}
		return $this->_selectedColumns;
	}

	protected function _makeBoundarySelect($from, $to)
	{
		$cols = $this->_getSelectedColumns();
		$sel = $this->getConnection()
			->select()
			->from($this->getResource()
			->getMainTable(), $cols)
			->where('time >= ?', $from)
			->where('time <= ?', $to)
			->group(array ($this->_periodFormat, 'source_type', 'source'));
		$this->_applyStoresFilterToSelect($sel);
		$this->_applyServiceFilterToSelect($sel);
		$this->_applyVisitorsFilterToSelect($sel);
		
		return $sel;
	}

	protected function _initSelect()
	{
		if (! $this->_period) {
			$this->_period = 'day';
		}
		
		$this->getSelect()
			->from($this->getResource()
			->getMainTable(), $this->_getSelectedColumns());
		
		$selectUnions = $this->_handlePeriod();
		
		if (! $this->isTotals() && ! $this->isSubTotals()) {
			if ('period' == $this->_grouping) $this->getSelect()
				->group(array ($this->_periodFormat, 'source_type', 'source'));
			else $this->getSelect()
				->group(array ('source_type', 'source'))
				->order(array ('unique_access DESC'));
		}
		
		$this->_applyStoresFilter();
		$this->_applyServiceFilter();
		$this->_applyVisitorsFilter();
		$this->_applyDateRangeFilter();
		
		if ($this->isSubTotals()) {
			if ('period' == $this->_grouping) $this->getSelect()
				->group(array ($this->_periodFormat));
			else $this->getSelect()
				->group(array ('source_type'))
				->order(array ('unique_access DESC'));
		
		}
		
		if ($selectUnions) {
			$unionParts = array ();
			$cloneSelect = clone $this->getSelect();
			$unionParts [] = '(' . $cloneSelect . ')';
			foreach ($selectUnions as $union) {
				$unionParts [] = '(' . $union . ')';
			}
			$this->getSelect()
				->reset()
				->union($unionParts, Zend_Db_Select::SQL_UNION_ALL);
		}
		
		if ($this->isTotals()) {
			$cloneSelect = clone $this->getSelect();
			$this->getSelect()
				->reset()
				->from($cloneSelect, $this->getAggregatedColumns());
		}
		
		if (! $this->isTotals() && ! $this->isSubTotals() && 'period' == $this->_grouping) $this->getSelect()
			->order(array ('time ASC', 'unique_access DESC'));
		
		return $this;
	}

	public function getSelectCountSql()
	{
		$this->_renderFilters();
		return $this->getConnection()
			->select()
			->from($this->getSelect(), 'COUNT(*)');
	}
}