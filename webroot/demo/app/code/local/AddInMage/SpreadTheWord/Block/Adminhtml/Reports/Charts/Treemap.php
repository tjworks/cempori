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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Charts_Treemap extends Mage_Adminhtml_Block_Template
{
	protected $_template = 'addinmage/spreadtheword/reports/visualization.phtml';
	protected $_chart_data;
	protected $_filter;

	public function getChartData()
	{
		$collection = $this->_chart_data;
		$chart_data = array ();
		
		$cols = array ();
		$cols [] = array ('id' => 'group', 'label' => $this->__('Service Name'), 'pattern' => null, 'type' => 'string');
		$cols [] = array ('id' => 'service', 'label' => $this->__('Group'), 'pattern' => null, 'type' => 'string');
		$cols [] = array ('id' => 'unique_access', 'label' => $this->__('Frequency of Use'), 'pattern' => null, 'type' => 'number');
		$cols [] = array ('id' => 'color', 'label' => $this->__('Frequency of Use increase/decrease (color)'), 'pattern' => null, 'type' => 'number');
		
		$rows = array ();
		$rows [] = array ('c' => array (array ('v' => 'Global', 'f' => $this->__('Service Popularity Tree Map %s', $this->getPeriod())), array ('v' => null, 'f' => null), array ('v' => null, 'f' => null), array ('v' => null, 'f' => null)));
		
		foreach ($collection as $service) {
			$c = array ();
			$c ['c'] [] = array ('v' => $service ['service'], 'f' => ucfirst($service ['service']));
			$c ['c'] [] = array ('v' => $this->__('Global'), 'f' => null);
			$c ['c'] [] = array ('v' => (int) $service ['frequency'], 'f' => null);
			$c ['c'] [] = array ('v' => (int) $service ['frequency'], 'f' => null);
			$rows [] = $c;
		}
		
		$chart_data ['cols'] = $cols;
		$chart_data ['rows'] = $rows;
		$chart_data = Zend_Json::encode($chart_data);
		return $chart_data;
	
	}

	public function getFixJsFlag()
	{
		return true;
	}

	public function getType()
	{
		return 'TreeMap';
	}

	public function canShowChart()
	{
		$this->_filter = $this->getData('filter');
		$this->_chart_data = $this->getData('chart_data');
		return ($this->_chart_data) ? true : false;
	
	}

	public function getOptions()
	{
		
		$options = array ('headerColor' => '#54748D', 'headerHeight' => 40, 'maxColor' => '#2B4952', 'midColor' => '#058DC7', 'minColor' => '#94C6D7', 'noColor' => '#fff', 'showTooltips' => true, 'fontSize' => 13, 'fontColor' => '#FFF', 'showScale' => true)

		;
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
		
		return $from . ' - ' . $to;
	}
}
