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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Charts_Performance extends Mage_Adminhtml_Block_Template
{
	protected $_template = 'addinmage/spreadtheword/reports/visualization.phtml';
	protected $_chart_data;
	protected $_filter;

	public function getChartData()
	{
		$collection = $this->_chart_data;
		
		$show_details = (isset($this->_filter ['detailed_information'])) ? true : false;
		$period = $this->_filter ['period_type'];
		$chart_data = array ();
		
		$cols = array ();
		$cols [] = array ('id' => 'date', 'label' => $this->__('Date'), 'type' => 'date');
		
		if ($show_details) {
			$cols [] = array ('id' => 'gross_profit', 'label' => $this->__('Gross Profit: '), 'type' => 'number');
			$cols [] = array ('id' => 'title', 'label' => 'title1', 'type' => 'string');
			$cols [] = array ('id' => 'text', 'label' => 'text1', 'type' => 'string');
			$cols [] = array ('id' => 'profit', 'label' => $this->__('Total STW Profit: '), 'type' => 'number');
		} else {
			$cols [] = array ('id' => 'profit', 'label' => $this->__('Total STW Profit: '), 'type' => 'number');
			$cols [] = array ('id' => 'title', 'label' => 'title1', 'type' => 'string');
			$cols [] = array ('id' => 'text', 'label' => 'text1', 'type' => 'string');
		}
		
		$rows = array ();
		foreach ($collection as $index => $data) {
			if (isset($data ['time']) && isset($data ['profit'])) {
				$c = array ();
				$c ['c'] [] = array ('v' => ('year' == $period) ? 'Date(' . ($data ['time']) . ',11,31)' : 'Date(' . (strtotime($data ['time']) * 1000) . ')', 'f' => null);
				
				if ($show_details) {
					$c ['c'] [] = array ('v' => $data ['gross_profit'], 'f' => null);
					$c ['c'] [] = array ('v' => $data ['title'], 'f' => null);
					$c ['c'] [] = array ('v' => $data ['text'], 'f' => null);
					$c ['c'] [] = array ('v' => $data ['profit'], 'f' => null);
				} else {
					$c ['c'] [] = array ('v' => $data ['profit'], 'f' => null);
					$c ['c'] [] = array ('v' => $data ['title'], 'f' => null);
					$c ['c'] [] = array ('v' => $data ['text'], 'f' => null);
				}
				
				$rows [] = $c;
			}
		}
		
		$chart_data ['cols'] = $cols;
		$chart_data ['rows'] = $rows;
		
		$chart_data = Zend_Json::encode($chart_data);
		return $chart_data;
	
	}

	public function getFixJsFlag()
	{
		return false;
	}

	public function getType()
	{
		return 'AnnotatedTimeLine';
	}

	public function canShowChart()
	{
		$this->_filter = $this->getData('filter');
		$this->_chart_data = $this->getData('chart_data');
		return ($this->_chart_data) ? true : false;
	
	}

	public function getOptions()
	{
		$period = $this->_filter ['period_type'];
		if ('month' == $period) $date_format = 'yyyy-MM';
		if ('year' == $period) $date_format = 'yyyy';
		if ('day' == $period) $date_format = 'yyyy-MM-dd';
		$options = array ('colors' => array ('#058DC7', '#67A54B', '#24CBE5'), 'displayAnnotations' => true, 'allowHtml' => true, 'annotationsWidth' => 15, 'fill' => 20, 'wmode' => 'opaque', 'thickness' => 1, 'displayExactValues' => true, 'allValuesSuffix' => ' ' . Mage::app()->getLocale()
			->currency(Mage::app()->getStore()
			->getCurrentCurrencyCode())
			->getSymbol(), 'dateFormat' => $date_format, 'displayZoomButtons' => ('day' == $period) ? true : false)

		;
		$options = Zend_Json::encode($options);
		return $options;
	
	}
}
