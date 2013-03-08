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

class AddInMage_SpreadTheWord_Helper_Report extends Mage_Core_Helper_Abstract
{

	const REPORT_PERIOD_TYPE_DAY 		= 'day';
	const REPORT_PERIOD_TYPE_MONTH 		= 'month';
	const REPORT_PERIOD_TYPE_YEAR 		= 'year';


	public function getIntervals($from, $to, $period = self::REPORT_PERIOD_TYPE_DAY)
	{
		$intervals = array ();
		if (! $from && ! $to) {return $intervals;}
		
		$start = new Zend_Date($from, Varien_Date::DATE_INTERNAL_FORMAT);
		
		if ($period == self::REPORT_PERIOD_TYPE_DAY) {
			$dateStart = $start;
		}
		
		if ($period == self::REPORT_PERIOD_TYPE_MONTH) {
			$dateStart = new Zend_Date(date("Y-m", $start->getTimestamp()), Varien_Date::DATE_INTERNAL_FORMAT);
		}
		
		if ($period == self::REPORT_PERIOD_TYPE_YEAR) {
			$dateStart = new Zend_Date(date("Y", $start->getTimestamp()), Varien_Date::DATE_INTERNAL_FORMAT);
		}
		
		$dateEnd = new Zend_Date($to, Varien_Date::DATE_INTERNAL_FORMAT);
		
		while($dateStart->compare($dateEnd) <= 0) {
			switch ($period) {
			case self::REPORT_PERIOD_TYPE_DAY:
			$t = $dateStart->toString('yyyy-MM-dd');
			$dateStart->addDay(1);
			break;
			case self::REPORT_PERIOD_TYPE_MONTH:
			$t = $dateStart->toString('yyyy-MM');
			$dateStart->addMonth(1);
			break;
			case self::REPORT_PERIOD_TYPE_YEAR:
			$t = $dateStart->toString('yyyy');
			$dateStart->addYear(1);
			break;
			}
			$intervals [] = $t;
		}
		return $intervals;
	}

	public function prepareIntervalsCollection($collection, $from, $to, $periodType = self::REPORT_PERIOD_TYPE_DAY)
	{
		$intervals = $this->getIntervals($from, $to, $periodType);
		
		foreach ($intervals as $interval) {
			$item = Mage::getModel('adminhtml/report_item');
			$item->setTime($interval);
			$item->setIsEmpty();
			$collection->addItem($item);
		}
	}
}

