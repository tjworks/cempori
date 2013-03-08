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

class AddInMage_SpreadTheWord_Model_Friends extends Mage_Core_Model_Abstract
{

	public function _construct()
	{
		parent::_construct();
		$this->_init('spreadtheword/friends');
	}

	public function addFriends($friends, $source)
	{
		$user_type = $friends [0] ['user_type'];
		$user_id = $friends [0] ['user_id'];
		
		$friends_ids = $this->getCollection()
			->addFieldToFilter('user_id', array ('eq' => $user_id))
			->addFieldToFilter('user_type', array ('eq' => $user_type))
			->addFieldToFilter('source', array ('eq' => $source))
			->getColumnValues('friend_id');
		
		$friends_array = array ();
		foreach ($friends as $friend) {
			if (! in_array($friend ['friend_id'], $friends_ids)) {
				$friends_array [] = array ('user_type' => $friend ['user_type'], 'user_id' => $friend ['user_id'], 'source_type' => $friend ['source_type'], 'source' => $friend ['source'], 'friend_id' => $friend ['friend_id'], 'friend_name' => $friend ['friend_name'], 'friend_url' => (isset($friend ['friend_url'])) ? $friend ['friend_url'] : null, 'friend_picture' => (isset($friend ['friend_picture'])) ? $friend ['friend_picture'] : null, 'friend_invite_link' => $friend ['friend_invite_link'], 'invited' => (isset($friend ['invited'])) ? true : false, 'already_customer' => (isset($friend ['already_customer'])) ? true : false, 'time' => $friend ['time'], 'store_id' => $friend ['store_id']);
			}
		}
		
		if ($friends_array) Mage::getModel('spreadtheword/friends')->getResource()
			->addFriends($friends_array);
	}

	public function getStore()
	{
		$storeId = $this->getStoreId();
		if ($storeId) {return Mage::app()->getStore($storeId);}
		return Mage::app()->getStore();
	}

	public function trackLink($link)
	{
		$stw_id = Mage::getModel('spreadtheword/friends')->getResource()
			->trackFriend($link);
		if ($stw_id) {
			$id = Mage::getModel('spreadtheword/sales')->getResource()
				->getIdByUserId($stw_id);
			if ($id) {
				Mage::getSingleton('customer/session')->setStwSalesId($id);
				Mage::helper('spreadtheword')->refreshStwCustomerId($id);
			}
			
			return true;
		}
		return false;
	}

	public function handleContacts($contacts, $source_type, $source)
	{
		if (count($contacts) > 0) {
			
			$contacts = $this->processContacts($contacts, $source_type, $source);
			Mage::getSingleton('customer/session')->setFriends($contacts);
			
			$this->addFriends($contacts, $source);
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword/friends"));
		} 

		else {
			
			Mage::getSingleton('core/session')->addNotice(Mage::helper('spreadtheword')->__('No contacts found. Please try to find your friends using others services.'));
			Mage::helper('spreadtheword')->processWindows(Mage::getUrl("spreadtheword"));
		}
		return;
	}

	protected function getShortIdentifier()
	{
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$lengthMin = 6;
		$lengthMax = 7;
		$length = rand($lengthMin, $lengthMax);
		$result = '';
		$indexMax = strlen($alphabet) - 1;
		for($i = 0; $i < $length; $i ++) {
			$index = rand(0, $indexMax);
			$result .= $alphabet {$index};
		}
		return $result;
	}

	public function processContacts($contacts, $source_type, $source)
	{
		$contacts = $this->removeDuplicates($contacts);
		
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$user_type = 'customer';
			$user_id = Mage::getSingleton('customer/session')->getCustomer()
				->getId();
		} else {
			$user_type = 'visitor';
			$visitor_data = Mage::getSingleton('core/session')->getVisitorData();
			$user_id = $visitor_data ['visitor_id'];
		}
		
		$date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
		$websiteId = Mage::app()->getWebsite()
			->getId();
		$store_id = $this->getStore()
			->getId();
		$customer = Mage::getModel('customer/customer');
		$encryptor = Mage::helper('core');
		$contact_list = array ();
		

		$user_friends = Mage::getModel('spreadtheword/friends')->getResource()
			->checkFriends($user_id, $user_type);
		$check_for_invited = (count($user_friends)) ? true : false;
		
		foreach ($contacts as $contact) {
			
			$c2 = array ();
			$c2 ['user_type'] = $user_type;
			$c2 ['user_id'] = (int) $user_id;
			$c2 ['source_type'] = $source_type;
			$c2 ['source'] = $source;
			$c2 ['friend_id'] = $contact ['id'];
			$c2 ['friend_name'] = $contact ['name'];
			if (isset($contact ['link'])) $c2 ['friend_url'] = $contact ['link'];
			if (isset($contact ['picture'])) $c2 ['friend_picture'] = $contact ['picture'];
			
			$c2 ['time'] = $date;
			
			if ($check_for_invited) {
				$friend = (isset($user_friends [$contact ['id']])) ? $user_friends [$contact ['id']] : false;
				if ($friend) {
					if ($friend ['invited']) $c2 ['invited'] = '1';
					
					$c2 ['friend_invite_link'] = $friend ['friend_invite_link'];
				} 				
				else
					$c2 ['friend_invite_link'] = $this->getShortIdentifier();
			} else
				$c2 ['friend_invite_link'] = $this->getShortIdentifier();
			
			if ('social' !== $source_type) {
				if ($websiteId) $customer->setWebsiteId($websiteId);
				
				$customer->loadByEmail($contact ['id']);
				
				if ($customer->getId()) $c2 ['already_customer'] = '1';
			}
			
			$c2 ['store_id'] = $store_id;
			$contact_list [] = $c2;
		}
		$contact_list = $this->sortData($contact_list);
		return $contact_list;
	}

	protected function sortData($contact_list)
	{
		usort($contact_list, array ($this, 'sortUnique'));
		return $contact_list;
	}

	protected function sortUnique(&$c1, &$c2)
	{
		return strcasecmp($c1 ['friend_name'], $c2 ['friend_name']);
	}

	protected function removeDuplicates($contact_list)
	{
		usort($contact_list, array ($this, 'leaveUnique'));
		for($z = 1, $j = 0, $n = count($contact_list); $z < $n; ++ $z) {
			if ($contact_list [$z] ['id'] == $contact_list [$j] ['id']) {
				if ($contact_list [$z] ['id'] !== $contact_list [$z] ['id']) {
					unset($contact_list [$j]);
				} else {
					unset($contact_list [$z]);
				}
			} else {
				$j = $z;
			}
		}
		return $contact_list;
	}

	protected function leaveUnique(&$c1, &$c2)
	{
		return strcasecmp($c1 ['id'], $c2 ['id']);
	}

}