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

class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Service_Treemap_Grid extends AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Grid_Abstract
{
	protected $_columnGroupBy = 'source_type';
	protected $_service_group = array ();

	public function __construct()
	{
		$requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()
			->getParam('filter'));
		if (isset($requestData ['group_by']) && 'period' == $requestData ['group_by']) $this->_columnGroupBy = 'time';
		parent::__construct();
		$this->setCountTotals(true);
		$this->setCountSubTotals(true);
	}

	protected function _afterLoadCollection()
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $this;
		
		$filter = $this->getFilterData();
		
		$chart_data = array ();
		$collection = $this->getCollection()
			->getItems();
		
		if ('period' == $filter->getData('group_by')) {
			$service_array = array ();
			foreach ($collection as $p) {
				if (! $p->getIsEmpty()) {
					$add = ($p->getData()) ? $p->getData() : false;
					if ($add) {
						if (! isset($service_array [$add ['source']])) $service_array [$add ['source']] = array ('frequency' => null);
						$service_array [$add ['source']] ['frequency'] += $add ['unique_access'];
					}
					foreach ($p->getChildren() as $service) {
						$service = $service->getData();
						if (! isset($service_array [$service ['source']])) $service_array [$service ['source']] = array ('frequency' => null);
						$service_array [$service ['source']] ['frequency'] += $service ['unique_access'];
					}
				}
			}
			
			foreach ($service_array as $service_code => $data) {
				$chart_data [] = array ('service' => $service_code, 'frequency' => $data ['frequency']);
			}
		} else {
			foreach ($collection as $group => $p) {
				$add = ($p->getData()) ? $p->getData() : false;
				if ($add) $chart_data [] = array ('service' => $add ['source'], 'frequency' => $add ['unique_access']);
				foreach ($p->getChildren() as $service)
					$chart_data [] = array ('service' => $service->getData('source'), 'frequency' => $service->getData('unique_access'));
			}
		}
		$this->_chart_data = ($chart_data && count($chart_data) > 1) ? $chart_data : false;
		return $this;
	}

	public function _afterToHtml($html)
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $html;
		
		$chart = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_charts_treemap', 'treemap.chart');
		$chart->setData(array ('chart_data' => $this->_chart_data, 'filter' => $this->getFilterData()
			->toArray()));
		$this->getLayout()
			->getBlock('spreadtheword.report.grid.container')
			->setChild('adm_stw.chart', $chart);
		
		return $html;
	}

	public function getResourceCollectionName()
	{
		return 'spreadtheword/reports_treemap_collection';
	}

	protected function _prepareColumns()
	{
		$filterData = $this->getFilterData();
		$grouping = ('time' == $this->_columnGroupBy) ? true : false;
		
		if ($grouping) {
			$this->addColumn('time', array (
					'header' => $this->__('Period'), 
					'index' => 'time', 
					'width' => 100, 
					'sortable' => false, 
					'period_type' => $this->getPeriodType(), 
					'renderer' => 'adminhtml/report_sales_grid_column_renderer_date', 
					'totals_label' => $this->__('Total'), 
					'html_decorators' => array ('nobr'), 
					'subtotals_label' => ''));
		}
		
		if ($this->getFilterData()->getStoreIds()) {
			$this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
		}
		
		$source_type = $this->addColumn('source_type', array (
				'header' => $this->__('Service Group'), 
				'index' => 'source_type', 
				'type' => 'string', 
				'sortable' => false, 
				'frame_callback' => array ($this, 'decorateServiceType'), 
				'subtotals_label' => ''));
		
		if (! $grouping) 
			$this->getColumn('source_type')->addData(array ('totals_label' => $this->__('Total')));
		
		$this->addColumn('source', array (
				'header' => $this->__('Service Name'), 
				'index' => 'source', 
				'type' => 'string', 
				'sortable' => false, 
				'frame_callback' => array ($this, 'decorateService'), 
				'subtotals_label' => $this->__('Subtotal'))

		);
		
		if (! $this->_isExport && $filterData->getShowDynamics()) {
			$this->addColumn('dynamic', array (
					'header' => $this->__('Trend'), 
					'type' => 'number', 
					'sortable' => false, 
					'width' => 10, 
					'subtotals_label' => false, 
					'frame_callback' => array ($this, 'decorateDynamic')));
		}
		
		$this->addColumn('unique_access', array (
				'header' => $this->__('Frequency of Use'), 
				'index' => 'unique_access', 
				'type' => 'number', 
				'sortable' => false, 
				'total' => 'sum'));
		
		$this->addExportType('*/*/exportTreemapCsv', $this->__('CSV'));
		$this->addExportType('*/*/exportTreemapExcel', $this->__('Excel XML'));
		
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return false;
	}

	public function decorateService($value, $row, $column, $isExport)
	{
		return ucfirst($value);
	}

	public function decorateDynamic($value, $row, $column, $isExport)
	{
		
		if (isset($this->_dynamics [$row->getSource()])) {
			if ($this->_dynamics [$row->getSource()] ['previous'] < $row->getUniqueAccess()) $dynamics = 'adm-up';
			elseif ($this->_dynamics [$row->getSource()] ['previous'] == $row->getUniqueAccess()) $dynamics = 'adm-eq';
			else $dynamics = 'adm-down';
			
			$this->_dynamics [$row->getSource()] ['previous'] = $row->getUniqueAccess();
			return '<span class="' . $dynamics . ' adm-dynamics"></span>';
		} else
			$this->_dynamics [$row->getSource()] = array ('previous' => $row->getUniqueAccess());
	}

	public function decorateServiceType($value, $row, $column, $isExport)
	{
		$data = false;
		switch ($row->getSourceType()) {
			case 'social':
			$data = $this->__('Social Networks');
			break;
			case 'mail':
			$data = $this->__('E-mail Services');
			break;
			case 'manual':
			$data = $this->__('Manual Invitations');
			break;
			case 'file':
			$data = $this->__('Address Book Files');
			break;
		}
		
		if ($data)
			return $data;
	}
}
