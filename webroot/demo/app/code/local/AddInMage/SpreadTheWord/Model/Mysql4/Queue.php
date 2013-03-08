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

class AddInMage_SpreadTheWord_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{

	public function _construct()
	{
		$this->_init('spreadtheword/queue', 'id');
	}

	public function addEmailsToQueue($data)
	{
		$adapter = $this->_getWriteAdapter();
		
		try {
			$adapter->insertMultiple($this->getMainTable(), $data);
		} 

		catch (Exception $e) {
			$adapter->rollback();
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		
		$adapter->commit();
	}

	public function changeStatus($ids = false, $status, $in = false, $nin = false)
	{
		$adapter = $this->_getWriteAdapter();
		
		try {
			
			$update_data = array ('status' => $status);
			
			$where = array ();
			
			if ($ids) $where [] = $adapter->quoteInto('id IN(?)', $ids);
			if ($in) $where [] = $adapter->quoteInto('status IN(?)', $in);
			if ($nin) $where [] = $adapter->quoteInto('status NOT IN(?)', $nin);
			
			$update = $adapter->update($this->getMainTable(), $update_data, $where);
			return $update;
		} 

		catch (Exception $e) {
			$adapter->rollback();
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		
		$adapter->commit();
	}

	public function deleteFromQueue($ids = false, $in = false, $nin = false)
	{
		$adapter = $this->_getWriteAdapter();
		
		try {
			
			$where = array ();
			
			if ($ids) $where [] = $adapter->quoteInto('id IN(?)', $ids);
			
			if ($in) $where [] = $adapter->quoteInto('status IN(?)', $in);
			if ($nin) $where [] = $adapter->quoteInto('status NOT IN(?)', $nin);
			
			$delete = $adapter->delete($this->getMainTable(), $where);
			return $delete;
		} 

		catch (Exception $e) {
			$adapter->rollback();
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		
		$adapter->commit();
	}
}