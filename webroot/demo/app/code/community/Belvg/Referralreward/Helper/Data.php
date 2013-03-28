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

class Belvg_Referralreward_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * The extension is enabled/disabled
     *
     * @return boolean
     */
    public function getReferralUrl(){
        return $this->_getUrl('referralreward/customer');
    }

    /**
     * The extension is enabled/disabled
     *
     * @return boolean
     */
    public function isEnabled(){
        return Mage::getStoreConfig('referralreward/settings/enabled', Mage::app()->getStore());
    }

    /**
     * Get extension config
     *
     * @return array
     */
    public function getSettings(){
        $storeId = Mage::app()->getStore();
        return array(
            'minorder'      => Mage::getStoreConfig('referralreward/settings/minorder',            $storeId),
            'pointcost'     => Mage::getStoreConfig('referralreward/settings/pointcost',           $storeId),
            'pointinviter'  => (int)Mage::getStoreConfig('referralreward/settings/pointinviter',   $storeId),
            'pointinvitee'  => (int)Mage::getStoreConfig('referralreward/settings/pointinvitee',   $storeId),
            'email'         => Mage::getStoreConfig('referralreward/settings/invitation_template', $storeId)
        );
    }

	public function storeConfig($path) {
		return Mage::getStoreConfig('referralreward/' . $path, Mage::app()->getStore());
	}

    /**
     * @param inf The length of a coupon code
     * @param boolean Only numbers
     * @return string
     */
    public function createCouponCode($length = 10, $only_numbers = false){
        if($only_numbers)
                $symbols    = range(0, $length - 1);
        else    $symbols    = array_merge(range(0, 9), range('A', 'Z'));

        $size   = count($symbols) - 1;
        $code   = '';
        for($i=0; $i<$length; ++$i){
            $rand    = rand(0, $size);
            $code   .= $symbols[$rand];
        }
        return $code;
    }

    /**
     * Shopping cart price rules create/update
     *
     * @return boolean
     */
    public function createCouponForReferral(){
        $customer_id    = (int)Mage::getSingleton('customer/session')->getId();
        $groupId        = (int)Mage::getSingleton('customer/session')->getCustomerGroupId();
        $customer       = Mage::getModel('customer/customer')->load($customer_id);
        $points         = Mage::getModel('referralreward/points')->getItem($customer_id);
        $settings       = Mage::helper('referralreward')->getSettings();
        $myCredit       = $points->getPoints() * $settings['pointcost'];
        $coupon         = Mage::getModel('salesrule/coupon')->load($points->getCouponCode(), 'code');
        $rule           = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
        if($myCredit>0){
            if($rule->getId()){
                $rule->setDiscountAmount($myCredit);
                $rule->setCustomerGroupIds($groupId); //'0,1,2,3,4'
                $rule->save();
            }else{
                $model  = Mage::getModel('salesrule/rule');
                $model->setName('Discount for '.$customer->getName());
                $model->setDescription('Discount for '.$customer->getName());
                $model->setFromDate(date('Y-m-d'));
                $model->setCouponType(2);
                $model->setCouponCode($points->getCouponCode());
                $model->setUsesPerCoupon(1);
                //$model->setUsesPerCustomer(1);
                $model->setCustomerGroupIds($groupId);
                $model->setIsActive(1);
                $model->setConditionsSerialized('a:6:{s:4:\"type\";s:32:\"salesrule/rule_condition_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";s:1:\"1\";s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";}');
                $model->setActionsSerialized('a:6:{s:4:\"type\";s:40:\"salesrule/rule_condition_product_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";s:1:\"1\";s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";}');
                $model->setStopRulesProcessing(0);
                $model->setIsAdvanced(1);
                $model->setProductIds('');
                $model->setSortOrder(1);
                $model->setSimpleAction('cart_fixed');
                $model->setDiscountAmount($myCredit);
                $model->setDiscountStep(0);
                $model->setSimpleFreeShipping(0);
                $model->setTimesUsed(0);
                $model->setIsRss(0);
                $model->setWebsiteIds(Mage::app()->getStore()->getWebsiteId());
                $model->save();
            }
            return true;
        }else{
            if($rule->getId())
                $rule->delete();
            return false;
        }
    }

    public function checkMageVersion(){
        $val                = 1;
        if(Mage::getVersion() < 1.3)
            $val            = 0;
        elseif(Mage::getVersion() > 1.3){
            if(Mage::getVersion() == '1.4.0.1')
                    $val    = 1;
            else	$val    = 2;
        }
        return $val;
    }

	public function saveFriendsUrl($provider) {
		return Mage::getUrl('referralreward/' . $provider . '/save');
		//return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	}
	
	public function policyUrl() {
		return Mage::getUrl('referralreward/policy');
	}
}