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

class AddInMage_SpreadTheWord_Model_Mysql4_Reports_Collection_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $_from = null;
	protected $_to = null;
	protected $_period = null;
	protected $_storesIds = 0;
	protected $_services = null;
	protected $_order_sides = null;
	protected $_visitors = null;
	protected $_aggregatedColumns = array ();
	protected $_applyFilters = true;
	protected $_isTotals = false;
	protected $_isSubTotals = false;
	protected $_selectedColumns = array ();
	protected $_periodFormat;
	protected $_orderStatus = null;
	protected $_show_details = false;
	protected $_grouping;
	protected $_sum_up_data = false;
	protected $_rating_condition = null;
	protected $_rule_type = null;

	public function setDetailsFlag($flag)
	{
		$this->_show_details = $flag;
		return $this;
	}

	public function setGrouping($condition)
	{
		$this->_grouping = $condition;
		return $this;
	}

	public function setSummation($summation)
	{
		$this->_sum_up_data = $summation;
		return $this;
	}

	public function setRuleType($types)
	{
		$this->_rule_type = $types;
		return $this;
	}
	
	public function setAggregatedColumns(array $columns)
	{
		$this->_aggregatedColumns = $columns;
		return $this;
	}

	public function getAggregatedColumns()
	{
		return $this->_aggregatedColumns;
	}

	public function setDateRange($from = null, $to = null)
	{
		$this->_from = $from;
		$this->_to = $to;
		return $this;
	}

	public function setPeriod($period)
	{
		$this->_period = $period;
		return $this;
	}

	public function setRatingCondition($condition)
	{
		$this->_rating_condition = $condition;
		return $this;
	}

	protected function _applyDateRangeFilter()
	{
		if (! is_null($this->_from)) {
			$this->getSelect()
				->where('time >= ?', $this->_from);
		}
		if (! is_null($this->_to)) {
			$this->getSelect()
				->where('time <= ?', $this->_to);
		}
		return $this;
	}

	public function addStoreFilter($storeIds)
	{
		$this->_storesIds = $storeIds;
		return $this;
	}

	protected function _applyStoresFilterToSelect(Zend_Db_Select $select)
	{
		$nullCheck = false;
		$storeIds = $this->_storesIds;
		$show_all = (count($storeIds) == 1 && $storeIds [0] == '') ? true : false;
		
		if ($show_all) return $this;
		
		if (! is_array($storeIds)) {
			$storeIds = array ($storeIds);
		}
		
		$storeIds = array_unique($storeIds);
		
		if ($index = array_search(null, $storeIds)) {
			unset($storeIds [$index]);
			$nullCheck = true;
		}
		
		$storeIds [0] = ($storeIds [0] == '') ? 0 : $storeIds [0];
		
		if ($nullCheck) {
			$select->where('store_id IN(?) OR store_id IS NULL', $storeIds);
		} else {
			$select->where('store_id IN(?)', $storeIds);
		}
		
		return $this;
	}

	protected function _applyServiceFilterToSelect(Zend_Db_Select $select)
	{
		if (! is_null($this->_services)) {
			
			$select->where('source IN(?)', $this->_services);
		}
		return $this;
	}

	protected function _applyOrderSideFilterToSelect(Zend_Db_Select $select)
	{
		if (! is_null($this->_order_sides)) {
			
			$select->where('user_type IN(?)', $this->_order_sides);
		}
		return $this;
	}

	protected function _applyVisitorsFilterToSelect(Zend_Db_Select $select)
	{
		if (! is_null($this->_visitors)) {
			
			$select->where('user_type IN(?)', $this->_visitors);
		}
		return $this;
	}

	protected function _applyRuleTypeFilterToSelect(Zend_Db_Select $select)
	{
		if (! is_null($this->_rule_type)) {
			
			$select->where('rule_mode IN(?)', $this->_rule_type);
		}
		return $this;
	}

	protected function _applyStoresFilter()
	{
		return $this->_applyStoresFilterToSelect($this->getSelect());
	}

	protected function _applyServiceFilter()
	{
		return $this->_applyServiceFilterToSelect($this->getSelect());
	}

	protected function _applyOrderSideFilter()
	{
		return $this->_applyOrderSideFilterToSelect($this->getSelect());
	}

	protected function _applyVisitorsFilter()
	{
		return $this->_applyVisitorsFilterToSelect($this->getSelect());
	}

	protected function _applyRuleTypeFilter()
	{
		return $this->_applyRuleTypeFilterToSelect($this->getSelect());
	}

	public function addServiceFilter($services)
	{
		$this->_services = $services;
		return $this;
	}

	public function addOrderSideFilter($sides)
	{
		$this->_order_sides = $sides;
		return $this;
	}

	public function addOrderStatusFilter($orderStatus)
	{
		$this->_orderStatus = $orderStatus;
		return $this;
	}

	public function addVisitorFilter($visitors)
	{
		$this->_visitors = $visitors;
		return $this;
	}

	public function setApplyFilters($flag)
	{
		$this->_applyFilters = $flag;
		return $this;
	}

	public function isTotals($flag = null)
	{
		if (is_null($flag)) {return $this->_isTotals;}
		$this->_isTotals = $flag;
		return $this;
	}

	public function isSubTotals($flag = null)
	{
		if (is_null($flag)) {return $this->_isSubTotals;}
		$this->_isSubTotals = $flag;
		return $this;
	}

	public function load($printQuery = false, $logQuery = false)
	{
		if ($this->isLoaded()) {return $this;}
		$this->_initSelect();
		if ($this->_applyFilters) {
			$this->_applyDateRangeFilter();
			$this->_applyStoresFilter();
			$this->_applyServiceFilter();
			$this->_applyVisitorsFilter();
		}
		return parent::load($printQuery, $logQuery);
	}

	protected function _handlePeriod()
	{
		$selectUnions = array ();
		

		$dtFormat = Varien_Date::DATE_INTERNAL_FORMAT;
		$periodFrom = (! is_null($this->_from) ? new Zend_Date($this->_from, $dtFormat) : null);
		$periodTo = (! is_null($this->_to) ? new Zend_Date($this->_to, $dtFormat) : null);
		if ('year' == $this->_period) {
			
			if ($periodFrom) {
				if ($periodFrom->toValue(Zend_Date::MONTH) != 1 || $periodFrom->toValue(Zend_Date::DAY) != 1) { 
					$dtFrom = $periodFrom->getDate();
					$dtTo = $periodFrom->getDate()
						->setMonth(12)
						->setDay(31); 
					if (! $periodTo || $dtTo->isEarlier($periodTo)) {
						$selectUnions [] = $this->_makeBoundarySelect($dtFrom->toString($dtFormat), $dtTo->toString($dtFormat));
						
						$this->_from = $periodFrom->getDate()
							->addYear(1)
							->setMonth(1)
							->setDay(1)
							->toString($dtFormat); 
					}
				}
			}
			
			if ($periodTo) {
				if ($periodTo->toValue(Zend_Date::MONTH) != 12 || $periodTo->toValue(Zend_Date::DAY) != 31) { 
					$dtFrom = $periodTo->getDate()
						->setMonth(1)
						->setDay(1); 
					$dtTo = $periodTo->getDate();
					if (! $periodFrom || $dtFrom->isLater($periodFrom)) {
						$selectUnions [] = $this->_makeBoundarySelect($dtFrom->toString($dtFormat), $dtTo->toString($dtFormat));
						
						$this->_to = $periodTo->getDate()
							->subYear(1)
							->setMonth(12)
							->setDay(31)
							->toString($dtFormat); 
					}
				}
			}
			
			if ($periodFrom && $periodTo) {
				if ($periodFrom->toValue(Zend_Date::YEAR) == $periodTo->toValue(Zend_Date::YEAR)) { // the same year
					$dtFrom = $periodFrom->getDate();
					$dtTo = $periodTo->getDate();
					$selectUnions [] = $this->_makeBoundarySelect($dtFrom->toString($dtFormat), $dtTo->toString($dtFormat));
					
					$this->getSelect()
						->where('1<>1');
				}
			}
		
		} else 
			if ('month' == $this->_period) {
				
				if ($periodFrom) {
					if ($periodFrom->toValue(Zend_Date::DAY) != 1) { 
						$dtFrom = $periodFrom->getDate();
						$dtTo = $periodFrom->getDate()
							->addMonth(1)
							->setDay(1)
							->subDay(1); 
						if (! $periodTo || $dtTo->isEarlier($periodTo)) {
							$selectUnions [] = $this->_makeBoundarySelect($dtFrom->toString($dtFormat), $dtTo->toString($dtFormat));
							
							$this->_from = $periodFrom->getDate()
								->addMonth(1)
								->setDay(1)
								->toString($dtFormat); 
						}
					}
				}
				
				if ($periodTo) {
					if ($periodTo->toValue(Zend_Date::DAY) != $periodTo->toValue(Zend_Date::MONTH_DAYS)) { 
						$dtFrom = $periodTo->getDate()
							->setDay(1); 
						$dtTo = $periodTo->getDate();
						if (! $periodFrom || $dtFrom->isLater($periodFrom)) {
							$selectUnions [] = $this->_makeBoundarySelect($dtFrom->toString($dtFormat), $dtTo->toString($dtFormat));
							
							$this->_to = $periodTo->getDate()
								->setDay(1)
								->subDay(1)
								->toString($dtFormat); 
						}
					}
				}
				
				if ($periodFrom && $periodTo) {
					if ($periodFrom->toValue(Zend_Date::YEAR) == $periodTo->toValue(Zend_Date::YEAR) && $periodFrom->toValue(Zend_Date::MONTH) == $periodTo->toValue(Zend_Date::MONTH)) { // the same month
						$dtFrom = $periodFrom->getDate();
						$dtTo = $periodTo->getDate();
						$selectUnions [] = $this->_makeBoundarySelect($dtFrom->toString($dtFormat), $dtTo->toString($dtFormat));
						
						$this->getSelect()
							->where('1<>1');
					}
				}
			
			}
		return $selectUnions;
	}
}
