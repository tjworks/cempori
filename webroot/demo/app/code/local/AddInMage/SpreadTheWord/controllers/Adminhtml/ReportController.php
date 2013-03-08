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


class AddInMage_SpreadTheWord_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action
{

	public function invitation_timelineAction()
	{
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Reports'))
			->_title($this->__('Invitation Timeline'));
		$this->loadLayout()
			->_setActiveMenu('addinmage/spreadtheword/reports/invitation_timeline')
			->_addBreadcrumb($this->__('Invitation Timeline'), $this->__('Invitation Timeline'));
		
		$gridBlock = $this->getLayout()
			->getBlock('adminhtml_reports_general_timeline.grid');
		$filterFormBlock = $this->getLayout()
			->getBlock('grid.filter.form');
		$this->_initReportAction(array ($gridBlock, $filterFormBlock));
		$this->renderLayout();
	}

	public function sales_performanceAction()
	{
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Reports'))
			->_title($this->__('Sales Performance'));
		$this->loadLayout()
			->_setActiveMenu('addinmage/spreadtheword/reports/sales_performance')
			->_addBreadcrumb($this->__('Sales Performance'), $this->__('Sales Performance'));
		
		$gridBlock = $this->getLayout()
			->getBlock('adminhtml_reports_general_performance.grid');
		$filterFormBlock = $this->getLayout()
			->getBlock('grid.filter.form');
		$this->_initReportAction(array ($gridBlock, $filterFormBlock));
		$this->renderLayout();
	}

	public function service_popularity_treemapAction()
	{
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Reports'))
			->_title($this->__('Service Popularity Tree Map'));
		$this->loadLayout()
			->_setActiveMenu('addinmage/spreadtheword/reports/service_treemap_popularity')
			->_addBreadcrumb($this->__('Service Popularity Tree Map'), $this->__('Service Popularity Tree Map'));
		$gridBlock = $this->getLayout()
			->getBlock('adminhtml_reports_service_treemap.grid');
		$filterFormBlock = $this->getLayout()
			->getBlock('grid.filter.form');
		$this->_initReportAction(array ($gridBlock, $filterFormBlock));
		$this->renderLayout();
	
	}

	public function visitors_statisticsAction()
	{
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Reports'))
			->_title($this->__('Statistics by Customer Type'));
		$this->loadLayout()
			->_setActiveMenu('addinmage/spreadtheword/reports/visitors_statistics')
			->_addBreadcrumb($this->__('Statistics by Customer Type'), $this->__('Statistics by Customer Type'));
		$gridBlock = $this->getLayout()
			->getBlock('adminhtml_reports_visitors_visitors.grid');
		$filterFormBlock = $this->getLayout()
			->getBlock('grid.filter.form');
		$this->_initReportAction(array ($gridBlock, $filterFormBlock));
		$this->renderLayout();
	
	}

	public function rule_ratingAction()
	{
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Reports'))
			->_title($this->__('Rule Rating'));
		$this->loadLayout()
			->_setActiveMenu('addinmage/spreadtheword/reports/rule_rating')
			->_addBreadcrumb($this->__('Rule Rating'), $this->__('Rule Rating'));
		$gridBlock = $this->getLayout()
			->getBlock('adminhtml_reports_rule_rating.grid');
		$filterFormBlock = $this->getLayout()
			->getBlock('grid.filter.form');
		$this->_initReportAction(array ($gridBlock, $filterFormBlock));
		$this->renderLayout();
	
	}

	public function involvementAction()
	{
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Reports'))
			->_title($this->__('Involvement'));
		$this->loadLayout()
			->_setActiveMenu('addinmage/spreadtheword/reports/involvement')
			->_addBreadcrumb($this->__('Involvement'), $this->__('Involvement'));
		$gridBlock = $this->getLayout()
			->getBlock('adminhtml_reports_involvement_behavior.grid');
		$filterFormBlock = $this->getLayout()
			->getBlock('grid.filter.form');
		$this->_initReportAction(array ($gridBlock, $filterFormBlock));
		$this->renderLayout();
	
	}

	public function _initReportAction($blocks)
	{
		if (! is_array($blocks)) {
			$blocks = array ($blocks);
		}
		
		$requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()
			->getParam('filter'));
		$requestData = $this->_filterDates($requestData, array ('from', 'to'));
		$requestData ['store_ids'] = $this->getRequest()
			->getParam('store_ids');
		
		$params = new Varien_Object();
		foreach ($requestData as $key => $value) {
			if (! empty($value)) {
				$params->setData($key, $value);
			}
		}
		foreach ($blocks as $block) {
			if ($block) {
				$block->setPeriodType($params->getData('period_type'));
				$block->setFilterData($params);
			}
		}
		return $this;
	}

	public function exportTimeLineCsvAction()
	{
		$fileName = 'stw_invitation_timeline_report.csv';
		$grid = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_general_timeline_grid');
		$this->_initReportAction($grid);
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}

	public function exportTimeLineExcelAction()
	{
		$fileName = 'stw_invitation_timeline_report.xml';
		$grid = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_general_timeline_grid');
		$this->_initReportAction($grid);
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

	public function exportPerformanceCsvAction()
	{
		$fileName = 'stw_sales_performance_report.csv';
		$grid = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_general_performance_grid');
		$this->_initReportAction($grid);
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}

	public function exportPerformanceExcelAction()
	{
		$fileName = 'stw_sales_performance_report.xml';
		$grid = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_general_performance_grid');
		$this->_initReportAction($grid);
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

	public function exportTreemapCsvAction()
	{
		$fileName = 'stw_service_popularity_tree_map_report.csv';
		$grid = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_service_treemap_grid');
		$this->_initReportAction($grid);
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}

	public function exportTreemapExcelAction()
	{
		$fileName = 'stw_service_popularity_tree_map_report.xml';
		$grid = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_service_treemap_grid');
		$this->_initReportAction($grid);
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

	public function exportVisitorsCsvAction()
	{
		$fileName = 'stw_visitors_statistics_report.xml.csv';
		$grid = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_visitors_visitors_grid');
		$this->_initReportAction($grid);
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}

	public function exportVisitorsExcelAction()
	{
		$fileName = 'stw_visitors_statistics_report.xml';
		$grid = $this->getLayout()
			->createBlock('spreadtheword/adminhtml_reports_visitors_visitors_grid');
		$this->_initReportAction($grid);
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

	protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream', $contentLength = null)
	{
		
		$session = Mage::getSingleton('admin/session');
		if ($session->isFirstPageAfterLogin()) {
			$this->_redirect($session->getUser()
				->getStartupPageUrl());
			return $this;
		}
		
		$isFile = false;
		$file = null;
		if (is_array($content)) {
			if (! isset($content ['type']) || ! isset($content ['value'])) {return $this;}
			if ($content ['type'] == 'filename') {
				$isFile = true;
				$file = $content ['value'];
				$contentLength = filesize($file);
			}
		}
		
		$this->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Pragma', 'public', true)
			->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
			->setHeader('Content-type', $contentType, true)
			->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength)
			->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
			->setHeader('Last-Modified', date('r'));
		
		if (! is_null($content)) {
			if ($isFile) {
				$this->getResponse()
					->clearBody();
				$this->getResponse()
					->sendHeaders();
				
				$ioAdapter = new Varien_Io_File();
				$ioAdapter->open(array ('path' => $ioAdapter->dirname($file)));
				$ioAdapter->streamOpen($file, 'r');
				while($buffer = $ioAdapter->streamRead()) {
					print $buffer;
				}
				$ioAdapter->streamClose();
				if (! empty($content ['rm'])) {
					$ioAdapter->rm($file);
				}
			} else {
				$this->getResponse()
					->setBody($content);
			}
		}
		die();
	}
}
