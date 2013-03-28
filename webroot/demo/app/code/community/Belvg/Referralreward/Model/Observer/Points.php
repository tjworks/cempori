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

class Belvg_Referralreward_Model_Observer_Points{

    /**
     *  Creating database entry for a current user to store points.
     *  If this entry does not exist.
     *  <events>
     *      <controller_action_layout_load_before>
     */
    public function checkCustomer(Varien_Event_Observer $observer){
        $customer_id        = (int)Mage::getSingleton('customer/session')->getId();
        if($customer_id){
            $customerPoints = Mage::getModel('referralreward/points')->getItem($customer_id);
            if(!$customerPoints->getId()){
                $new        = Mage::getModel('referralreward/points');
                $new->setCustomerId($customer_id)
                    ->setUrl('c'.$customer_id)
                    ->setCouponCode(Mage::helper('referralreward')->createCouponCode(12, false));
                $new->save();
            }
        }
    }

    /**
     *  <events>
     *      <checkout_onepage_controller_success_action>
     *      <checkout_multishipping_controller_success_action>
     */
    public function changeCredit(Varien_Event_Observer $observer){
        
        $customer_id    = (int)Mage::getSingleton('customer/session')->getId();
        $settings       = Mage::helper('referralreward')->getSettings();
        $pointsItem     = Mage::getModel('referralreward/points')->getItem($customer_id);

        // Check withdrawn points
        if(Mage::getSingleton('core/session')->getData('mycredit') == 'used'){
            $coupon     = Mage::getModel('salesrule/coupon')->load($pointsItem->getCouponCode(), 'code');
            $rule       = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
            if($rule->getId())
                $rule->delete();
            $pointsItem->setPoints(0)->save();
        }

        // Check added points
        $order_id       = (int)Mage::getSingleton('checkout/session')->getLastOrderId();
        $tmp_order      = Mage::getModel('sales/order')->load($order_id);
        $order_amt      = (float)$tmp_order->getGrandTotal();
        $minorder       = (float)str_replace(',', '.', $settings['minorder']);
        if($order_amt >= $minorder){
            $friend     = Mage::getModel('referralreward/friends')->loadCurrentSuccessFriend($customer_id); // Belvg_Referralreward_Model_Friends::FRIEND_BRING ( == 2 )
            if($friend->getId()){
                // Inviter added points
                $pointsItem = Mage::getModel('referralreward/points')->getItem($friend->getCustomerId());
                $points     = $settings['pointinviter'] + $pointsItem->getPoints();
                $pointsItem->setPoints($points)->save();
                
                // Invitee added points
                $pointsItem = Mage::getModel('referralreward/points')->getItem($customer_id);
                $points     = $settings['pointinvitee'] + $pointsItem->getPoints();
                $pointsItem->setPoints($points)->save();

                $friend->setStatus( Belvg_Referralreward_Model_Friends::FRIEND_NO_BRING )->save();
            }
        }
    }
    
}
 
 