<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/******************************************
 *      MAGENTO EDITION USAGE NOTICE      *
 ******************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/******************************************
 *      DISCLAIMER                        *
 ******************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 ******************************************
 * @category   Belvg
 * @package    Belvg_Referralreward
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Referralreward_Block_Invite extends Mage_Core_Block_Template{

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get 'Point' object for a current customer
     *
     * @return Mage_Referralreward_Model_Points
     */
    protected function getItem(){
        $customer_id = (int)$this->_getSession()->getId();
        return Mage::getModel('referralreward/points')->getItem($customer_id);
    }

    /**
     * Get 'Friend' collection of objects for a current customer, filtered by status
     *
     * @param int Discribed - Belvg_Referralreward_Model_Friends
     * @return Mage_Referralreward_Model_Friends_Collection
     */
    protected function getFriends($status){
        $customer_id = (int)$this->_getSession()->getId();
        return Mage::getModel('referralreward/friends')->getFriendsCollection($customer_id, $status);
    }

}