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


class AddInMage_SpreadTheWord_Block_Adminhtml_Reports_Charts_Timeline extends Mage_Adminhtml_Block_Template
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
		$cols [] = array ('id' => 'imported', 'label' => $this->__('Imported Friends:'), 'type' => 'number');
		
		if ($show_details) {
			$cols [] = array ('id' => 'title', 'label' => 'title1', 'type' => 'string');
			$cols [] = array ('id' => 'text', 'label' => 'text1', 'type' => 'string');
		}
		
		$cols [] = array ('id' => 'invited', 'label' => $this->__('Invited Friends:'), 'type' => 'number');
		$cols [] = array ('id' => 'returned', 'label' => $this->__('Responded Invitees:'), 'type' => 'number');
		
		$rows = array ();
		foreach ($collection as $index => $data) {
			if (isset($data ['time']) && isset($data ['imported']) && isset($data ['invited']) && isset($data ['invite_link_used'])) {
				$c = array ();
				$c ['c'] [] = array ('v' => ('year' == $period) ? 'Date(' . ($data ['time']) . ',11,31)' : 'Date(' . (strtotime($data ['time']) * 1000) . ')', 'f' => null);
				$c ['c'] [] = array ('v' => $data ['imported'], 'f' => null);
				
				if ($show_details) {
					$c ['c'] [] = array ('v' => $data ['title'], 'f' => null);
					$c ['c'] [] = array ('v' => $data ['text'], 'f' => null);
				}
				
				$c ['c'] [] = array ('v' => $data ['invited'], 'f' => null);
				$c ['c'] [] = array ('v' => $data ['invite_link_used'], 'f' => null);
				
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
		$show_details = (isset($this->_filter ['detailed_information'])) ? true : false;
		$options = array ('colors' => array ('#058DC7', '#ED6502', '#24CBE5'), 'displayAnnotations' => ($show_details) ? true : false, 'allowHtml' => ($show_details) ? true : false, 'annotationsWidth' => ($show_details) ? 17 : 100, 'fill' => 20, 'wmode' => 'opaque', 'thickness' => 1, 'displayExactValues' => true, 'allValuesSuffix' => $this->__(' people'), 'dateFormat' => $date_format, 'displayZoomButtons' => ('day' == $period) ? true : false)

		;
		$options = Zend_Json::encode($options);
		return $options;
	
	}
}
