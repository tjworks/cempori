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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Visitors_Visitors_Grid extends AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Grid_Abstract
{
	protected $_columnGroupBy = 'source';

	public function __construct()
	{
		$requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
		
		if (isset($requestData ['group_by'])) {
			if ('period' == $requestData ['group_by']) $this->_columnGroupBy = 'time';
			else $this->_columnGroupBy = ('service' == $requestData ['group_by']) ? 'source' : 'user_type';
		}
		parent::__construct();
		$this->setCountTotals(true);
		$this->setCountSubTotals(true);
	}

	protected function _afterLoadCollection()
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) 
			return $this;
		
		$filter = $this->getFilterData();
		$chart_data = array ();
		$collection = $this->getCollection()->getItems();
		$needle = ('visitor_type' == $filter->getGroupBy()) ? $needle = 'source' : 'user_type';
		$context = array ();
		$data_count = 0;
		
		if ($filter->getVisualizationBasePoint()) {
			if ('imported' == $filter->getVisualizationBasePoint()) $v_point = 'imported';
			else $v_point = ('invited' == $filter->getVisualizationBasePoint()) ? 'invited' : 'invite_link_used';
		} else
			$v_point = 'imported';
		
		foreach ($collection as $group => $p) {
			$add = ($p->getData()) ? $p->getData() : false;
			if ($add) $context [$add [$needle]] = null;
			foreach ($p->getChildren() as $service)
				$context [$service->getData($needle)] = null;
		}
		foreach ($collection as $group => $p) {
			$chart_data [$group] = $context;
			foreach ($chart_data [$group] as $service => $data) {
				$add = ($p->getData()) ? $p->getData() : false;
				if ($add) {
					$data_count ++;
					if ($service == $add [$needle]) $chart_data [$group] [$service] = $add [$v_point];
				}
				foreach ($p->getChildren() as $data) {
					$data_count ++;
					if ($service == $data->getData($needle)) $chart_data [$group] [$service] = $data->getData($v_point);
				}
			}
		}
		$chart_data ['v_point'] = $v_point;
		$chart_data ['context'] = array_keys($context);
		
		$this->_chart_data = ($chart_data && $data_count > 1) ? $chart_data : false;
		return $this;
	}

	public function _afterToHtml($html)
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) 
			return $html;
		
		$chart = $this->getLayout()->createBlock('spreadtheword/adminhtml_reports_charts_visitors', 'visitors.chart');
		$chart->setData(array ('chart_data' => $this->_chart_data, 'filter' => $this->getFilterData()->toArray()));
		$this->getLayout()->getBlock('spreadtheword.report.grid.container')->setChild('adm_stw.chart', $chart);
		
		return $html;
	}

	public function getResourceCollectionName()
	{
		return 'spreadtheword/reports_visitors_collection';
	}

	protected function _prepareColumns()
	{
		$filterData = $this->getFilterData();
		
		if ('time' == $this->_columnGroupBy) {
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
		} else {
			$this->addColumn('source', array (
					'header' => $this->__('Service Name'), 
					'index' => 'source', 
					'type' => 'string', 
					'sortable' => false, 
					'frame_callback' => array ($this, 'decorateService'), 
					'subtotals_label' => false));
		}
		
		if ($this->getFilterData()->getStoreIds()) {
			$this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
		}
		
		$this->addColumn('user_type', array (
				'header' => $this->__('Customer Type'), 
				'index' => 'user_type', 
				'type' => 'string', 
				'sortable' => false, 
				'width' => 100, 
				'frame_callback' => array ($this, 'decorateVisitorType'), 
				'subtotals_label' => $this->__('Subtotal')));
		
		if (! $this->_isExport && $filterData->getShowDynamics() && 'time' == $this->_columnGroupBy) {
			$this->addColumn('imported_dynamic', array (
					'header' => $this->__('Trend'), 
					'type' => 'number', 
					'sortable' => false, 
					'width' => 10, 
					'subtotals_label' => false, 
					'frame_callback' => array ($this, 'decorateDynamic')));
		}
		
		$this->addColumn('imported', array (
				'header' => $this->__('Imported Friends'), 
				'index' => 'imported', 
				'type' => 'number', 
				'sortable' => false, 
				'total' => 'count'));
		
		if (! $this->_isExport && $filterData->getShowDynamics() && 'time' == $this->_columnGroupBy) {
			$this->addColumn('invited_dynamic', array (
					'header' => $this->__('Trend'), 
					'type' => 'number', 
					'sortable' => false, 
					'width' => 10, 
					'subtotals_label' => false, 
					'frame_callback' => array ($this, 'decorateDynamic')));
		}
		
		$this->addColumn('invited', array (
				'header' => $this->__('Invited Friends'), 
				'index' => 'invited', 
				'type' => 'number', 
				'sortable' => false, 
				'total' => 'sum'));
		
		if (! $this->_isExport && $filterData->getShowDynamics() && 'time' == $this->_columnGroupBy) {
			$this->addColumn('invite_link_used_dynamic', array (
					'header' => $this->__('Trend'), 
					'type' => 'string', 
					'sortable' => false, 
					'width' => 10, 
					'subtotals_label' => false, 
					'frame_callback' => array ($this, 'decorateDynamic')));
		}
		
		$this->addColumn('invite_link_used', array (
				'header' => $this->__('Responded Invitees'), 
				'index' => 'invite_link_used', 
				'type' => 'number', 
				'sortable' => false, 
				'total' => 'sum'));
		
		if ('user_type' == $this->_columnGroupBy) {
			$this->getColumn('user_type')->addData(array ('totals_label' => $this->__('Total'), 'subtotals_label' => false));
			$this->getColumn('source')->addData(array ('subtotals_label' => $this->__('Subtotal')));
			$this->_columnsOrder = array ('user_type');
		}
		
		if ('source' == $this->_columnGroupBy) $this->getColumn('source')
			->addData(array ('totals_label' => $this->__('Total')));
		
		$this->addExportType('*/*/exportVisitorsCsv', $this->__('CSV'));
		$this->addExportType('*/*/exportVisitorsExcel', $this->__('Excel XML'));
		
		return parent::_prepareColumns();
	}

	public function decorateDynamic($value, $row, $column, $isExport)
	{
		$column_id = $column->getId();
		if ('imported_dynamic' == $column_id) $request = $row->getImported();
		else $request = ('invited_dynamic' == $column_id) ? $row->getInvited() : $row->getInviteLinkUsed();
		
		$index = $row->getUserType();
		if (isset($this->_dynamics [$index] [$column_id])) {
			if ($this->_dynamics [$index] [$column_id] < $request) $dynamics = 'adm-up';
			elseif ($this->_dynamics [$index] [$column_id] == $request) $dynamics = 'adm-eq';
			else $dynamics = 'adm-down';
			
			$this->_dynamics [$index] [$column_id] = $request;
			return '<span class="' . $dynamics . ' adm-dynamics"></span>';
		} else
			$this->_dynamics [$index] [$column_id] = $request;
	
	}

	public function getRowUrl($row)
	{
		return false;
	}

	public function decorateService($value, $row, $column, $isExport)
	{
		return ucfirst($value);
	}

	public function decorateVisitorType($value, $row, $column, $isExport)
	{
		switch ($row->getUserType()) {
		case 'customer':
		$value = $this->__('Customer');
		break;
		case 'visitor':
		$value = $this->__('Visitor');
		break;
		}
		return $value;
	}
}
