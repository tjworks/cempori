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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_General_Timeline_Grid extends AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Grid_Abstract
{
	protected $_columnGroupBy = 'time';
	protected $_dynamics_index;
	protected $_period_index = array ();

	public function __construct()
	{
		parent::__construct();
		$this->setCountTotals(true);
	}

	protected function _afterLoadCollection()
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $this;
		
		$filter = $this->getFilterData();
		
		if ($filter->getData('detailed_information', false)) {
			$chart_data = array ();
			$collection = $this->getCollection()
				->getItems();
			$show_empty = ($filter->getData('show_empty_rows', false)) ? true : false;
			foreach ($collection as $date => $p) {
				if (! $p->getIsEmpty()) {
					$data = array ('time' => ($show_empty) ? $p->getData('time') : $date, 'imported' => 0, 'title' => null, 'text' => null, 'invited' => 0, 'invite_link_used' => 0);
					
					$service_info = '<div class="adm-stw-timeline-event">';
					
					$add = ($p->getData()) ? $p->getData() : false;
					if ($add) {
						$data ['imported'] += $add ['imported'];
						$data ['invited'] += $add ['invited'];
						$data ['invite_link_used'] += $add ['invite_link_used'];
						$service_info .= '<div>' . ucfirst($add ['source']) . ' <span>' . $add ['imported'] . ' / ' . $add ['invited'] . ' / ' . $add ['invite_link_used'] . '<span>' . $this->__('Ratio') . '<span>' . (($add ['invite_link_used'] == 0) ? 0 : round($add ['invite_link_used'] / $add ['invited'], 1)) . '</span></span></span></div>';
					}
					
					foreach ($p->getChildren() as $service) {
						$data ['imported'] += $service ['imported'];
						$data ['invited'] += $service ['invited'];
						$data ['invite_link_used'] += $service ['invite_link_used'];
						$service_info .= '<div>' . ucfirst($service ['source']) . ' <span>' . $service ['imported'] . ' / ' . $service ['invited'] . ' / ' . $service ['invite_link_used'] . '<span>' . $this->__('Ratio') . '<span>' . (($service ['invite_link_used'] == 0) ? 0 : round($service ['invite_link_used'] / $service ['invited'], 1)) . '</span></span></span></div>';
					}
					
					$service_info .= '</div>';
					
					$data ['title'] = $p->getData('time') . ' ' . $this->__('Events');
					$data ['text'] = $service_info;
					$chart_data [] = $data;
				
				}
			}
			
			$this->_chart_data = ($chart_data && count($chart_data) > 1) ? $chart_data : false;
		
		} else {
			$data = $this->getCollection()
				->getItems();
			$chart_data = array ();
			foreach ($data as $info)
				if (! $info->getIsEmpty()) $chart_data [] = $info->toArray();
			$this->_chart_data = ($chart_data && count($chart_data) > 1) ? $chart_data : false;
		}
		return $this;
	}

	public function _afterToHtml($html)
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $html;
		
		$chart = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_charts_timeline', 'timeline.chart');
		$chart->setData(array ('chart_data' => $this->_chart_data, 'filter' => $this->getFilterData()
			->toArray()));
		$this->getLayout()
			->getBlock('spreadtheword.report.grid.container')
			->setChild('adm_stw.chart', $chart);
		return $html;
	}

	public function getResourceCollectionName()
	{
		return 'spreadtheword/reports_timeline_collection';
	}

	protected function _prepareColumns()
	{
		$filterData = $this->getFilterData();
		
		if (! $this->_dynamics_index) $this->_dynamics_index = ($filterData->getDetailedInformation()) ? 'source' : 'period';
		
		$this->addColumn('time', array ('header' => $this->__('Period'), 'index' => 'time', 'width' => 100, 'sortable' => false, 'period_type' => $this->getPeriodType(), 'renderer' => 'adminhtml/report_sales_grid_column_renderer_date', 'totals_label' => $this->__('Total'), 'html_decorators' => array ('nobr'), 'subtotals_label' => '')

		);
		
		if ($this->getFilterData()
			->getDetailedInformation()) {
			$this->addColumn('source', array ('header' => $this->__('Service'), 'index' => 'source', 'type' => 'srting', 'sortable' => false, 'subtotals_label' => $this->__('Subtotal'), 'frame_callback' => array ($this, 'decorateService')));
		}
		
		if ($this->getFilterData()
			->getStoreIds()) {
			$this->setStoreIds(explode(',', $this->getFilterData()
				->getStoreIds()));
		}
		
		if (! $this->_isExport && $filterData->getShowDynamics()) {
			$this->addColumn('imported_dynamic', array ('header' => $this->__('Trend'), 'type' => 'number', 'sortable' => false, 'width' => 10, 'subtotals_label' => false, 'frame_callback' => array ($this, 'decorateDynamic')));
		}
		
		$this->addColumn('imported', array ('header' => $this->__('Imported Friends'), 'index' => 'imported', 'type' => 'number', 'sortable' => false, 'total' => 'count'));
		
		if (! $this->_isExport && $filterData->getShowDynamics()) {
			$this->addColumn('invited_dynamic', array ('header' => $this->__('Trend'), 'type' => 'number', 'sortable' => false, 'width' => 10, 'subtotals_label' => false, 'frame_callback' => array ($this, 'decorateDynamic')));
		}
		
		$this->addColumn('invited', array ('header' => $this->__('Invited Friends'), 'index' => 'invited', 'type' => 'number', 'sortable' => false, 'total' => 'sum'));
		
		if (! $this->_isExport && $filterData->getShowDynamics()) {
			$this->addColumn('invite_link_used_dynamic', array ('header' => $this->__('Trend'), 'type' => 'string', 'sortable' => false, 'width' => 10, 'subtotals_label' => false, 'frame_callback' => array ($this, 'decorateDynamic')));
		}
		
		$this->addColumn('invite_link_used', array ('header' => $this->__('Responded Invitees'), 'index' => 'invite_link_used', 'type' => 'number', 'sortable' => false, 'total' => 'sum'));
		
		$this->addExportType('*/*/exportTimelineCsv', $this->__('CSV'));
		$this->addExportType('*/*/exportTimelineExcel', $this->__('Excel XML'));
		
		return parent::_prepareColumns();
	}

	public function decorateDynamic($value, $row, $column, $isExport)
	{
		$column_id = $column->getId();
		if ('imported_dynamic' == $column_id) $request = $row->getImported();
		else $request = ('invited_dynamic' == $column_id) ? $row->getInvited() : $row->getInviteLinkUsed();
		
		if ('period' == $this->_dynamics_index) {
			$index = $row->getTime();
			if (! in_array($index, $this->_period_index)) $this->_period_index [] = $index;
			
			$this->_dynamics [$index] [$column_id] = $request;
			
			$prev_key = sizeof($this->_period_index) - 2;
			if (isset($this->_period_index [$prev_key])) {
				if ($this->_dynamics [$this->_period_index [$prev_key]] [$column_id] < $request) $dynamics = 'adm-up';
				else $dynamics = ($this->_dynamics [$this->_period_index [$prev_key]] [$column_id] == $request) ? 'adm-eq' : 'adm-down';
				return '<span class="' . $dynamics . ' adm-dynamics"></span>';
			}
		} else {
			$index = $row->getSource();
			if (isset($this->_dynamics [$index] [$column_id])) {
				if ($this->_dynamics [$index] [$column_id] < $request) $dynamics = 'adm-up';
				elseif ($this->_dynamics [$index] [$column_id] == $request) $dynamics = 'adm-eq';
				else $dynamics = 'adm-down';
				
				$this->_dynamics [$index] [$column_id] = $request;
				return '<span class="' . $dynamics . ' adm-dynamics"></span>';
			} else
				$this->_dynamics [$index] [$column_id] = $request;
		}
	}

	public function getRowUrl($row)
	{
		return false;
	}

	public function decorateService($value, $row, $column, $isExport)
	{
		return ucfirst($value);
	}
}
