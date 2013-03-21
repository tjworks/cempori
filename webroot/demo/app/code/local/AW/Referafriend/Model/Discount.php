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
class AW_Referafriend_Model_Discount extends Mage_Core_Model_Abstract
{
    const TYPE_FLATRATE = 1;
    const TYPE_PERCENT  = 2;

    protected $_eventPrefix = 'raf_discount';
    protected $_eventObject = 'discount';

    protected $_discount;
    protected $_xdiscount;
    protected $_quote;
    protected $_couponCode;

    public function _construct()
    {
        parent::_construct();
        $this->_init('referafriend/discount');
    }

    public function afterLoadConvert()
    {
        if ($this->_quote){
            $store = $this->_quote->getStore();
        } else {
            $store = Mage::app()->getStore();
        }
        switch ($this->getType()){
            case AW_Referafriend_Model_Rule::ACTION_PERCENT:
                $this->setAmount($store->roundPrice($this->getAmount()));
                break;
            case AW_Referafriend_Model_Rule::ACTION_FLATRATE:
                $this->setAmount($store->convertPrice($this->getAmount()));
                break;
        }
        return $this;
    }

    public function afterLoadFormat()
    {
        if ($this->_quote){
            $store = $this->_quote->getStore();
        } else {
            $store = Mage::app()->getStore();
        }
        switch ($this->getType()){
            case AW_Referafriend_Model_Rule::ACTION_PERCENT:
                $this->setAmount($store->roundPrice($this->getAmount()).'%');
                break;
            case AW_Referafriend_Model_Rule::ACTION_FLATRATE:
                $this->setAmount($store->formatPrice($this->getAmount(), false));
                break;
        }
        return $this;
    }

    public function apply(Mage_Sales_Model_Quote_Item_Abstract $item)
    {    
        # Do not apply any discount if Ext is Disabled
        if (Mage::helper('referafriend')->getExtDisabled()){
            return $this;
        }

        $this->_quote = $quote = $item->getQuote();
        
        
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $address = $item->getAddress();
        } elseif ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        

        # allow additional discount logic start
        $hasAdditional = $item->getQuote()->getCouponCode() ? true : false;
     
        $helper = Mage::helper('referafriend/referrer');
       
        $baseSubtotal = $address->getBaseSubtotal();
      
        $clearUse = array();
        
        if (!$this->_discount){
            Mage::helper('referafriend')->setCustomerDiscount(0);
            
            $customer = $quote->getCustomer();
            
            $referrerId = $customer->getId();
            
            
            $discounts = Mage::getResourceModel('referafriend/discount_collection')->loadByReferrer($referrerId);
            
            
            if (count($discounts)){
                foreach ($discounts as $discount){
                    $rule = Mage::getSingleton('referafriend/rule')->load($discount->getRuleId());
                    if ( $rule->getDiscountUsage() == 0 || $rule->getDiscountUsage() > $discount->getDiscountUsed() ){
                        # allow additional discount logic
                        if ( ($hasAdditional && $rule->getAllowAdditionalDiscount()) || !$hasAdditional )
                        {
                            $this->_discount[$discount->getId()] = $discount;
                        } else {
                            $clearUse[] = $discount->getId();
                        }
                    }
                }
            }
        }
        if (!count($this->_discount)){
            # Reset used discount
            $customer = Mage::getSingleton('customer/session');
            if ($customer->isLoggedIn()){
                $customer->setDiscountUsed(array());
            }
            return $this;
        }
        $this->_getCouponCode();
//        $this->_couponCode = $this->_getCouponCode();

        $customer = Mage::getSingleton('customer/session');
        if ($customer->isLoggedIn()){
            $discountUsed = (array) $customer->getDiscountUsed();
        }

        $rafDiscount = Mage::helper('referafriend')->getCustomerDiscount(false) ? Mage::helper('referafriend')->getCustomerDiscount(false) : 0;
        $notUsed = array();
        foreach ($this->_discount as $discountId => $discount)
        {
            $discountAmount = 0;
            $baseDiscountAmount = 0;
            $rule = Mage::getModel('referafriend/rule')->load( $discount->getRuleId() );

            switch ($discount->getType()){
                case self::TYPE_PERCENT:
                    $discountPercent    = min(100, $discount->getAmount());
                    $discountAmount     = ($item->getRowTotal() - $item->getDiscountAmount()) * $discountPercent/100;
                    $baseDiscountAmount = ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()) * $discountPercent/100;
                    $checkDiscountAmount= ($baseSubtotal) * $discountPercent/100;

                    if ( !($rule->getDiscountGreater() && $checkDiscountAmount < $rule->getDiscountGreater())
                        && !($rule->getTotalGreater() && $baseSubtotal < $rule->getTotalGreater()))
                    {
                        if (isset($discountUsed))
                        {
                            if (!isset($discountUsed[$discountId]))
                            {
                               $discountUsed[$discountId] = false;
                            }
                        }

                        $rafDiscount += $baseDiscountAmount;
                        $discountAmount     = min($discountAmount + $item->getDiscountAmount(), $item->getRowTotal());
                        $baseDiscountAmount = min($baseDiscountAmount + $item->getBaseDiscountAmount(), $item->getBaseRowTotal());
                        //$discountPercent = min(100, $item->getDiscountPercent()+$percentDiscount);
                        $item->setDiscountPercent(min(100,  $item->getDiscountPercent() + $discountPercent));
                        $discountAmount     = $quote->getStore()->roundPrice($discountAmount);
                        $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
                        $item->setDiscountAmount($discountAmount);
                        $item->setBaseDiscountAmount($baseDiscountAmount);
                    }
                    else
                    {
                        $notUsed[] = $discountId;
                        if (isset($discountUsed[$discountId]))
                        {
                            unset($discountUsed[$discountId]);
                        }
                    }
                    break;
                case self::TYPE_FLATRATE:
                    $discountAmount = min($item->getRowTotal() - $item->getDiscountAmount(), $quote->getStore()->convertPrice($discount->getAmount()));
                    $baseDiscountAmount = min($item->getBaseRowTotal() - $item->getBaseDiscountAmount(), $discount->getAmount());
                    $checkDiscountAmount= $discount->getAmount();

                    if ( !($rule->getDiscountGreater() && $checkDiscountAmount < $rule->getDiscountGreater())
                        && !($rule->getTotalGreater() && $baseSubtotal < $rule->getTotalGreater()) )
                    {

                        if (isset($discountUsed))
                        {
                            if (!isset($discountUsed[$discountId]))
                            {
                               $discountUsed[$discountId] = false;
                            }
                        }

                        $rafDiscount += $baseDiscountAmount;
                        $this->_discount[$discountId]->setAmount($discount->getAmount() - $baseDiscountAmount);
                        /*$discountAmount     = $quote->getStore()->roundPrice($discountAmount);
                        $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);*/
                        $discountAmount     = min($discountAmount + $item->getDiscountAmount(), $item->getRowTotal());
                        $baseDiscountAmount = min($baseDiscountAmount + $item->getBaseDiscountAmount(), $item->getBaseRowTotal());
                        $item->setDiscountAmount($discountAmount);
                        $item->setBaseDiscountAmount($baseDiscountAmount);
                    }
                    else
                    {
                        $notUsed[] = $discountId;
                        if (isset($discountUsed[$discountId]))
                        {
                            unset($discountUsed[$discountId]);
                        }
                    }
                    break;
            }
        }
        $trueDiscounts = Mage::helper('referafriend')->getTrueDiscount();
        if(isset($trueDiscounts[$item->getId()]['discounts'][1]))
            $item->setDiscountAmount($trueDiscounts[$item->getId()]['discounts'][1]);
        Mage::helper('referafriend')->setTrueDiscount($item->getId(),$item->getDiscountAmount());

        if (!$this->_couponCode)
        {
            $this->_couponCode = $this->_getPostCouponCode($notUsed);
        }

        $couponCode = explode(', ', $address->getCouponCode());
        $couponCode[] = $helper->getCouponCodeDescription($this->_couponCode);
        $couponCode = array_unique(array_filter($couponCode));
        $address->setCouponCode(implode(', ', $couponCode));

        # injectin to Discount Description Array directly
        if (Mage::helper('referafriend')->checkVersion('1.4.0.0')){
            $arr = array();
            $arr[1] = implode(', ', $couponCode);
            $address->setDiscountDescriptionArray($arr);
        }

           # put discount amount in session store for future history
        Mage::helper('referafriend')->setCustomerDiscount($rafDiscount);

        if (count($clearUse) && $discountUsed && is_array($discountUsed) && count($discountUsed)){
            foreach ($clearUse as $discountId){
                if (isset($discountUsed[$discountId])){
                    unset($discountUsed[$discountId]);
                }
            }
        }
        if (isset($discountUsed)){
            $customer->setDiscountUsed($discountUsed);
        }
        return $this;
    }

    protected function _getPostCouponCode($notUsed)
    {
        $out = array();
        if ($this->_xdiscount && is_array($this->_xdiscount) && count($this->_xdiscount))
        {
            foreach ($this->_xdiscount as $discount)
            {
                if (!in_array($discount->getId(), $notUsed))
                {
                    $out[] = $discount->getAmount();
                }
            }
        }
        return implode(' + ', $out);
    }

    protected function _getCouponCode()
    {
        $discounts = array();
        foreach ($this->_discount as $discount){
            $_discount = clone $discount;
            $_discount->setQuote($this->_quote);
            $_discount->afterLoadConvert();
            if (isset($discounts[$_discount->getRuleId()])){
                $discounts[$_discount->getRuleId()]->setAmount($discounts[$_discount->getRuleId()]->getAmount() + $_discount->getAmount());
            } else {
                $discounts[$_discount->getRuleId()] = $_discount;
            }
        }
        foreach ($discounts as $_discount){
            $_discount->afterLoadFormat();
            $_discounts[] = $_discount->getAmount();
        }
        $this->_xdiscount = $discounts;
        return implode(' + ', $_discounts);
    }

    public function setQuote($quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    public function calculate(AW_Referafriend_Model_Rule $rule, $signups, $amount, $qty, $trig = 0,$referrerId = null, $referralId = null)
    {
         
        /* 
         * This is the switch of the logic below for: all customers and per customer
         * In per customer filter we simply add referralId to filter
         * Rule applies 1, means per customer
         * #8855 - Per customer rule doesn't work
         */
        if ($rule->getApplies() != 1) {
            if ($trig && $referrerId && Mage::getResourceModel('referafriend/discount')->getDiscountEarnedCount($referrerId, $rule->getId()) >= $trig) {
                return 0;
            }
        } else {   // Rule applies per customer, so we should consider not only referrarId and ruleId, but also referralId
            if ($trig && $referrerId && $referralId && Mage::getResourceModel('referafriend/discount')->getDiscountEarnedCountPerCustomer($referrerId, $referralId, $rule->getId()) >= $trig) {
                return 0;
            }
        }        
        /***************************************************/
        
       
        if(!$amount && $rule->getTargetType() != AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY) { return 0; }


        if($amount){
            $usedOrders = $amount;
            $amam = 0;
            foreach($amount as $key=>$am){
                if($qty[$key]['purchase_qty']>0)
                    $amam += $am['purchase_amount'];
            }
            $amount = $amam;
        }


        if ($rule->getTargetAmount() > 0){
            switch ($rule->getTargetType()){
                case AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY:
                    $m = floor($signups / $rule->getTargetAmount());
                    $rest = fmod($signups,$rule->getTargetAmount());

                    $UsedSignups    = $signups - $rest;
                    if($UsedSignups > 0){

                        $usedsignupModel = Mage::getModel('referafriend/usedsignups');
                        $usedsignupModel->saveUsedSignups($referrerId,$rule->getId(),$UsedSignups);
                    }

                    break;

                case AW_Referafriend_Model_Rule::TARGET_PURCHASED_QTY:

                    $usedOrders = $qty;
                    $qt = 0;
                    foreach($qty as $q)
                        $qt += $q['purchase_qty'];
                    $qty = $qt;

                    $m = floor($qty / $rule->getTargetAmount());
                    $rest = fmod($qty,$rule->getTargetAmount());

                    $UsedQty    = $qty - $rest;
                    if($UsedQty > 0){
                        foreach($usedOrders as $order){
                            if(($UsedQty - $order['purchase_qty']) >= 0){
                                $usedByOrder = $order['purchase_qty'];
                                $UsedQty = $UsedQty - $usedByOrder;
                            }
                            else{
                                $usedByOrder = $order['purchase_qty'] - ($order['purchase_qty'] - $UsedQty);
                                $UsedQty = 0;
                            }
                            $usedordersModel = Mage::getModel('referafriend/usedorders');
                            $usedordersModel->saveUsedOrder($order['order_id'],$rule->getId(),null,$usedByOrder);
                        }
                    }
                    break;

                case AW_Referafriend_Model_Rule::TARGET_PURCHASE_AMOUNT:

                    $m = floor($amount / $rule->getTargetAmount());
                    
                    if($rule->getPreTrigCount() != 0 && $m > $rule->getPreTrigCount())
                        $m = $rule->getPreTrigCount();
                    
                    $rest = fmod($amount,$rule->getTargetAmount());

                    $UsedAmount = $amount - $rest;
                    if($UsedAmount > 0 ){
                        foreach($usedOrders as $order){
                            if(($UsedAmount - $order['purchase_amount']) >= 0){
                                    $usedByOrder = $order['purchase_amount'];
                                    $UsedAmount = $UsedAmount - $usedByOrder;
                            }
                            else{
                                $usedByOrder = $order['purchase_amount'] - ($order['purchase_amount'] - $UsedAmount);
                                $UsedAmount = 0;
                            }
                            $usedordersModel = Mage::getModel('referafriend/usedorders');
                            $usedordersModel->saveUsedOrder($order['order_id'],$rule->getId(),$usedByOrder);
                        }
                    }
                    break;
            }
        } else {
            if ($amount > 0 && $rule->getTargetAmount() == 0 && $rule->getActionType() == AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE ){

                if(!empty($amount))
                    $discount = round(($amount * $rule->getActionAmount()) / 100, 2);
                else
                    $discount = 0;
                
                foreach($usedOrders as $order){
                    $usedordersModel = Mage::getModel('referafriend/usedorders');
                    $usedordersModel->saveUsedOrder($order['order_id'],$rule->getId(),$order['purchase_amount']);
                }
                return $discount;

            }
            else
                return 0;
        }
        if ($m > 0){
            switch($rule->getActionType()){
                    case AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE:
                        if($rule->getActionAmount() > 0 && count($amount))
                            $discount = round(($amount * $rule->getActionAmount()) / 100, 2);
                        else
                            $discount = 0;
                        break;
                    default:
                        $discount = $m * $rule->getActionAmount();
            }
        } else {
            $discount = 0;
        }
        return $discount;
    }
}