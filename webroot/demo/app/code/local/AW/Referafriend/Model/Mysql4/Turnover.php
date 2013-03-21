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
 * This class process referral turnovers
 */
class AW_Referafriend_Model_Mysql4_Turnover extends AW_Referafriend_Model_Mysql4_Abstract
{
    /**
     * This is constructor
     */
    protected function _construct()
    {
        $this->_isPkAutoIncrement = false;
        $this->_init('referafriend/turnover', 'order_id');
    }

    /**
     * Returns discount amount based on referral's turnovers
     * @param Integer $referralId|array
     * @param boolean $filterUsed
     * @param Integer $ruleId
     * @param Integer $referrerId
     * @param int $limit Order limitation for each referral
     * @return Double
     */
    public function getTotalAmount($referralId = null, $filterUsed = false, $ruleId = null, $referrerId = null, $limit = 0,$ruleUpdated = null, $storeId = null,$forDiscount = false)
    {
        # Fix #1411 Two rules were applied
        if ($filterUsed){
            $rule = Mage::getModel('referafriend/rule')->load($ruleId);
            if ($rule->getTargetAmount()){
                if ($rule->getTargetType() == AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY){
//                    $count = Mage::getModel('referafriend/invite')->getCollection()
//                                ->setReferrerFilter($referrerId)
//                                ->setSignupFilter()
//                                ->getSize()
//                            ;
//                    if ($count < $rule->getTargetAmount()){
//                        return 0;
//                    }
                } elseif ($rule->getTargetType() == AW_Referafriend_Model_Rule::TARGET_PURCHASE_AMOUNT) {
//                    if ($this->getTotalAmount($referralId) < $rule->getTargetAmount()){
//                        return 0;
//                    }
                } elseif ($rule->getTargetType() == AW_Referafriend_Model_Rule::TARGET_PURCHASED_QTY) {
                    if ($this->getTotalQty($referralId) < $rule->getTargetAmount()){
                        return 0;
                    }                    
                } else {
                    # Return 0 and all discounts disappeare
                    return 0;
                }
            }
        }
        # End Fix #1411
        if($forDiscount){
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table'=>$this->getMainTable()), array('main_table.order_id',' IFNULL((main_table.purchase_amount - used_orders.used_amount),main_table.purchase_amount) as purchase_amount'))
            ->joinLeft( array('used_orders' => $this->getTable('referafriend/usedorders') ), 'used_orders.order_id = main_table.order_id AND used_orders.rule_id = '.$ruleId, array())
            ;
        }
        else{
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table'=>$this->getMainTable()), 'SUM(main_table.purchase_amount)');
        }


        if (is_array($referralId)){
            $select->where('main_table.referral_id IN (?)', $referralId);
        } else {
            $select->where('main_table.referral_id = ?', $referralId);
        }
        if ($filterUsed)
        {
            $select->joinLeft( array('usedlink' => $this->getTable('referafriend/usedlink') ), 'usedlink.order_id = main_table.order_id AND usedlink.rule_id = '.$ruleId.' AND usedlink.referrer_id = '.$referrerId, array() )
                    ->where('usedlink.used IS NULL')
                    ;
        }
        if ($limit){
            $orders = $this->_getReferralLastOrderIds($referralId, $limit);
            $select->where('main_table.order_id IN (?)', $orders);
        }
        if ($ruleUpdated){
            $select->where('main_table.created_at >= ?', $ruleUpdated);
        }
        if($storeId){
            $select->where('main_table.store_id IN ('.$storeId.')');
        }

        if($forDiscount)
            $amount = $this->_getReadAdapter()->fetchAll($select);
        else
            $amount = $this->_getReadAdapter()->fetchOne($select);
        return $amount;
    }

    /**
     * Returns eligible discount amount based on referral's turnovers
     * @param Integer|array $referralId
     * @param int $limit Order limitation for each referral
     * @return Double
     */
    public function getEligibleAmount($referralId = null, $limit = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from( array('main_table'=>$this->getMainTable()), array('eligible_amount' => 'SUM(main_table.purchase_amount)') )
            ->joinLeft( array('usedlink' => $this->getTable('referafriend/usedlink') ), 'usedlink.order_id = main_table.order_id', array() )
            ->where('usedlink.used IS NULL')
        ;
        if (is_array($referralId)){
            $select->where('main_table.referral_id IN (?)', $referralId);
        } else {
            $select->where('main_table.referral_id = ?', $referralId);
        }
        if ($limit){
            $select->where('main_table.order_id IN (?)', $this->_getReferralLastOrderIds($referralId, $limit));
        }
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Returns purchased items count for referral(s)
     * @param int $referralId
     * @param int $limit Order limitation for each referral
     * @return double
     */
    public function getTotalQty($referralId = null, $limit = null,$ruleUpdated = null,$forDiscount = false,$ruleId = null)
    {
        $select = $this->_getReadAdapter()->select();

        if($forDiscount && $ruleId){
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table'=>$this->getMainTable()), array('main_table.order_id',' IFNULL((main_table.purchased_qty - used_orders.used_qty),main_table.purchased_qty) as purchase_qty'))
            ->joinLeft( array('used_orders' => $this->getTable('referafriend/usedorders') ), 'used_orders.order_id = main_table.order_id AND used_orders.rule_id = '.$ruleId, array())
            ;
        }
        else{
        $select
            ->from($this->getMainTable(), 'SUM(purchased_qty)');
            //->from(array('main_table'=>$this->getMainTable()), 'SUM(main_table.purchase_amount)');
        }

            
        if (is_array($referralId)){
            $select->where('referral_id IN (?)', $referralId);
        } else {
            $select->where('referral_id = ?', $referralId);
        }
        if ($limit){
            $select->where('main_table.order_id IN (?)', $this->_getReferralLastOrderIds($referralId, $limit));
        }
        if ($ruleUpdated){
            $select->where('created_at >= ?', $ruleUpdated);
        }
        if($forDiscount && $ruleId)
            return $this->_getReadAdapter()->fetchAll($select);

        return (int) $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Retrives array with order ids, what can be used
     * If limit is zero, return is empty array
     * @param int|string|array $referralId
     * @param int|string $limit
     * @return array
     */
    protected function _getReferralLastOrderIds($referralId, $limit = 0)
    {
        if (is_null($limit) || $limit < 1 || is_null($referralId)){
            return array();
        }
        $result = array();
        if (is_array($referralId)){
            foreach($referralId as $id){
                $result = array_merge($result, $this->_getReferralLastOrderIds($id, $limit));
            }
            return $result;
        }

        $select = $this->_getReadAdapter()->select()
                    ->from($this->getMainTable(), array('order_id'));
       
        $select->where('referral_id = ?', $referralId);
        $select->limit($limit);
        $select->order('created_at ASC');
        $data = $this->_getReadAdapter()->fetchAll($select);

        foreach($data as $item){
            $result[] = $item['order_id'];
        }
        return $result;
    }
}