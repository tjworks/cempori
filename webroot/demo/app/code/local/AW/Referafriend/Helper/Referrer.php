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
class AW_Referafriend_Helper_Referrer extends Mage_Core_Helper_Abstract
{
    public function getReferrerDiscount($referrerId ,$convert = true, $string = false, $filterUsed = true)
    {
        if ($referrerId) {
            $discounts = Mage::getResourceModel('referafriend/discount_collection')->loadByReferrer($referrerId);
            $_discounts = array();
            if (count($discounts)){
                if ($convert){
                    $discounts->walk('afterLoadConvert');
                }
                if ($filterUsed){
                    foreach ($discounts->getItems() as $key => $discount){
                        $rule = Mage::getSingleton('referafriend/rule')->load($discount->getRuleId());
                        if ($rule->getDiscountUsage() != 0 && $rule->getDiscountUsage() <= $discount->getDiscountUsed()){
                            $discounts->removeItemByKey($key);
                        }
                    }
                }

                if ($string){
                    $discountsByType = array();
                    $removeIds = array();
                    foreach ($discounts->getItems() as $discount){
                        if (isset($discountsByType[$discount->getType()])){
                            $discountsByType[$discount->getType()] += $discount->getAmount();
                            $removeIds[] = $discount->getId();
                        } else {
                            $discountsByType[$discount->getType()] = $discount->getAmount();
                        }
                    }
                    if (count($removeIds)){
                        foreach ($removeIds as $key){
                            $discounts->removeItemByKey($key);
                        }
                    }
                    foreach ($discounts->getItems() as $discount){
                        if (isset($discountsByType[$discount->getType()])){
                            $discount->setAmount($discountsByType[$discount->getType()]);
                        }
                    }
                    $discounts->walk('afterLoadFormat');
                    foreach ($discounts->getItems() as $discount){
                        $_discounts[] = $discount->getAmount();
                    }
                    $discount = implode(' + ', $_discounts);

                    if (!count($_discounts))
                    {
                        return 'none';
                    }
                    return $discount;
                }
                return $discounts;
            }
            else
            {
                if ($string)
                {
                    return $this->__('none');
                }
            }
        }
        return null;
    }

    public function getDiscount($convert = true, $string = false, $filterUsed = true)
    {
        if ($referrerId = Mage::getSingleton('customer/session')->getCustomerId()){
            $discounts = Mage::getResourceModel('referafriend/discount_collection')->loadByReferrer($referrerId);
            $_discounts = array();
            if (count($discounts)){
                if ($convert){
                    $discounts->walk('afterLoadConvert');
                }
                if ($filterUsed){
                    foreach ($discounts->getItems() as $key => $discount){
                        $rule = Mage::getSingleton('referafriend/rule')->load($discount->getRuleId());
                        if ($rule->getDiscountUsage() != 0 && $rule->getDiscountUsage() <= $discount->getDiscountUsed()){
                            $discounts->removeItemByKey($key);
                        }
                    }
                }
                if ($string){
/*
                    $total_greater = Mage::getResourceModel('referafriend/rule')->getTotalGreater();
                    $discount_greater = Mage::getResourceModel('referafriend/rule')->getDiscountGreater();
*/
                    $discountsByRule = array();
                    $removeIds = array();
                    foreach ($discounts->getItems() as $discount){
                        if (isset($discountsByRule[$discount->getRuleId()])){
                            $discountsByRule[$discount->getRuleId()] += $discount->getAmount();
                            $removeIds[] = $discount->getId();
                        } else {
                            $discountsByRule[$discount->getRuleId()] = $discount->getAmount();
                        }
                    }
                    if (count($removeIds)){
                        foreach ($removeIds as $key){
                            $discounts->removeItemByKey($key);
                        }
                    }
                    foreach ($discounts->getItems() as $discount){
                        if (isset($discountsByRule[$discount->getRuleId()])){
                            $discount->setAmount($discountsByRule[$discount->getRuleId()]);
                        }
                    }
                    $discounts->walk('afterLoadFormat');
                    foreach ($discounts->getItems() as $discount){
/*
                        $warning = '';

                        if(isset($total_greater[$discount->getRuleId()]))
                            $warning.='can be used only if the order total is equal or greater than '.$total_greater[$discount->getRuleId()];

                        if(isset($discount_greater[$discount->getRuleId()])){
                            if($warning != '')
                                $warning.=' and ';
                            $warning.='the discount amount is equal or greater than '.$discount_greater[$discount->getRuleId()];
                        }

                        if($warning != '')
                            $_discounts[] = $discount->getAmount().'('.$warning.')';
                        else
 */
                            $_discounts[] = $discount->getAmount();
                    }
                    $discount = implode(' + ', $_discounts);
                    return $discount;
                }
                return $discounts;
            }
        }
        return null;
    }

    public function hasDiscount()
    {
        if ($discount = $this->getDiscount()){
            $discount = $discount->getItems();
            return ((bool) $discount && (bool) count((array) $discount));
        }
    }

    public function getCouponCode()
    {
        return $this->getDiscount(true, true);
    }

    public function getCouponCodeDescription($code)
    {
        return $this->__('Your %s discount for referred friends', $code);
    }
}