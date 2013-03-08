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

class AddInMage_SpreadTheWord_Model_Mysql4_Friends extends Mage_Core_Model_Mysql4_Abstract
{

	public function _construct()
	{
		$this->_init('spreadtheword/friends', 'id');
	}

	public function addFriends($data)
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

	public function checkFriends($user_id, $user_type)
	{
		$read_adapter = $this->_getReadAdapter();
		$select = $read_adapter->select()
			->from($this->getMainTable(), array ('friend_id', 'id', 'invited', 'friend_invite_link'))
			->where('user_id = ?', (int) $user_id)
			->where('user_type = ?', $user_type);
		$result = $read_adapter->fetchAssoc($select);
		
		return $result;
	}

	public function proccessUpdates($data)
	{
		if (isset($data ['update'] ['ids']) && $data ['update'] ['ids']) $this->updateFriends($data);
		
		if (isset($data ['increase_reinvited'] ['ids']) && $data ['increase_reinvited'] ['ids']) $this->increaseInvitedTime($data ['increase_reinvited'] ['ids']);
	}

	public function updateFriends($data)
	{
		$adapter = $this->_getWriteAdapter();
		
		$update_data = array ('discount_rule' => $data ['discount_rule'], 'invited_time' => $data ['invited_time'], 'stw_rule' => $data ['stw_rule'], 'invited' => true);
		$where = $adapter->quoteInto('id IN(?)', $data ['update'] ['ids']);
		$adapter->update($this->getMainTable(), $update_data, $where);
	}

	public function increaseInvitedTime($data)
	{
		$adapter = $this->_getWriteAdapter();
		
		$update_data = array ('time_reinvited' => new Zend_Db_Expr('time_reinvited + 1'));
		$where = $adapter->quoteInto('id IN(?)', $data);
		$adapter->update($this->getMainTable(), $update_data, $where);
	}

	public function updateInviteStatus($ids, $status)
	{
		$adapter = $this->_getWriteAdapter();
		
		$update_data = array ('invited' => $status, 'invited_time' => null);
		$where = $adapter->quoteInto('id IN(?)', $ids);
		$result = $adapter->update($this->getMainTable(), $update_data, $where);
		
		return $result;
	}

	public function trackFriend($link)
	{
		$datetime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
		
		$read_adapter = $this->_getReadAdapter();
		$select = $read_adapter->select()
			->from($this->getMainTable(), 'id')
			->where('friend_invite_link = ?', $link);
		$id = $read_adapter->fetchOne($select);
		
		if ($id) {
			$write_adapter = $this->_getWriteAdapter();
			$update_data = array ('invite_link_used' => true, 'invite_link_used_time' => $datetime);
			$where = $write_adapter->quoteInto('id = ?', $id);
			$write_adapter->update($this->getMainTable(), $update_data, $where);
			
			return $id;
		}
		return false;
	}
}