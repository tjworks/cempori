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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_General_Performance_Grid extends AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Grid_Abstract
{
	protected $_columnGroupBy = 'time';
	protected $_period_index = array ();
	protected $_is_sales_report = true;
	protected $_is_sub_total = false;

	public function __construct()
	{
		parent::__construct();
		$this->setCountTotals(true);
		$this->setCountSubTotals(true);
	}

	protected function _afterLoadCollection()
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $this;
		
		$chart_data = array ();
		$filter = $this->getFilterData();
		$format_helper = Mage::helper('core');
		$collection = $this->getCollection()
			->getItems();
		$show_empty = ($filter->getData('show_empty_rows', false)) ? true : false;
		$sum_up_data = ($filter->getSumUpDataPerPeriod()) ? true : false;
		
		if ($filter->getData('detailed_information', false)) {
			
			foreach ($collection as $date => $p) {
				
				if (! $p->getIsEmpty()) {
					$data = array ('time' => ($show_empty) ? $p->getData('time') : $date, 'gross_profit' => 0, 'title' => null, 'text' => null, 'profit' => 0);
					
					$orders_info = '<div class="adm-stw-timeline-event">';
					
					$gross_profit_per_period = 0;
					$total_profit = 0;
					
					$gross_orders_count = 0;
					$orders_count = 0;
					
					$gross_total_qty_ordered = 0;
					$total_qty_ordered = 0;
					
					$orders = '';
					$szr_order_set = false;
					
					$add = ($p->getData()) ? $p->getData() : false;
					if ($add) {
						
						$data ['gross_profit'] += $add ['gross_profit_per_period'];
						$data ['profit'] += $add ['total_profit_amount'];
						
						if ($sum_up_data) {
							$sum = '';
							$sum .= '<div class="adm-stw-event-data">' . $this->__('Gross Profit') . '<span class="adm-stw-event-data">' . $format_helper->currency($add ['gross_profit_per_period'], true, false) . '<span><span></span></span></span><br/>';
							$sum .= $this->__('Total STW Profit') . '<span class="adm-stw-event-data">' . $format_helper->currency($add ['total_profit_amount'], true, false) . '<span><span></span></span></span><br/>';
							$sum .= $this->__('STW Profit Share') . '<span><span><span>' . round(($add ['total_profit_amount'] / $add ['gross_profit_per_period']) * 100) . '%</span></span></span></div>';
							$sum .= '<div class="adm-stw-event-data">' . $this->__('Gross Orders') . '<span class="adm-stw-event-data">' . (int) $add ['gross_orders_count'] . '<span><span></span></span></span><br/>';
							$sum .= $this->__('Total Orders via STW') . '<span class="adm-stw-event-data">' . (int) $add ['orders_count'] . '<span><span></span></span></span><br/>';
							$sum .= $this->__('SWT Order Share') . '<span><span><span>' . round(($add ['orders_count'] / $add ['gross_orders_count']) * 100) . '%</span></span></span></div>';
							$sum .= '<div class="adm-stw-event-data adm-stw-event-last">' . $this->__('Gross Sales Items') . '<span class="adm-stw-event-data">' . (int) $add ['gross_total_qty_ordered'] . '<span><span></span></span></span><br/>';
							$sum .= $this->__('STW Sales Items') . '<span class="adm-stw-event-data">' . (int) $add ['total_qty_ordered'] . '<span><span></span></span></span><br/>';
							$sum .= $this->__('STW Sales Items Share') . '<span><span><span>' . round(($add ['total_qty_ordered'] / $add ['gross_total_qty_ordered']) * 100) . '%</span></span></span>';
							$orders_info .= $sum;
						} else {
							$gross_profit_per_period += $add ['gross_profit_per_period'];
							$total_profit += $add ['total_profit_amount'];
							$gross_orders_count += $add ['gross_orders_count'];
							$orders_count += $add ['orders_count'];
							$gross_total_qty_ordered += $add ['gross_total_qty_ordered'];
							$total_qty_ordered += $add ['total_qty_ordered'];
							
							if (isset($add ['stw_order_id'])) {
								$szr_order_set = true;
								$details = '<div class="adm-stw-event-data">';
								$details .= $this->__('Order #') . '<span><span><span>' . $add ['order_id'] . '</span></span></span><br/>';
								$details .= $this->__('Order Status') . '<span class="adm-stw-event-data">' . ucfirst($add ['order_status']) . '<span><span></span></span></span><br/>';
								$details .= $this->__('Placed By') . '<span class="adm-stw-event-data">' . ucfirst($add ['user_type']) . '<span><span></span></span></span><br/>';
								$details .= $this->__('Service') . '<span class="adm-stw-event-data">' . ucfirst($add ['source']) . '<span><span></span></span></span><br/>';
								$details .= $this->__('Sales Items') . '<span class="adm-stw-event-data">' . (int) $add ['total_qty_ordered'] . '<span><span></span></span></span><br/>';
								$details .= $this->__('Order Profit') . '<span><span>' . $format_helper->currency($add ['total_profit_amount'], true, false) . '</span></span><br/>';
								$orders .= $details . '</div>';
							}
						}
					}
					foreach ($p->getChildren() as $order) {
						$data ['gross_profit'] += $order ['gross_profit_per_period'];
						$data ['profit'] += $order ['total_profit_amount'];
						
						if (! $sum_up_data) {
							$gross_profit_per_period += $order ['gross_profit_per_period'];
							$total_profit += $order ['total_profit_amount'];
							$gross_orders_count += $order ['gross_orders_count'];
							$orders_count += $order ['orders_count'];
							$gross_total_qty_ordered += $order ['gross_total_qty_ordered'];
							$total_qty_ordered += $order ['total_qty_ordered'];
							
							if (isset($order ['stw_order_id'])) {
								$szr_order_set = true;
								$details = '<div class="adm-stw-event-data">';
								$details .= $this->__('Order #') . '<span><span><span>' . $order ['order_id'] . '</span></span></span><br/>';
								$details .= $this->__('Order Status') . '<span class="adm-stw-event-data">' . ucfirst($order ['order_status']) . '<span><span></span></span></span><br/>';
								$details .= $this->__('Placed By') . '<span class="adm-stw-event-data">' . ucfirst($order ['user_type']) . '<span><span></span></span></span><br/>';
								$details .= $this->__('Service') . '<span class="adm-stw-event-data">' . ucfirst($order ['source']) . '<span><span></span></span></span><br/>';
								$details .= $this->__('Sales Items') . '<span class="adm-stw-event-data">' . (int) $order ['total_qty_ordered'] . '<span><span></span></span></span><br/>';
								$details .= $this->__('Order Profit') . '<span><span>' . $format_helper->currency($order ['total_profit_amount'], true, false) . '</span></span><br/>';
								$orders .= $details . '</div>';
							}
						
						}
					}
					
					if (! $sum_up_data) {
						$sum = '';
						$sum .= '<div class="adm-stw-event-data">' . $this->__('Gross Profit') . '<span class="adm-stw-event-data">' . $format_helper->currency($gross_profit_per_period, true, false) . '<span><span></span></span></span><br/>';
						$sum .= $this->__('Total STW Profit') . '<span class="adm-stw-event-data">' . $format_helper->currency($total_profit, true, false) . '<span><span></span></span></span><br/>';
						$sum .= $this->__('STW Profit Share') . '<span><span><span>' . round(($total_profit / $gross_profit_per_period) * 100) . '%</span></span></span></div>';
						$sum .= '<div class="adm-stw-event-data">' . $this->__('Gross Orders') . '<span class="adm-stw-event-data">' . (int) $gross_orders_count . '<span><span></span></span></span><br/>';
						$sum .= $this->__('Total Orders via STW') . '<span class="adm-stw-event-data">' . (int) $orders_count . '<span><span></span></span></span><br/>';
						$sum .= $this->__('SWT Order Share') . '<span><span><span>' . round(($orders_count / $gross_orders_count) * 100) . '%</span></span></span></div>';
						$sum .= '<div class="adm-stw-event-data adm-stw-event-last">' . $this->__('Gross Sales Items') . '<span class="adm-stw-event-data">' . (int) $gross_total_qty_ordered . '<span><span></span></span></span><br/>';
						$sum .= $this->__('STW Sales Items') . '<span class="adm-stw-event-data">' . (int) $total_qty_ordered . '<span><span></span></span></span><br/>';
						$sum .= $this->__('STW Sales Items Share') . '<span><span><span>' . round(($total_qty_ordered / $gross_total_qty_ordered) * 100) . '%</span></span></span>';
						
						if ($szr_order_set) $sum .= '<div></div><div style="color:black">' . $this->__('Details') . '</div>';
						$orders_info .= $sum;
						$orders_info .= $orders;
					
					}
					
					$orders_info .= '</div>';
					
					$data ['title'] = $p->getData('time') . ' ' . $this->__('Events');
					$data ['text'] = $orders_info;
					$chart_data [] = $data;
				}
			}
		} else {
			foreach ($collection as $date => $p) {
				if (! $p->getIsEmpty()) {
					
					$data = array ('time' => ($show_empty) ? $p->getData('time') : $date, 'profit' => 0, 'title' => null, 'text' => null);
					
					$orders_info = '<div class="adm-stw-timeline-event">';
					
					$add = ($p->getData()) ? $p->getData() : false;
					if ($add) {
						$data ['profit'] += $add ['total_profit_amount'];
						$order_info = '<div class="adm-stw-event-data">';
						if ($sum_up_data) {
							$order_info .= $this->__('Orders') . '<span class="adm-stw-event-data">' . (int) $add ['orders_count'] . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Sales Items') . '<span class="adm-stw-event-data">' . (int) $add ['total_qty_ordered'] . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Total Profit') . '<span><span>' . $format_helper->currency($add ['total_profit_amount'], true, false) . '</span></span><br/>';
						} else {
							$order_info .= $this->__('Order #') . '<span><span><span>' . $add ['order_id'] . '</span></span></span><br/>';
							$order_info .= $this->__('Order Status') . '<span class="adm-stw-event-data">' . ucfirst($add ['order_status']) . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Placed By') . '<span class="adm-stw-event-data">' . ucfirst($add ['user_type']) . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Service') . '<span class="adm-stw-event-data">' . ucfirst($add ['source']) . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Sales Items') . '<span class="adm-stw-event-data">' . (int) $add ['total_qty_ordered'] . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Order Profit') . '<span><span>' . $format_helper->currency($add ['total_profit_amount'], true, false) . '</span></span><br/>';
						}
						$order_info .= '</div>';
						$orders_info .= $order_info;
					}
					
					foreach ($p->getChildren() as $order) {
						$data ['profit'] += $order ['total_profit_amount'];
						
						$order_info = '<div class="adm-stw-event-data">';
						if ($sum_up_data) {
							$order_info .= $this->__('Orders') . '<span class="adm-stw-event-data">' . (int) $order ['orders_count'] . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Sales Items') . '<span class="adm-stw-event-data">' . (int) $order ['total_qty_ordered'] . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Total Profit') . '<span><span>' . $format_helper->currency($order ['total_profit_amount'], true, false) . '</span></span><br/>';
						} else {
							$order_info .= $this->__('Order #') . '<span><span><span>' . $order ['order_id'] . '</span></span></span><br/>';
							$order_info .= $this->__('Order Status') . '<span class="adm-stw-event-data">' . ucfirst($order ['order_status']) . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Placed By') . '<span class="adm-stw-event-data">' . ucfirst($order ['user_type']) . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Service') . '<span class="adm-stw-event-data">' . ucfirst($order ['source']) . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Sales Items') . '<span class="adm-stw-event-data">' . (int) $order ['total_qty_ordered'] . '<span><span></span></span></span><br/>';
							$order_info .= $this->__('Order Profit') . '<span><span>' . $format_helper->currency($order ['total_profit_amount'], true, false) . '</span></span><br/>';
						}
						$order_info .= '</div>';
						$orders_info .= $order_info;
					}
					
					$orders_info .= '</div>';
					
					$data ['title'] = $p->getData('time') . ' ' . $this->__('Events');
					$data ['text'] = $orders_info;
					$chart_data [] = $data;
				}
			}
		}
		$this->_chart_data = ($chart_data && count($chart_data) > 1) ? $chart_data : false;
		return $this;
	}

	public function _afterToHtml($html)
	{
		if (! Mage::getStoreConfig('addinmage_spreadtheword/general/visualization', Mage::app()->getStore())) return $html;
		
		$chart = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_charts_performance', 'performance.chart');
		$chart->setData(array ('chart_data' => $this->_chart_data, 'filter' => $this->getFilterData()
			->toArray()));
		$this->getLayout()
			->getBlock('spreadtheword.report.grid.container')
			->setChild('adm_stw.chart', $chart);
		
		return $html;
	}

	public function getResourceCollectionName()
	{
		return 'spreadtheword/reports_performance_collection';
	}

	protected function _prepareColumns()
	{
		$filterData = $this->getFilterData();
		
		$this->addColumn('time', array ('header' => $this->__('Period'), 'index' => 'time', 'width' => 100, 'sortable' => false, 'period_type' => $this->getPeriodType(), 'renderer' => 'adminhtml/report_sales_grid_column_renderer_date', 'totals_label' => $this->__('Total'), 'html_decorators' => array ('nobr'), 'subtotals_label' => $this->__('Subtotal'))

		);
		
		if ($this->getFilterData()
			->getStoreIds()) {
			$this->setStoreIds(explode(',', $this->getFilterData()
				->getStoreIds()));
		}
		
		$currencyCode = $this->getCurrentCurrencyCode();
		$compatibility = (! $filterData->getServices() && ! $filterData->getOrderPlacedBy() && $filterData->getDetailedInformation()) ? true : false;
		$can_show_dynamics = (! $this->_isExport && $filterData->getShowDynamics() && $filterData->getSumUpDataPerPeriod() && ! $filterData->getDetailedInformation()) ? true : false;
		
		if ($compatibility) $this->addColumn('gross_profit_per_period', array ('header' => $this->__('Gross Profit'), 'index' => 'gross_profit_per_period', 'column_css_class' => 'adm-stw-report-order-profit', 'type' => 'currency', 'currency_code' => $currencyCode, 'sortable' => false, 'total' => 'sum', 'visibility_filter' => array ('detailed_information')));
		
		if ($compatibility) $this->addColumn('services_profit_share', array ('header' => $this->__('STW Profit Share'), 'index' => 'services_profit_share', 'type' => 'number', 'column_css_class' => 'adm-stw-report-order-profit', 'sortable' => false, 'visibility_filter' => array ('detailed_information'), 'frame_callback' => array ($this, 'decorateShare')));
		
		if (! $filterData->getSumUpDataPerPeriod()) {
			$this->addColumn('user_type', array ('header' => $this->__('Placed By'), 'index' => 'user_type', 'type' => 'string', 'sortable' => false, 'frame_callback' => array ($this, 'ucfirstValue')));
			
			$this->addColumn('source', array ('header' => $this->__('Service'), 'index' => 'source', 'type' => 'string', 'sortable' => false, 'frame_callback' => array ($this, 'ucfirstValue')));
			
			$this->addColumn('order_id', array ('header' => $this->__('Order ID'), 'index' => 'order_id', 'type' => 'string', 'sortable' => false, 'frame_callback' => array ($this, 'getOrderUrl')));
			
			$this->addColumn('order_status', array ('header' => $this->__('Order Status'), 'index' => 'order_status', 'type' => 'string', 'sortable' => false));
		} else {
			
			if ($can_show_dynamics) {
				$this->addColumn('orders_dynamic', array ('header' => $this->__('Order Trend'), 'type' => 'number', 'sortable' => false, 'width' => 10, 'subtotals_label' => false, 'frame_callback' => array ($this, 'decorateDynamic')));
			}
			
			$this->addColumn('orders_count', array ('header' => Mage::helper('sales')->__('Orders'), 'index' => 'orders_count', 'type' => 'number', 'column_css_class' => 'adm-stw-report-orders-count', 'total' => 'sum', 'sortable' => false, 'frame_callback' => array ($this, 'hideNullValues')));
			
			if ($compatibility) $this->addColumn('services_orders_count_share', array ('header' => $this->__('SWT Order Share'), 'index' => 'services_orders_count_share', 'type' => 'number', 'column_css_class' => 'adm-stw-report-orders-count', 'sortable' => false, 'visibility_filter' => array ('detailed_information'), 'frame_callback' => array ($this, 'decorateShare')));
			
			if ($compatibility) $this->addColumn('gross_orders_count', array ('header' => Mage::helper('sales')->__('Gross Orders'), 'index' => 'gross_orders_count', 'type' => 'number', 'column_css_class' => 'adm-stw-report-orders-count', 'total' => 'sum', 'sortable' => false, 'visibility_filter' => array ('detailed_information')));
		}
		
		$this->addColumn('total_qty_ordered', array ('header' => Mage::helper('sales')->__('Sales Items'), 'index' => 'total_qty_ordered', 'type' => 'number', 'column_css_class' => 'adm-stw-report-order-qty-ordered', 'total' => 'sum', 'sortable' => false, 'frame_callback' => array ($this, 'hideNullValues')));
		
		if ($compatibility) $this->addColumn('services_qty_ordered_share', array ('header' => Mage::helper('sales')->__('STW Sales Items Share'), 'index' => 'services_qty_ordered_share', 'type' => 'number', 'column_css_class' => 'adm-stw-report-order-qty-ordered', 'sortable' => false, 'visibility_filter' => array ('detailed_information'), 'frame_callback' => array ($this, 'decorateShare')));
		
		if ($compatibility) $this->addColumn('gross_total_qty_ordered', array ('header' => Mage::helper('sales')->__('Gross Sales Items'), 'index' => 'gross_total_qty_ordered', 'type' => 'number', 'column_css_class' => 'adm-stw-report-order-qty-ordered', 'total' => 'sum', 'sortable' => false, 'visibility_filter' => array ('detailed_information')));
		
		if ($can_show_dynamics) {
			$this->addColumn('profit_dynamic', array ('header' => $this->__('Profit Trend'), 'type' => 'number', 'sortable' => false, 'width' => 10, 'subtotals_label' => false, 'frame_callback' => array ($this, 'decorateDynamic')));
		}
		
		$this->addColumn('total_profit_amount', array ('header' => $this->__('Profit'), 'sortable' => false, 'type' => 'currency', 'column_css_class' => 'adm-stw-report-order-profit', 'currency_code' => $currencyCode, 'total' => 'sum', 'index' => 'total_profit_amount', 'frame_callback' => array ($this, 'hideNullValues')));
		
		if ($compatibility) {
			$this->addColumnsOrder('services_profit_share', 'total_profit_amount');
			$this->addColumnsOrder('gross_profit_per_period', 'services_profit_share');
		
		}
		
		$this->addExportType('*/*/exportPerformanceCsv', $this->__('CSV'));
		$this->addExportType('*/*/exportPerformanceExcel', $this->__('Excel XML'));
		
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return false;
	}

	public function decorateDynamic($value, $row, $column, $isExport)
	{
		$column_id = $column->getId();
		$request = ('profit_dynamic' == $column_id) ? $row->getTotalProfitAmount() : $row->getOrdersCount();
		
		$index = $row->getTime();
		
		if (! in_array($index, $this->_period_index)) $this->_period_index [] = $index;
		
		$this->_dynamics [$index] [$column_id] = $request;
		
		$prev_key = sizeof($this->_period_index) - 2;
		if (isset($this->_period_index [$prev_key])) {
			if ($this->_dynamics [$this->_period_index [$prev_key]] [$column_id] < $request) $dynamics = 'adm-up';
			else $dynamics = ($this->_dynamics [$this->_period_index [$prev_key]] [$column_id] == $request) ? 'adm-eq' : 'adm-down';
			return '<span class="' . $dynamics . ' adm-dynamics"></span>';
		}
	}

	public function getOrderUrl($value, $row, $column, $isExport)
	{
		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view') && ! $isExport) return "<a target='_blank' href='{$this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getRealOrderId()))}'>{$row->getOrderId()}</a>";
		return $row->getOrderId();
	}

	public function ucfirstValue($value, $row, $column, $isExport)
	{
		$value = ucfirst($value);
		if (! $isExport && ($row->getSource() || $row->getUserType())) return "<div class='adm-stw-report-order'>{$value}</div>";
		return $value;
	}

	public function decorateShare($value, $row, $column, $isExport)
	{
		$data = false;
		
		switch ($column->getId()) {
		case 'services_profit_share':
		$row_data = $row->getTotalProfitAmount();
		$row_gross_data = $row->getGrossProfitPerPeriod();
		break;
		case 'services_qty_ordered_share':
		$row_data = $row->getTotalQtyOrdered();
		$row_gross_data = $row->getGrossTotalQtyOrdered();
		break;
		case 'services_orders_count_share':
		$row_data = $row->getOrdersCount();
		$row_gross_data = $row->getGrossOrdersCount();
		break;
		}
		if ((! $row->getOrderId() && $row_data) || ($this->getFilterData()
			->getDetailedInformation() && $this->getFilterData()
			->getSumUpDataPerPeriod())) {
			$data = round(($row_data / $row_gross_data) * 100);
		}
		
		if ($data) return $data . ' %';
	}

	public function hideNullValues($value, $row, $column, $isExport)
	{
		if ($this->getFilterData()
			->getDetailedInformation()) {
			switch ($column->getId()) {
			case 'total_profit_amount':
			$value = ((int) $row->getTotalProfitAmount()) ? $value : null;
			break;
			case 'total_qty_ordered':
			$value = ((int) $row->getTotalQtyOrdered()) ? $value : null;
			break;
			case 'orders_count':
			$value = ((int) $row->getOrdersCount()) ? $value : null;
			break;
			}
		}
		return $value;
	}
}
