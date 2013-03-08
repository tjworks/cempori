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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Grid_Abstract extends Mage_Adminhtml_Block_Widget_Grid
{
	protected $_resourceCollectionName = '';
	protected $_currentCurrencyCode = null;
	protected $_storeIds = array ();
	protected $_aggregatedColumns = null;
	protected $_dynamics = array ();
	protected $_chart_data;

	public function __construct()
	{
		parent::__construct();
		$this->setFilterVisibility(false);
		$this->setPagerVisibility(false);
		$this->setUseAjax(false);
		if (isset($this->_columnGroupBy)) {
			$this->isColumnGrouped($this->_columnGroupBy, true);
		}
		$this->setEmptyCellLabel(Mage::helper('reports')->__('No records found for this period.'));
	}

	public function getResourceCollectionName()
	{
		return $this->_resourceCollectionName;
	}

	public function getCollection()
	{
		if (is_null($this->_collection)) {
			$model = Mage::getModel('spreadtheword/grouped_collection');
			$model->setPeriod($this->getFilterData()
				->getPeriodType());
			$this->setCollection($model);
		}
		
		return $this->_collection;
	}

	protected function _getAggregatedColumns()
	{
		if (is_null($this->_aggregatedColumns)) {
			foreach ($this->getColumns() as $column) {
				if (! is_array($this->_aggregatedColumns)) {
					$this->_aggregatedColumns = array ();
				}
				if ($column->hasTotal()) {
					$this->_aggregatedColumns [$column->getId()] = "{$column->getTotal()}({$column->getIndex()})";
				}
			}
		}
		return $this->_aggregatedColumns;
	}

	public function addColumn($columnId, $column)
	{
		if (is_array($column) && array_key_exists('visibility_filter', $column)) {
			$filterData = $this->getFilterData();
			$visibilityFilter = $column ['visibility_filter'];
			if (! is_array($visibilityFilter)) {
				$visibilityFilter = array ($visibilityFilter);
			}
			foreach ($visibilityFilter as $k => $v) {
				if (is_int($k)) {
					$filterFieldId = $v;
					$filterFieldValue = true;
				} else {
					$filterFieldId = $k;
					$filterFieldValue = $v;
				}
				if (! $filterData->hasData($filterFieldId) || $filterData->getData($filterFieldId) != $filterFieldValue) {return $this;				// don't add column
				}
			}
		}
		return parent::addColumn($columnId, $column);
	}

	protected function _prepareCollection()
	{
		$filterData = $this->getFilterData();
		
		if ($filterData->getData('detailed_information', false)) $this->setCountSubTotals(true);
		
		if ($filterData->getData('from') == null || $filterData->getData('to') == null) {
			$this->setCountTotals(false);
			$this->setCountSubTotals(false);
			return parent::_prepareCollection();
		}
		
		$resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())->setPeriod($filterData->getData('period_type'))
			->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
			->addStoreFilter(explode(',', $filterData->getData('store_ids')))
			->addOrderStatusFilter($filterData->getData('order_statuses'))
			->addServiceFilter($filterData->getData('services', null))
			->addVisitorFilter($filterData->getData('visitor_type', null))
			->setDetailsFlag($filterData->getData('detailed_information', false))
			->setGrouping(($filterData->getGroupBy()) ? $filterData->getData('group_by') : null)
			->setSummation(($filterData->getSumUpDataPerPeriod()) ? false : true)
			->setRatingCondition(($filterData->getData('rating_conditions')) ? $filterData->getData('rating_conditions') : 'invited')
			->setRuleType($filterData->getData('rule_type'))
			->addOrderSideFilter($filterData->getData('order_placed_by'))
			->setAggregatedColumns($this->_getAggregatedColumns());
		if ($this->_isExport) {
			$this->setCollection($resourceCollection);
			return $this;
		}
		
		if ($filterData->getData('show_empty_rows', false)) {
			Mage::helper('spreadtheword/report')->prepareIntervalsCollection($this->getCollection(), $filterData->getData('from', null), $filterData->getData('to', null), $filterData->getData('period_type'));
		}
		
		if ($this->getCountSubTotals()) {
			$this->getSubTotals();
		}
		
		if ($this->getCountTotals()) {
			$totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())->setPeriod($filterData->getData('period_type'))
				->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
				->addStoreFilter(explode(',', $filterData->getData('store_ids')))
				->addOrderStatusFilter($filterData->getData('order_statuses'))
				->addServiceFilter($filterData->getData('services', null))
				->setDetailsFlag($filterData->getData('detailed_information', false))
				->addVisitorFilter($filterData->getData('visitor_type', null))
				->setRatingCondition(($filterData->getData('rating_conditions')) ? $filterData->getData('rating_conditions') : 'invited')
				->setRuleType($filterData->getData('rule_type'))
				->addOrderSideFilter($filterData->getData('order_placed_by'))
				->setAggregatedColumns($this->_getAggregatedColumns())
				->isTotals(true);
			foreach ($totalsCollection as $item) {
				$this->setTotals($item);
				break;
			}
		}
		
		$this->getCollection()
			->setColumnGroupBy($this->_columnGroupBy);
		$this->getCollection()
			->setResourceCollection($resourceCollection);
		
		return parent::_prepareCollection();
	}

	public function getCountTotals()
	{
		if (! $this->getTotals()) {
			$filterData = $this->getFilterData();
			
			$totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())->setPeriod($filterData->getData('period_type'))
				->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
				->addStoreFilter(explode(',', $filterData->getData('store_ids')))
				->addOrderStatusFilter($filterData->getData('order_statuses'))
				->addServiceFilter($filterData->getData('services', null))
				->addVisitorFilter($filterData->getData('visitor_type', null))
				->setDetailsFlag($filterData->getData('detailed_information', false))
				->setRatingCondition(($filterData->getData('rating_conditions')) ? $filterData->getData('rating_conditions') : 'invited')
				->setRuleType($filterData->getData('rule_type'))
				->addOrderSideFilter($filterData->getData('order_placed_by'))
				->setAggregatedColumns($this->_getAggregatedColumns())
				->isTotals(true);
			if (count($totalsCollection->getItems()) < 1 || ! $filterData->getData('from')) {
				$this->setTotals(new Varien_Object());
			} else {
				foreach ($totalsCollection->getItems() as $item) {
					$this->setTotals($item);
					break;
				}
			}
		}
		return parent::getCountTotals();
	}

	public function getSubTotals()
	{
		$filterData = $this->getFilterData();
		$subTotalsCollection = Mage::getResourceModel($this->getResourceCollectionName())->setPeriod($filterData->getData('period_type'))
			->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
			->addStoreFilter(explode(',', $filterData->getData('store_ids')))
			->addOrderStatusFilter($filterData->getData('order_statuses'))
			->addServiceFilter($filterData->getData('services', null))
			->addOrderSideFilter($filterData->getData('order_placed_by'))
			->addVisitorFilter($filterData->getData('visitor_type', null))
			->setGrouping(($filterData->getGroupBy()) ? $filterData->getData('group_by') : null)
			->setAggregatedColumns($this->_getAggregatedColumns())
			->setDetailsFlag($filterData->getData('detailed_information', false))
			->isSubTotals(true);
		$this->setSubTotals($subTotalsCollection->getItems());
		return parent::getSubTotals();
	}

	public function setStoreIds($storeIds)
	{
		$this->_storeIds = $storeIds;
		return $this;
	}

	public function getCurrentCurrencyCode()
	{
		if (is_null($this->_currentCurrencyCode)) {
			$this->_currentCurrencyCode = (count($this->_storeIds) > 0) ? Mage::app()->getStore(array_shift($this->_storeIds))
				->getBaseCurrencyCode() : Mage::app()->getStore()
				->getBaseCurrencyCode();
		}
		return $this->_currentCurrencyCode;
	}
}
