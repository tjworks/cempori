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

class AddInMage_SpreadTheWord_Model_Grouped_Collection extends Varien_Data_Collection
{

	protected $_columnGroupBy = null;
	protected $_resourceCollection = array ();
	protected $_period;

	public function setColumnGroupBy($column)
	{
		$this->_columnGroupBy = $column;
		return $this;
	}

	public function load($printQuery = false, $logQuery = false)
	{
		if ($this->isLoaded()) {return $this;}
		
		parent::load($printQuery, $logQuery);
		$this->_setIsLoaded();
		
		if (! is_null($this->_columnGroupBy)) {
			$this->_mergeWithEmptyData();
			$this->_groupResourceData();
		}
		
		return $this;
	}

	public function setResourceCollection($collection)
	{
		$this->_resourceCollection = $collection;
		return $this;
	}

	public function setPeriod($period)
	{
		$this->_period = $period;
	}

	protected function _mergeWithEmptyData()
	{
		if (count($this->_items) == 0) {return $this;}
		
		foreach ($this->_items as $key => $item) {
			
			foreach ($this->_resourceCollection as $dataItem) {
				if ('day' == $this->_period) {
					if ($item->getData($this->_columnGroupBy) == date('Y-m-d', strtotime($dataItem->getData($this->_columnGroupBy)))) {
						if ($this->_items [$key]->getIsEmpty()) {
							$this->_items [$key] = $dataItem;
						} else {
							$this->_items [$key]->addChild($dataItem);
						}
					}
				} else {
					if ($item->getData($this->_columnGroupBy) == $dataItem->getData($this->_columnGroupBy)) {
						if ($this->_items [$key]->getIsEmpty()) {
							$this->_items [$key] = $dataItem;
						} else {
							$this->_items [$key]->addChild($dataItem);
						}
					}
				}
			}
		}
		
		return $this;
	}

	protected function _groupResourceData()
	{
		if (count($this->_items) == 0) {
			foreach ($this->_resourceCollection as $item) {
				if (isset($this->_items [$item->getData($this->_columnGroupBy)])) {
					$this->_items [$item->getData($this->_columnGroupBy)]->addChild($item->setIsEmpty(false));
				} else {
					$this->_items [$item->getData($this->_columnGroupBy)] = $item->setIsEmpty(false);
				}
			}
		}
		
		return $this;
	}
}
