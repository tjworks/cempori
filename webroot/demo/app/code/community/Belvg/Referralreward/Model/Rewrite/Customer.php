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

class Belvg_Referralreward_Model_Rewrite_Customer extends Mage_Customer_Model_Customer{

    /**
     *  Verifying the referral link registration
     */
    public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0'){

        $customer       = $this;
        $invitelink     = Mage::app()->getRequest()->getParam('friend_invitelink');
        if($type=='registered' || $type=='confirmed'){
            $points     = Mage::getModel('referralreward/points')->getItemByUrl($invitelink);
            $friend     = Mage::getModel('referralreward/friends')->getItem($points->getCustomerId(), $customer->getEmail());
            if(1<strlen($invitelink)){
                if($friend->getId()){
                    $friend->setStatus(Belvg_Referralreward_Model_Friends::FRIEND_BRING)->save();
                }else{
                    $data                   = array();
                    $data['friend_name']    = $customer->getFirstname().' '.$customer->getLastname();
                    $data['friend_email']   = $customer->getEmail();
                    $data['customer_id']    = $points->getCustomerId();
                    $data['status']         = Belvg_Referralreward_Model_Friends::FRIEND_BRING;
                    $friend->setData($data)->save();
                }
                
                $settings       = Mage::helper('referralreward')->getSettings();                
                $pointsItem = Mage::getModel('referralreward/points')->getItem($points->getCustomerId());
                $points     = $settings['pointinviter'] + $pointsItem->getPoints();
                Mage::log("Add credit to inviter, was ".$pointsItem->getPoints()." now ".$points);
                $pointsItem->setPoints($points)->save();
            }
            $collection = Mage::getModel('referralreward/friends')->getOtherItems($points->getCustomerId(), $customer->getEmail());
            $collection->setDataToAll('status', Belvg_Referralreward_Model_Friends::FRIEND_NO_BRING)->save();
        }
        return parent::sendNewAccountEmail($type, $backUrl, $storeId);
    }

}
