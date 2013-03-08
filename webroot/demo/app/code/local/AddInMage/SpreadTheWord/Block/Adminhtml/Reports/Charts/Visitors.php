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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Charts_Visitors extends Mage_Adminhtml_Block_Template
{
	protected $_template = 'addinmage/spreadtheword/reports/visualization.phtml';
	protected $_chart_data;
	protected $_filter;
	protected $_v_point;

	public function getChartData()
	{
		$collection = $this->_chart_data;
		$chart_data = array ();
		$cols = array ();
		
		if ('visitor_type' == $this->_filter ['group_by']) {
			$cols [] = array ('id' => 'group', 'label' => $this->__('Group'), 'type' => 'string');
			foreach ($collection ['context'] as $service)
				$cols [] = array ('id' => $service, 'label' => ucfirst($service), 'type' => 'number');
		} elseif ('service' == $this->_filter ['group_by']) {
			$cols [] = array ('id' => 'service', 'label' => $this->__('Service Name'), 'type' => 'string');
			foreach ($collection ['context'] as $group)
				$cols [] = array ('id' => $group, 'label' => ('visitor' == $group) ? $this->__('Visitors') : $this->__('Customers'), 'type' => 'number');
		} else {
			$v_point = $collection ['v_point'];
			$period = $this->_filter ['period_type'];
			$cols [] = array ('id' => 'date', 'label' => $this->__('Date'), 'type' => 'date');
			foreach ($collection ['context'] as $group)
				$cols [] = array ('id' => $group, 'label' => $this->getLabel($group, $v_point), 'type' => 'number');
		}
		
		unset($collection ['v_point']);
		unset($collection ['context']);
		
		$rows = array ();
		foreach ($collection as $group => $data) {
			$c = array ();
			if ('visitor_type' == $this->_filter ['group_by']) {
				$c ['c'] [] = array ('v' => ('visitor' == $group) ? $this->__('Visitors') : $this->__('Customers'), 'f' => null);
				foreach ($data as $row_data)
					$c ['c'] [] = array ('v' => (int) $row_data, 'f' => null);
			} elseif ('service' == $this->_filter ['group_by']) {
				$c ['c'] [] = array ('v' => ucfirst($group), 'f' => null);
				foreach ($data as $row_data)
					$c ['c'] [] = array ('v' => (int) $row_data, 'f' => null);
			} else {
				$c ['c'] [] = array ('v' => ('year' == $period) ? 'Date(' . ($group) . ',11,31)' : 'Date(' . (strtotime($group) * 1000) . ')', 'f' => null);
				foreach ($data as $row_data)
					$c ['c'] [] = array ('v' => (int) $row_data, 'f' => null);
			}
			$rows [] = $c;
		}
		
		$chart_data ['cols'] = $cols;
		$chart_data ['rows'] = $rows;
		return Zend_Json::encode($chart_data);
	}

	public function getFixJsFlag()
	{
		if ('period' == $this->_filter ['group_by']) return false;
		return true;
	}

	protected function getLabel($group, $v_point)
	{
		if ('imported' == $v_point) $title = ('customer' == $group) ? $this->__('Number of Friends Imported by the Customers:') : $this->__('Number of Friends Imported by the Visitors:');
		elseif ('invited' == $v_point) $title = ('customer' == $group) ? $this->__('Number of Friends Invited by the Customers:') : $this->__('Number of Friends Invited by the Visitors:');
		else $title = ('customer' == $group) ? $this->__('Number of Friends who responded to Customer Invitations:') : $this->__('Number of Friends who responded to Visitor Invitations:');
		return $title;
	}

	public function getType()
	{
		if ('period' == $this->_filter ['group_by']) return 'AnnotatedTimeLine';
		else return 'ColumnChart';
	}

	public function canShowChart()
	{
		$this->_filter = $this->getData('filter');
		$this->_chart_data = $this->getData('chart_data');
		$this->_v_point = $this->_chart_data ['v_point'];
		return ($this->_chart_data) ? true : false;
	
	}

	public function getOptions()
	{
		if ('visitor_type' == $this->_filter ['group_by']) {
			if ('imported' == $this->_v_point) $title = $this->__('Number of Friends Imported %s by Service', $this->getPeriod());
			else $title = ('invited' == $this->_v_point) ? $title = $this->__('Number of Friends Invited %s by Serice', $this->getPeriod()) : $title = $this->__('Number of Invitees who Responded %s by Serice', $this->getPeriod());
		} elseif ('service' == $this->_filter ['group_by']) {
			if ('imported' == $this->_v_point) $title = $this->__('Number of Friends Imported %s by Customer Type', $this->getPeriod());
			else $title = ('invited' == $this->_v_point) ? $title = $this->__('Number of Friends Invited %s by Customer Type', $this->getPeriod()) : $title = $this->__('Number of Invitees who Responded %s by Customer Type', $this->getPeriod());
		}
		
		if ('period' == $this->_filter ['group_by']) {
			$period = $this->_filter ['period_type'];
			if ('month' == $period) $date_format = 'yyyy-MM';
			if ('year' == $period) $date_format = 'yyyy';
			if ('day' == $period) $date_format = 'yyyy-MM-dd';
			$show_details = (isset($this->_filter ['detailed_information'])) ? true : false;
			$options = array ('colors' => array ('#058DC7', '#67A54B'), 'fill' => 40, 'wmode' => 'opaque', 'thickness' => 1, 'displayExactValues' => true, 'allValuesSuffix' => $this->__(' people'), 'dateFormat' => $date_format, 'displayZoomButtons' => ('day' == $period) ? true : false)

			;
		} else {
			$options = array ('backgroundColor' => array ('stroke' => '#CDDDDD', 'strokeWidth' => 1, 'fill' => '#E7EFEF'), 'fontSize' => 13, 'title' => $title, 'tooltip' => array ('showColorCode' => true), 'vAxis' => array ('gridlines' => array ('color' => '#CDDDDD', 'count' => 10)), 'colors' => ('service' == $this->_filter ['group_by']) ? array ('#058DC7', '#67A54B') : null);
		}
		
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
