<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Referafriend
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
/**
 * This is Observer class.
 * It listen all events Refer a Friend needs and execute models for every event.
 */
class AW_Referafriend_Model_Observer
{
    protected $_discount;

    /**
     * This collect Used discounts for referrerId and if RULE has
     * action type ACTION_PERCENT_REF_FLATRATE, store it in "usedlink" table
     * @param String|Integer $referrer_id
     */

    public function provideIE9Compatibility($observer)
    {
        $body = $observer->getResponse()->getBody();
        if (strpos(strToLower($body), 'x-ua-compatible') !== false) { return; }
        $body = preg_replace('{(</title>)}i', '$1' . '<meta http-equiv="X-UA-Compatible" content="IE=8" />', $body);
        $observer->getResponse()->setBody($body);
    }

    protected function _collectUsedDiscounts($referrer_id = 0)
    {
        if ( !$referrer_id )
        {
            return ;
        }

        $collection = Mage::getModel('referafriend/rule')->getCollection()
                    ->resetForCollect()
                    ->setOnlyUseLimited()
                    ->addCollectTables()
                    ->setReferrerFilter($referrer_id)->load();
        if (count($collection))
        {
            foreach ($collection as $item)
            {
                $model = Mage::getModel('referafriend/usedlink');
                $model    ->setUsedId( null )
                    ->setOrderId( $item->getOrderId() )
                    ->setRuleId( $item->getAwRuleId() )
                    ->setReferrerId( $item->getReferrerId() )
                    ->save();
                $discount = Mage::getSingleton('referafriend/discount')->load( $item->getDiscountId() )->delete();
//                try
//                {
//                    Mage::dispatchEvent('raf_discount_process', array('invite' => $invite));
//                }
//                catch (Exception $e) {}
            }
        }
    }

    /**
     * Meet referal and set up flag NEW_REFERRAL for use him if referral will
     * be registered.
     * @param Varien_Object $observer
     */
    public function preProcessInvite($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer->getId()){
            Mage::register('new_referral', 1);
        }
    }

    /**
     * Register referral
     * @param Varien_Object $observer
     */
    public function processInvite($observer)
    {
        $confirm = Mage::helper('referafriend')->getReqEmailConf();
        $invite = Mage::helper('referafriend/referral')->getInvite();
        if ($invite && Mage::registry('new_referral'))
        {
            $customer = $observer->getEvent()->getCustomer();
            if ($referralId = $customer->getId())
            {
                $invite->setReferralId($referralId);
                $invite->setReferralStatus((!$confirm) ? 1 : 0);
                $invite->save();

                if(Mage::helper('referafriend')->isBonusEnabled())
                    $this->giveBonus($referralId);

                Mage::helper('referafriend/referral')->deleteCookie();
                if(!$confirm){
                    try {
                        Mage::dispatchEvent('raf_discount_process', array('invite' => $invite));
                    } catch (Exception $e) {}
                }
            }
        }
        elseif (Mage::helper('referafriend')->getBroadcastReferrer() && Mage::registry('new_referral'))
        {
            $referrerId = Mage::helper('referafriend')->getBroadcastReferrer();
            $customer = $observer->getEvent()->getCustomer();
            $referralId = $customer->getId();
            $referralEmail = $customer->getEmail();
            $referralName = $customer->getName();
            if ($referrerId && $referralId && $referralEmail && $referralName)
            {
                $invite = Mage::getModel('referafriend/invite');
                try
                {
                    $invite
                        ->setReferrerId($referrerId)
                        ->setReferralId($referralId)
                        ->setReferralName($referralName)
                        ->setReferralEmail($referralEmail)
                        ->setReferralStatus((!$confirm) ? 1 : 0)
                        ->save()
                    ;

                    if(Mage::helper('referafriend')->isBonusEnabled())
                        $this->giveBonus($referralId);

                    Mage::helper('referafriend')->setBroadcastReferrer( null );
                    if(!$confirm){
                        try {
                            Mage::dispatchEvent('raf_discount_process', array('invite' => $invite));
                        } catch (Exception $e) {}
                    }
                }
                catch (Exception $e)
                {
                    Mage::throwException($e->getMessage());
                }
            }
        }
    }

    public function giveBonus($referralId){
        $discount = Mage::getModel('referafriend/discount');
        $discount->load(null)
                 ->setReferrerId($referralId)
                 ->setReferralId($referralId)
                 ->setType(Mage::helper('referafriend')->getBonusType())
                 ->setAmount(Mage::helper('referafriend')->getBonusAmount())
                 ->save();

        if(Mage::helper('referafriend')->getBonusType() == 1)
            $bonus = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol().number_format($discount->getAmount(), 2);
        else
            $bonus = $discount->getAmount() . '%';
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('referafriend')->__('Also you get the discount of %s.',$bonus));
    }
     /**
     * Register order
     * @param VArien_Object $observer Order information
     * @return AW_Referafriend_Model_Observer
     */
    public function processTurnover($observer)
    {
        $order = $observer->getEvent()->getOrder();
        # check null customer_id
        if ( !$order->getCustomerId() )
        {
            return $this;
        }
        $invite = Mage::getModel('referafriend/invite')->loadByReferral($order->getCustomerId());

        if (Mage_Sales_Model_Order::STATE_CANCELED == $order->getState()){

            $history = Mage::getModel('referafriend/history')->getCollection();
            $history->getSelect()->where('order_id = ?',$order->getEntityId());
            foreach($history as $hItem){

                $discount = Mage::getSingleton('referafriend/discount')->load($hItem->getDiscountId());
                $historyItem = Mage::getModel('referafriend/history')->load($hItem->getHistoryId());
                
                if($discount->getType() == AW_Referafriend_Model_Rule::ACTION_FLATRATE || $discount->getType() == AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE)
                    $discount->setAmount($discount->getAmount() + $historyItem->getAmount());

                $discount->setDiscountUsed((int) $discount->getDiscountUsed() - 1)->save();
                $historyItem->delete();
            }
        }

        if (Mage_Sales_Model_Order::STATE_CLOSED == $order->getState()){

            $history = Mage::getModel('referafriend/discounthistory')->getCollection();
            $history->getSelect()
                    ->where('order_id = ?',$order->getId());
            
            foreach($history->getData() as $history_item){
                $discount = Mage::getModel('referafriend/discount')->load($history_item['discount_id']);
                $discount_refund = $discount->getAmount() - $history_item['discount_amount'];
                if($discount_refund >= 0){
                    $discount->setAmount($discount_refund)
                             ->save();
                }
            }
        }

        if ($invite && $invite->getId()){
            $turnover = Mage::getModel('referafriend/turnover')->load($order->getId());
            if (Mage_Sales_Model_Order::STATE_COMPLETE == $order->getState()){
                $turnover->setReferralId($order->getCustomerId());
                $items = $order->getAllVisibleItems();
                $qty = 0;
                foreach ($items as $item){
                    $qty += $item->getQtyInvoiced();
                }
                $turnover->setStoreId($order->getStoreId());
                $turnover->setPurchasedQty($qty);
                $amount = (Mage::helper('referafriend')->getPurchaseCalculateType() == AW_Referafriend_Model_Config_Source_Calculate::PRICE_AND_TAX) ? $order->getBaseTotalPaid():$order->getBaseSubtotal();
                $turnover->setPurchaseAmount($amount);
                if (!$turnover->getId()){
                    $turnover->setId($order->getId());
                }
                try {
                    $turnover->save();
                    Mage::dispatchEvent('raf_discount_process', array('invite' => $invite,'order'=>$order));
                } catch (Exception $e) {}
            } else {
                if ($turnover->getId()){
                    $turnover->delete();
                    Mage::dispatchEvent('raf_discount_process', array('invite' => $invite));
                }
            }
        }
        $customer = Mage::getSingleton('customer/session');
        $orderCost = $order->getBaseSubtotal();
        if ($customer->isLoggedIn()){
            $discountUsed = (array) $customer->getDiscountUsed();
            if (count($discountUsed)){
                foreach ($discountUsed as $discountId => $used){
                    if (!$used)
                    {
                        if($orderCost > 0 ){
                            $discount = Mage::getSingleton('referafriend/discount')->load($discountId);
                            if($discount->getType() == AW_Referafriend_Model_Rule::ACTION_FLATRATE || $discount->getType() == AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE){
                                $curDiscountAmount = ($orderCost >= $discount->getAmount())? $discount->getAmount() : $orderCost;
                                $orderCost -= $curDiscountAmount;
                                $discount->setAmount($discount->getAmount() - $curDiscountAmount);
                            }
                            else{
                                $curDiscountAmount = $discount->getAmount();
                                $orderCost -= ($orderCost / 100 * $discount->getAmount());
                            }
                            $discount->setDiscountUsed((int) $discount->getDiscountUsed() + 1)->save();
                            $discountUsed[$discountId] = true;

                            try{
                                $history = Mage::getModel('referafriend/history')->load(null);
                                $history->setReferrerId($customer->getCustomer()->getId())
                                        ->setOrderId($order->getId())
                                        ->setDiscountId($discountId)
                                        ->setAmount($curDiscountAmount)
                                        ->setUsedAt(now())
                                        ->save();
                            } catch (Exception $e) {
                                Mage::throwException($e->getMessage());
                            }

                        }
                    }
                }
                //$customer->setDiscountUsed($discountUsed);
                $customer->setDiscountUsed(array());
                # try to delete used orders
                try
                {
                    $this->_collectUsedDiscounts( $customer->getCustomer()->getId() );
                }
                catch (Exception $e)
                {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
            }
            /*
            #put discount to history
            if ( $discount = Mage::helper('referafriend')->getCustomerDiscount(true) )
            {
                if(is_array($discountUsed)){
                    foreach ($discountUsed as $discountId => $used){
                        try{
                            $discount = Mage::getSingleton('referafriend/discount')->load($discountId);
                            $referrerId = $customer->getCustomer()->getId();
                            $orderId = $order->getId();
                            $history = Mage::getModel('referafriend/history');
                            $history->setId(null);
                            $history->setReferrerId($referrerId);
                            $history->setOrderId($orderId);
                            $history->setDiscountId($discountId);
                            $history->setAmount($discount->getAmount());
                            $history->setUsedAt(now());
                            $history->save();
                        } catch (Exception $e) {
                            Mage::throwException($e->getMessage());
                        }
                    }
                }
            }
            */
        }
        return $this;
    }

    /**
     * Process discount
     * @param Varien_Object $observer
     */
    public function processDiscounts($observer)
    {
        $inviteIds = Mage::getResourceModel('referafriend/invite')->getInviteIds();
        if (count($inviteIds)){
            $inviteModel = Mage::getModel('referafriend/invite');
            $refs = array();
            foreach ($inviteIds as $inviteId){
                $invite = $inviteModel->load($inviteId);
                if (in_array($invite->getReferrerId(), $refs)){
                    continue;
                }
                $refs[] = $invite->getReferrerId();
                Mage::dispatchEvent('raf_discount_process', array('invite' => $invite));
            }
        }
    }

    /**
     * Process discounts.
     * Start after all rule changes and after all order putting
     * @param Varien_Object $observer
     */
    public function processDiscount($observer)
    {
        $invite = $observer->getEvent()->getInvite();
        if ($referrerId = $invite->getReferrerId()){
            $inviteResource = Mage::getResourceModel('referafriend/invite');
            $turnoverResource = Mage::getResourceModel('referafriend/turnover');

            $referralIds = $inviteResource->getReferralIds($referrerId);
            $rules = Mage::getResourceModel('referafriend/rule_collection')->setPriorityOrder()->getOnlyEnabled()->load();
            $final = 0;
            $discountCollection = Mage::getResourceModel('referafriend/discount_collection');
            $discountModel = Mage::getModel('referafriend/discount');
            if (count($rules)){
                foreach ($rules as $rule){
                    if(!$final){
                        $signups = $inviteResource->getTotalInvites(true, $referrerId,true,$rule->getId());
                        $final = $rule->getLastRule();
                        # flag  for filter used turnover
                        $filterUsed = ( $rule->getActionType() == AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE ) ? true : false;
                        $discountAmount = array();
                        switch ($rule->getApplies()){
                            case AW_Referafriend_Model_Rule::APPLY_PER_CUSTOMER:
                                foreach ($referralIds as $referralId){
                                    if (0 == $referralId) continue;
                                    $amount = $turnoverResource->getTotalAmount($referralId, $filterUsed, $rule->getId(), $referrerId, $rule->getOrdersLimit(),$rule->getUpdated(),implode(',',$rule->getStoreId()),true);
                                    $qty = $turnoverResource->getTotalQty($referralId, $rule->getOrdersLimit(),$rule->getUpdated(),true,$rule->getId());
                                    $discountAmount[$referralId] = $discountModel->calculate($rule, $signups, $amount, $qty, $rule->getTrigCount(),$referrerId,$referralId);
                                }
                                break;
                            case AW_Referafriend_Model_Rule::APPLY_ALL_CUSTOMERS:
                                $amount = $turnoverResource->getTotalAmount($referralIds, $filterUsed, $rule->getId(), $referrerId, $rule->getOrdersLimit(),$rule->getUpdated(),implode(',',$rule->getStoreId()),true);
                                $qty = $turnoverResource->getTotalQty($referralIds, $rule->getOrdersLimit(),$rule->getUpdated(),true,$rule->getId());
                                $discountAmount[] = $discountModel->calculate($rule, $signups, $amount, $qty, $rule->getTrigCount(),$referrerId);
                                break;
                            default:
                                continue;
                        }
                        foreach ($discountAmount as $referralId => $_discountAmount){
                            if($rule->getTargetType() == AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY)
                                    $referralId = end($referralIds);
                            
                            /* 
                             * We should not apply discount for signups qty if it is invoice process 
                             * We just check if Event has order object and skip discount calculation 
                             * if target type is signups quantity
                             * 
                             */
                            if($rule->getTargetType() == AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY && is_object($observer->getEvent()->getOrder())) {
                                continue;
                            }
                            
                                   

                            $discount = $discountCollection->loadByRule($rule->getId(), $referrerId, $referralId);
                            if (0 == $_discountAmount && $discount->getAmount() == 0){
                                if ($discount->getId()){
                                    $discount->delete();
                                }
                                continue;
                            }

                            $discount->setRuleId($rule->getId());
                            $discount->setReferrerId($referrerId);
                            $discount->setReferralId($referralId);

                            if ($rule->getActionType() == AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE)
                            {
                                $discount->setType( AW_Referafriend_Model_Discount::TYPE_FLATRATE );
                            }
                            else
                            {
                                $discount->setType($rule->getActionType());
                            }
                            
                            
                           /* We should not calculate discount for signups quantity, becase it is always fixed and should be taken from rule
                            *  action amount
                            */
                           if($rule->getTargetType() == AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY) {                               
                                $_discountAmount = $rule->getActionAmount();
                                $discount->setAmount($_discountAmount);                                  
                            } 
                            else {                          
                                $discount->setAmount($discount->getAmount() + $_discountAmount);                            
                            }
                            
                            
                            
                            $discount->setPriority($rule->getPriority());
                            $discount->setEarned($discount->getEarned() + (($_discountAmount)? 1 : 0));
                            $discount->save();

                            // if we have discount, then save this info into discount history
                            if($_discountAmount){
                                $discount_history = Mage::getModel('referafriend/discounthistory');
                                $history_data = array(
                                    'order_id'          =>  ($observer->getOrder()) ? $observer->getOrder()->getId() : 0,
                                    'discount_id'       =>  $discount->getId(),
                                    'rule_id'           =>  $rule->getId(),
                                    'referrer_id'       =>  $referrerId,
                                    'discount_type'     =>  $discount->getType(),
                                    'discount_amount'   =>  $_discountAmount,
                                    'added'             =>  now(),
                                );
                                $discount_history->setData($history_data);
                                $discount_history->save();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Delete all discounts
     * @param Varien_Object $observer
     */
    public function removeDiscounts($observer)
    {
        $rule = $observer->getEvent()->getRule();
        Mage::getResourceModel('referafriend/discount')->deleteByRuleId($rule->getId());
    }

    /**
     * Apply discaunt to cart.
     * Executing when customer each load of cart.
     * Change state of Quote.
     * @param Varien_Object $observer
     */
    public function applyDiscount($observer)
    {
        try {
            if (!Mage::helper('referafriend/referrer')->hasDiscount()){
                return;
            }
            if (!$this->_discount){
                $this->_discount = Mage::getModel('referafriend/discount');
                Mage::helper('referafriend')->clearTrueDiscount();
            }
            return $this->_discount->apply($observer->getEvent()->getItem());
        } catch (Mage_Core_Exception $e) {
            //Mage::getSingleton('checkout/session')->addError($e->getMessage());
        } catch (Exception $e) {
            //Mage::getSingleton('checkout/session')->addError($e);
        }
    }

    public function updateCustomer($observer){
        $customer = $observer->getEvent()->getCustomer();
        if($id = $customer->getId()){
            $invite = Mage::getModel('referafriend/invite')->load($id,'referral_id');
            if($invite->getReferralStatus() == 0 && $customer->getConfirmation() == ''){
                $invite->setReferralStatus(1)
                       ->save();
                try {
                    Mage::dispatchEvent('raf_discount_process', array('invite' => $invite));
                } catch (Exception $e) {}
            }
        }
    }
}