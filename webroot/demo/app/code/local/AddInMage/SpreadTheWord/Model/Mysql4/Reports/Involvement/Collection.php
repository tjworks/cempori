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

class AddInMage_SpreadTheWord_Model_Mysql4_Reports_Involvement_Collection extends AddInMage_SpreadTheWord_Model_Mysql4_Reports_Collection_Abstract
{
	protected $_inited = false;

	public function __construct()
	{
		parent::_construct();
		$this->setModel('spreadtheword/report_item');
		$this->_resource = Mage::getResourceModel('spreadtheword/reports')->init('spreadtheword/friends');
		$this->setConnection($this->getResource()
			->getReadConnection());
		$this->_applyFilters = false;
	}

	protected function _getSelectedColumns()
	{
		$selectedColumns = array ();
		
		$selectedColumns ['period'] = 'CASE WHEN (DATEDIFF(invite_link_used_time, invited_time) = 0) THEN \'0\' WHEN (DATEDIFF(invite_link_used_time, invited_time) = 1) THEN \'1\' WHEN (DATEDIFF(invite_link_used_time, invited_time) BETWEEN 2 AND 5) THEN \'2-5\' WHEN (DATEDIFF(invite_link_used_time, invited_time) BETWEEN 6 AND 10) THEN \'6-10\' WHEN (DATEDIFF(invite_link_used_time, invited_time) BETWEEN 11 AND 15) THEN \'11-15\' WHEN (DATEDIFF(invite_link_used_time, invited_time) BETWEEN 16 AND 20) THEN \'16-20\' WHEN (DATEDIFF(invite_link_used_time, invited_time) BETWEEN 21 AND 30) THEN \'21-30\' ELSE \'more\' END';
		$selectedColumns ['people'] = 'count(DATEDIFF(invite_link_used_time,invited_time))';
		$selectedColumns ['percentage'] = '';
		
		return $selectedColumns;
	}

	protected function _initSelect()
	{
		if ($this->_inited) return $this;
		
		$columns = $this->_getSelectedColumns();
		$mainTable = $this->getResource()
			->getMainTable();
		
		$subselect = clone $this->getSelect();
		$subselect = $subselect->from($mainTable, array (new Zend_Db_Expr('count(*)')));
		$subselect->where('invite_link_used_time IS NOT NULL');
		$subselect->where('invite_link_used_time != ?', '0000-00-00 00:00:00');
		$this->_applyStoresFilterToSelect($subselect);
		if ($this->_to !== null) $subselect->where("DATE(invited_time) <= DATE(?)", $this->_to);
		
		if ($this->_from !== null) $subselect->where("DATE(invited_time) >= DATE(?)", $this->_from);
		
		if (! is_null($this->_services)) $subselect->where('source IN(?)', $this->_services);
		
		$columns ['percentage'] = "ROUND((count(DATEDIFF(invite_link_used_time,invited_time)))/({$subselect})*100)";
		
		$select = $this->getSelect()
			->from($mainTable, $columns);
		
		$this->_applyServiceFilter();
		$this->_applyStoresFilter();
		$select->where('invite_link_used_time IS NOT NULL');
		$select->where('invite_link_used_time !=(?)', '0000-00-00 00:00:00');
		
		if ($this->_to !== null) {
			$select->where("DATE(invited_time) <= DATE(?)", $this->_to);
		}
		
		if ($this->_from !== null) {
			$select->where("DATE(invited_time) >= DATE(?)", $this->_from);
		}
		
		$select->group('period')
			->order('percentage DESC');
		
		$this->_inited = true;
		return $this;
	}

	public function load($printQuery = false, $logQuery = false)
	{
		if ($this->isLoaded()) {return $this;}
		$this->_initSelect();
		$this->setApplyFilters(false);
		return parent::load($printQuery, $logQuery);
	}
}