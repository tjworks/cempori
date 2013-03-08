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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Charts_Rules extends Mage_Adminhtml_Block_Template
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
		
		$cols [] = array ('id' => null, 'label' => $this->__('Rating'), 'type' => 'string');
		
		foreach ($collection ['rule_names'] as $name)
			$cols [] = array ('id' => null, 'label' => $name, 'type' => 'number');
		
		$rows = array ();
		foreach ($collection ['data'] as $index => $data) {
			$c = array ();
			$c ['c'] [] = array ('v' => $index, 'f' => $this->__('%d place', $index));
			foreach ($data as $row_data)
				$c ['c'] [] = array ('v' => (int) $row_data, 'f' => $this->getTip((int) $row_data));
			$rows [] = $c;
		}
		
		$chart_data ['cols'] = $cols;
		$chart_data ['rows'] = $rows;
		
		return Zend_Json::encode($chart_data);
	}

	public function getRuleTypeLabel($type)
	{
		switch ($type) {
		case 'noaction':
		$value = $this->__('Invitation only');
		break;
		case 'action_d_sen':
		$value = $this->__('Invitation sender gets discount');
		break;
		case 'action_d_frd':
		$value = $this->__('Invited friends get discount');
		break;
		case 'action_d_all':
		$value = $this->__('Senders and invitees get discount');
		break;
		}
		return $value;
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
		$this->_v_point = $this->_chart_data ['v_point'];
		return ($this->_chart_data) ? true : false;
	
	}

	public function getTip($value)
	{
		switch ($this->_v_point) {
		case 'invited':
		$tip = $this->__('%d invited friends', $value);
		break;
		case 'returned':
		$tip = $this->__('%d invitees responded', $value);
		break;
		case 'profit':
		$format_helper = Mage::helper('core');
		$tip = $this->__('total profit %s', $format_helper->currency($value, true, false));
		break;
		case 'orders':
		$tip = $this->__('%d order(s)', $value);
		break;
		}
		return $tip;
	}

	public function getOptions()
	{
		switch ($this->_v_point) {
		case 'invited':
		$title = $this->__('Rule Rating by the Number of Friends Invited %s', $this->getPeriod());
		break;
		case 'returned':
		$title = $this->__('Rule Rating by the Number of Invitees Who Responded %s', $this->getPeriod());
		break;
		case 'profit':
		$title = $this->__('Rule Rating by Profit %s', $this->getPeriod());
		break;
		case 'orders':
		$title = $this->__('Rule Rating by the Numer of Orders %s', $this->getPeriod());
		break;
		}
		
		$options = array ('backgroundColor' => array ('stroke' => '#CDDDDD', 'strokeWidth' => 1, 'fill' => '#E7EFEF'), 'fontSize' => 13, 'isStacked' => true, 'title' => $title, 'tooltip' => array ('showColorCode' => true), 'hAxis' => array ('gridlines' => array ('color' => '#CDDDDD')));
		
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
