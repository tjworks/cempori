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

class AddInMage_SpreadTheWord_Model_Mysql4_Reports_Visitors_Collection extends AddInMage_SpreadTheWord_Model_Mysql4_Reports_Collection_Abstract
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
				$this->_selectedColumns ['source'] = 'source';
				$this->_selectedColumns ['user_type'] = 'user_type';
				$this->_selectedColumns ['imported'] = 'id';
				$this->_selectedColumns ['invited'] = 'invited';
				$this->_selectedColumns ['invite_link_used'] = 'invite_link_used';
			}
			if ($this->isTotals()) {
				$this->_selectedColumns = array ('imported' => 'count(id)') + $this->getAggregatedColumns();
			}
			if ($this->isSubTotals()) {
				if ('service' == $this->_grouping) $this->_selectedColumns = array ('source' => 'source', 'imported' => 'count(id)') + $this->getAggregatedColumns();
				elseif ('visitor_type' == $this->_grouping) $this->_selectedColumns = array ('user_type' => 'user_type', 'imported' => 'count(id)') + $this->getAggregatedColumns();
				else $this->_selectedColumns = array ('time' => $this->_periodFormat, 'imported' => 'count(id)') + $this->getAggregatedColumns();
			}
		}
		return $this->_selectedColumns;
	}

	protected function _makeBoundarySelect($from, $to)
	{
		$cols = $this->_getSelectedColumns();
		$cols ['imported'] = 'COUNT(*)';
		$cols ['invited'] = 'SUM(invited)';
		$cols ['invite_link_used'] = 'SUM(invite_link_used)';
		$sel = $this->getConnection()
			->select()
			->from($this->getResource()
			->getMainTable(), $cols)
			->where('time >= ?', $from)
			->where('time <= ?', $to);
		if ('period' == $this->_grouping) $sel->group(array ($this->_periodFormat, 'user_type'));
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
		
		$columns = $this->getSelect()
			->getPart('columns');
		
		foreach ($columns as $index => $column) {
			if ($column [1] == 'id') {
				$column [1] = new Zend_Db_Expr('count(*)');
				$columns [$index] = $column;
			
			}
			if ($column [1] == 'invited') {
				$column [1] = new Zend_Db_Expr('sum(invited)');
				$columns [$index] = $column;
			}
			if ($column [1] == 'invite_link_used') {
				$column [1] = new Zend_Db_Expr('sum(invite_link_used)');
				$columns [$index] = $column;
			}
		}
		
		if (! $this->isTotals() && ! $this->isSubTotals()) {
			if ('period' == $this->_grouping) $this->getSelect()
				->group(array ($this->_periodFormat, 'user_type'));
			else $this->getSelect()
				->group(array ('source', 'user_type'))
				->order(array ('source ASC', 'imported DESC'));
		}
		
		$this->getSelect()
			->setPart('columns', $columns);
		
		$this->_applyStoresFilter();
		$this->_applyServiceFilter();
		$this->_applyVisitorsFilter();
		$this->_applyDateRangeFilter();
		
		if ($this->isSubTotals()) {
			if ('service' == $this->_grouping) $this->getSelect()
				->group('source');
			elseif ('visitor_type' == $this->_grouping) $this->getSelect()
				->group('user_type');
			else $this->getSelect()
				->group($this->_periodFormat);
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
				->from($cloneSelect, array ('imported' => 'sum(imported)', 'invited' => 'sum(invited)', 'invite_link_used' => 'sum(invite_link_used)'));
		}
		
		if (! $this->isTotals() && ! $this->isSubTotals() && 'period' == $this->_grouping) $this->getSelect()
			->order(array ('time ASC', 'source ASC', 'imported DESC'));
		
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