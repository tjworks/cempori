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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Rule_Rating_Grid extends AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Grid_Abstract
{
	protected $_columnGroupBy = false;

	public function __construct()
	{
		parent::__construct();
		$this->setCountTotals(true);
	}

	protected function _afterLoadCollection()
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $this;
		
		$filter = $this->getFilterData();
		$current_rating_cond = $filter->getRatingConditions();
		$chart_data = array ();
		$collection = $this->getCollection()
			->getItems();
		$rule_ids = array ();
		$rule_names = array ();
		
		switch ($current_rating_cond) {
		case 'invited':
		$needle = 'invited';
		break;
		case 'returned':
		$needle = 'returned';
		break;
		case 'profit':
		$needle = 'total_profit_amount';
		break;
		case 'orders':
		$needle = 'orders_count';
		break;
		}
		
		foreach ($collection as $group => $p) {
			$add = ($p->getData()) ? $p->getData() : false;
			if ($add) {
				if ($add ['rule_name']) $rule_names [] = $add ['rule_name'];
				else $rule_names [] = ($add ['stw_rule']) ? $this->__('Rule ID #%d (deleted)', $add ['stw_rule']) : $this->__('Default Mode');
				$rule_ids [$add ['stw_rule']] = null;
			}
			foreach ($p->getChildren() as $service) {
				if ($service->getData('rule_name')) $rule_names [] = $service->getData('rule_name');
				else $rule_names [] = ($service->getData('stw_rule')) ? $this->__('Rule ID #%d (deleted)', $service->getData('stw_rule')) : $this->__('Default Mode');
				$rule_ids [$service->getData('stw_rule')] = null;
			}
		}
		
		foreach ($collection as $p) {
			$i = 0;
			$add = ($p->getData()) ? $p->getData() : false;
			if ($add) {
				$i ++;
				$chart_data ['data'] [$i] = $rule_ids;
				foreach ($chart_data ['data'] [$i] as $rule => $value) {
					if ($rule == $add ['stw_rule']) {
						$chart_data ['data'] [$i] [$rule] = $add [$needle];
					}
				}
			}
			
			foreach ($p->getChildren() as $data) {
				$i ++;
				$chart_data ['data'] [$i] = $rule_ids;
				foreach ($chart_data ['data'] [$i] as $rule => $value) {
					if ($rule == $data->getData('stw_rule')) {
						$chart_data ['data'] [$i] [$rule] = $data->getData($needle);
					}
				}
			}
		}
		$chart_data ['v_point'] = $current_rating_cond;
		$chart_data ['rule_names'] = $rule_names;
		$this->_chart_data = ($chart_data && isset($chart_data ['data']) && count($chart_data ['data']) > 1) ? $chart_data : false;
		return $this;
	}

	public function _afterToHtml($html)
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $html;
		
		$chart = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_charts_rules', 'rules.chart');
		$chart->setData(array ('chart_data' => $this->_chart_data, 'filter' => $this->getFilterData()
			->toArray()));
		$this->getLayout()
			->getBlock('spreadtheword.report.grid.container')
			->setChild('adm_stw.chart', $chart);
		return $html;
	}

	public function getResourceCollectionName()
	{
		return 'spreadtheword/reports_rules_collection';
	}

	protected function _prepareColumns()
	{
		$filterData = $this->getFilterData();
		
		if ($this->getFilterData()
			->getStoreIds()) {
			$this->setStoreIds(explode(',', $this->getFilterData()
				->getStoreIds()));
		}
		
		$this->addColumn('rule_name', array (
				'header' => $this->__('Rule Name'), 
				'index' => 'rule_name', 
				'type' => 'string', 
				'sortable' => false, 
				'width' => 100, 
				'totals_label' => $this->__('Total'), 
				'html_decorators' => array ('nobr'), 
				'frame_callback' => array ($this, 'decorateDeletedRule')));
		
		$this->addColumn('rule_mode', array (
				'header' => $this->__('Rule Mode'), 
				'index' => 'rule_mode', 
				'type' => 'string', 
				'sortable' => false, 
				'width' => 100, 
				'totals_label' => false, 
				'frame_callback' => array ($this, 'decorateRuleType')));
		
		if ('invited' == $this->getFilterData()->getRatingConditions() || ! $this->getFilterData()->getRatingConditions()) {
			$this->addColumn('invited', array (
					'header' => $this->__('Invited Friends'), 
					'index' => 'invited', 
					'type' => 'number', 
					'sortable' => false, 
					'total' => 'sum'));
		}
		
		if ('returned' == $this->getFilterData()->getRatingConditions()) {
			$this->addColumn('returned', array (
					'header' => $this->__('Responded Invitees'), 
					'index' => 'returned', 
					'type' => 'number', 
					'sortable' => false, 
					'total' => 'sum'));
		}
		
		if ('profit' == $this->getFilterData()->getRatingConditions()) {
			$currencyCode = $this->getCurrentCurrencyCode();
			$this->addColumn('total_profit_amount', array (
					'header' => $this->__('Profit'), 
					'index' => 'total_profit_amount', 
					'type' => 'currency', 
					'sortable' => false, 
					'currency_code' => $currencyCode, 
					'total' => 'sum'));
		}
		
		if ('orders' == $this->getFilterData()->getRatingConditions()) {
			$this->addColumn('orders_count', array (
					'header' => $this->__('Orders'), 
					'index' => 'orders_count', 
					'type' => 'number', 
					'sortable' => false, 
					'total' => 'sum'));
		}
		
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return false;
	}

	public function decorateDeletedRule($value, $row, $column, $isExport)
	{
		if (! $row->getRuleName()) {
			if ($row->getStwRule()) $data = $this->__('Rule ID #%d. The rule was deleted.', $row->getStwRule());
			else $data = $this->__('Default Mode - "Invitation Only"');
		} else
			$data = $value;
		return $data;
	}

	public function decorateRuleType($value, $row, $column, $isExport)
	{
		switch ($row->getRuleMode()) {
		case 'noaction':
		$value = $this->__('Invitation only');
		break;
		case 'action_d_sen':
		$value = $this->__('Invitation sender gets discount');
		break;
		case 'action_d_frd':
		$value = $this->__('Invited friends get discount');
		break;
		case 'action_d_all':
		$value = $this->__('Senders and invitees get discount');
		break;
		case null:
		$value = $this->__('No information available');
		break;
		}
		return $value;
	}
}
