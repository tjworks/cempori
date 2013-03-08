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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Charts_Behavior extends Mage_Adminhtml_Block_Template
{
	protected $_template = 'addinmage/spreadtheword/reports/visualization.phtml';
	protected $_chart_data;
	protected $_filter;


	public function getChartData()
	{
		$collection = $this->_chart_data;
		$chart_data = array ();
		$cols = array ();
		
		$cols [] = array ('id' => null, 'label' => $this->__('Period'), 'type' => 'string');
		
		foreach ($collection ['periods'] as $name => $data)
			$cols [] = array ('id' => null, 'label' => $this->__('Details'), 'type' => 'number');
		
		$rows = array ();
		foreach ($collection ['data'] as $index => $data) {
			
			$c = array ();
			$c ['c'] [] = array ('v' => $this->getTip($data['period']), 'f' => null);
			
			foreach ($data as $row_data)
				$c ['c'] [] = array (
									'v' => ('less' == $row_data ['percentage']) ? (int) 1 / 100 : (int) $row_data ['percentage'] / 100, 
									'f' => ($row_data ['percentage'] == 0) ? $this->__('Less than %s or %s %s', '1%', $row_data ['people'], ($row_data ['people'] == 1) ? $this->__('person') : $this->__('people')) : $row_data ['percentage'] . '% ' . $this->__('or %d %s', $row_data ['people'], ($row_data ['people'] == 1) ? $this->__('person') : $this->__('people')));
			$rows [] = $c;			
		}
		
		$chart_data ['cols'] = $cols;
		$chart_data ['rows'] = $rows;
		return Zend_Json::encode($chart_data);
	}

	public function getFixJsFlag()
	{
		return true;
	}

	public function getType()
	{
		return 'BarChart';
	}

	public function canShowChart()
	{
		$this->_filter = $this->getData('filter');
		$this->_chart_data = $this->getData('chart_data');
		return ($this->_chart_data) ? true : false;
	
	}

	public function getTip($value)
	{
		$tip = '';
		if ($value == 'more' || $value <= 1) switch ($value) {
		case 'more':
		$tip = $this->__('In more than a month');
		break;
		case '0':
		$tip = $this->__('On the same day');
		break;
		case '1':
		$tip = $this->__('On the next day');
		break;
		}
		else $tip = $this->__('%s days later', $value);
		return $tip;
	}

	public function getOptions()
	{
		$options = array ('backgroundColor' => array ('stroke' => '#CDDDDD', 'strokeWidth' => 1, 'fill' => '#E7EFEF'), 'fontSize' => 13, 'isStacked' => true, 'title' => $this->__('How soon the invited people came to your shop %s?', $this->getPeriod()), 'tooltip' => array ('showColorCode' => true), 'legend' => array ('position' => 'none'), 'hAxis' => array ('gridlines' => array ('color' => '#CDDDDD', 'count' => 11), 'format' => '#,#%'));
		
		$options = Zend_Json::encode($options);
		return $options;
	
	}

	public function getPeriod()
	{
		list ($from_year, $from_month, $from_day) = explode('-', $this->filter ['from']);
		list ($to_year, $to_month, $to_day) = explode('-', $this->filter ['to']);
		
		$from_timestamp = mktime(0, 0, 0, $from_month, $from_day, $from_year);
		$to_timestamp = mktime(0, 0, 0, $to_month, $to_day, $to_year);
		
		$from_date = new Zend_Date($from_timestamp);
		$to_date = new Zend_Date($to_timestamp);
		
		$from = $from_date->toString(Mage::app()->getLocale()
			->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
		$to = $to_date->toString(Mage::app()->getLocale()
			->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
		
		return $this->__('from %s', $from) . ' ' . $this->__('to %s', $to);
	}
}
