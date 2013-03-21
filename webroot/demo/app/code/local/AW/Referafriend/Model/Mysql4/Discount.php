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
class AW_Referafriend_Model_Mysql4_Discount extends AW_Referafriend_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('referafriend/discount', 'discount_id');
    }

    public function deleteByRuleId($ruleId)
    {
        if (!empty($ruleId)){
            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                $this->_getWriteAdapter()->quoteInto('rule_id IN (?)', (array) $ruleId)
            );
        }
    }

    public function deleteByReferrerAndRuleId($referrerId, $ruleId)
    {
        if ($referrerId && $ruleId){
            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                $this->_getWriteAdapter()->quoteInto('rule_id = ?', $ruleId).' AND '.
                $this->_getWriteAdapter()->quoteInto('referrer_id = ?', $referrerId)
            );
        }
    }

    public function getDiscountEarnedCount($referrerId, $ruleId){

        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(),array('SUM(earned)'))
                ->where('referrer_id = ?',$referrerId)
                ->where('rule_id = ?',$ruleId)
                //->group('referrer_id')
                ;

        return $this->_getReadAdapter()->fetchOne($select);
    }
    
    
     public function getDiscountEarnedCountPerCustomer($referrerId, $referralId, $ruleId){

        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(),array('SUM(earned)'))
                ->where('referrer_id = ?',$referrerId)
                ->where('referral_id = ?',$referralId)
                ->where('rule_id = ?',$ruleId);

        return $this->_getReadAdapter()->fetchOne($select);
    }
    
    
    
}