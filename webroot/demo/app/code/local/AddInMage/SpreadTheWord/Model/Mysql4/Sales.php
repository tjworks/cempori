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

class AddInMage_SpreadTheWord_Model_Mysql4_Sales extends Mage_Core_Model_Mysql4_Abstract
{

	public function _construct()
	{
		$this->_init('spreadtheword/sales', 'id');
	}

	public function testShortDiscount($code)
	{
		$adapter = $this->_getReadAdapter();
		$select = $adapter->select()
			->from($this->getMainTable(), 'short_discount')
			->where('short_discount = ?', $code);
		return ($adapter->fetchOne($select)) ? true : false;
	}

	public function getRealDiscountData($code, $type = null)
	{
		$adapter = $this->_getReadAdapter();
		
		if ($type) $select = $adapter->select()
			->from($this->getMainTable(), array ('id', 'real_discount'))
			->where('short_discount = ?', $code)
			->where('user_type = ?', $type);
		
		else $select = $adapter->select()
			->from($this->getMainTable(), array ('id', 'real_discount'))
			->where('short_discount = ?', $code);
		
		$real_discount = $adapter->fetchRow($select);
		return $real_discount;
	}

	public function getShortDiscount($code, $in = false, $nin = false)
	{
		$adapter = $this->_getReadAdapter();
		
		$select = $adapter->select()
			->from($this->getMainTable(), 'short_discount')
			->where('real_discount = ?', $code);
		
		if ($in) $select->where('user_type IN(?)', $in);
		if ($nin) $select->where('user_type NOT IN(?)', $nin);
		
		$short_discount = $adapter->fetchOne($select);
		return $short_discount;
	}

	public function getIdByUserId($user_id)
	{
		$adapter = $this->_getReadAdapter();
		$select = $adapter->select()
			->from($this->getMainTable(), 'id')
			->where('stw_id = ?', $user_id)
			->where('user_type = ?', 'friend');
		$id = $adapter->fetchOne($select);
		return $id;
	}

	public function fixShortDiscount($code, $failed_codes)
	{
		$adapter = $this->_getWriteAdapter();
		$data = array ('real_discount' => $code);
		$where = $adapter->quoteInto('real_discount IN(?)', $failed_codes);
		$adapter->update($this->getMainTable(), $data, $where);
	}

	public function addFriendsToSalesTracker($data)
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
}