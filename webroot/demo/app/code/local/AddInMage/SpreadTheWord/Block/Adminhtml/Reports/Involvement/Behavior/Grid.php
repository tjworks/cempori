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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Involvement_Behavior_Grid extends AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Grid_Abstract
{
	protected $_columnGroupBy = false;

	public function __construct()
	{
		parent::__construct();
		$this->setCountTotals(false);
	}

	protected function _afterLoadCollection()
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $this;
		
		$filter = $this->getFilterData();
		$chart_data = array ();
		$collection = $this->getCollection()
			->getItems();
		
		$periods = array ();
		$period_data = array ();
		
		foreach ($collection as $group => $p) {
			$add = ($p->getData()) ? $p->getData() : false;
			if ($add) $periods [$add ['period']] = array ('percentage' => null, 'people' => null);
			
			foreach ($p->getChildren() as $service)
				$periods [$service->getData('period')] = array ('percentage' => null, 'people' => null);
		
		}
		foreach ($collection as $p) {
			$i = 0;
			$add = ($p->getData()) ? $p->getData() : false;
			if ($add) {
				$i ++;
				
				$chart_data ['data'] [$i] = $periods;
				$chart_data ['data'] [$i] ['period'] = $add ['period'];
				foreach ($chart_data ['data'] [$i] as $period => $value) {
					if ((string) $period == (string) $add ['period']) {
						$chart_data ['data'] [$i] [$period] = array ('percentage' => ($add ['percentage'] < 1) ? 'less' : $add ['percentage'], 'people' => $add ['people']);
					}
				}
			}
			
			foreach ($p->getChildren() as $data) {
				$i ++;
				$chart_data ['data'] [$i] = $periods;
				$chart_data ['data'] [$i] ['period'] = (string) $data->getData('period');
				foreach ($chart_data ['data'] [$i] as $period => $value) {
					if ((string) $period == (string) $data->getData('period')) {
						$chart_data ['data'] [$i] [$period] = array ('percentage' => ($data->getData('percentage') < 1) ? 'less' : $data->getData('percentage'), 'people' => $data->getData('people'));
					}
				}
			}
		}
		
		$chart_data ['periods'] = $periods;
		$this->_chart_data = (isset($chart_data ['data']) && count($chart_data ['data']) > 1) ? $chart_data : false;
		return $this;
	}

	public function _afterToHtml($html)
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $html;
		
		$chart = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_charts_behavior', 'behavior.chart');
		$chart->setData(array ('chart_data' => $this->_chart_data, 'filter' => $this->getFilterData()
			->toArray()));
		$this->getLayout()
			->getBlock('spreadtheword.report.grid.container')
			->setChild('adm_stw.chart', $chart);
		return $html;
	}

	public function getResourceCollectionName()
	{
		return 'spreadtheword/reports_involvement_collection';
	}

	protected function _prepareColumns()
	{
		$filterData = $this->getFilterData();
		
		if ($this->getFilterData()
			->getStoreIds()) {
			$this->setStoreIds(explode(',', $this->getFilterData()
				->getStoreIds()));
		}
		
		$this->addColumn('period', array (
				'header' => $this->__('Period'), 
				'index' => 'period', 
				'type' => 'string', 
				'sortable' => false, 
				'width' => 100, 
				'frame_callback' => array ($this, 'decoratePeriod')));
		
		$this->addColumn('percentage', array (
				'header' => $this->__('Percentage'), 
				'index' => 'percentage', 
				'type' => 'string', 
				'sortable' => false, 
				'width' => 100, 
				'frame_callback' => array ($this, 'decoratePercentage')));
		
		$this->addColumn('people', array (
				'header' => $this->__('People'), 
				'index' => 'people', 
				'type' => 'number', 
				'sortable' => false));
		
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return false;
	}

	public function decoratePeriod($value, $row, $column, $isExport)
	{
		$label = '';
		if ($row->getPeriod() == 'more' || $row->getPeriod() <= 1) switch ($row->getPeriod()) {
			case 'more':
			$label = $this->__('In more than a month');
			break;
			case '0':
			$label = $this->__('On the same day');
			break;
			case '1':
			$label = $this->__('On the next day');
			break;
		}
		else $label = $this->__('%s days later', $row->getPeriod());
		
		return $label;
	}

	public function decoratePercentage($value, $row, $column, $isExport)
	{
		if ($row->getPercentage() == 0) return $this->__('Less than 1 %');
		
		return $value . '%';
	}
}
