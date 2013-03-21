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
class AW_Referafriend_Model_Mysql4_Discount_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('referafriend/discount');
    }

    public function loadByRule($ruleId, $referrerId = null, $referralId = null)
    {
        $this->clear();
        $select = $this->getSelect();
        $select->reset('where');
        $select->where('main_table.rule_id = ?', $ruleId);
        if ($referrerId && $referralId){
            $select->where('main_table.referrer_id = ?', $referrerId)
                ->where('main_table.referral_id = ?', $referralId);
            return $this->getFirstItem();
        }
        if ($referrerId){
            $select->where('main_table.referrer_id = ?', $referrerId);
            return $this->getFirstItem();
        }
        if ($referralId){
            $select->where('main_table.referral_id = ?', $referralId);
            return $this->getFirstItem();
        }
        $this->load();
        return $this;
    }

    public function loadByReferrer($referrerId)
    {
        $this->clear();
        $select = $this->getSelect();
        $select->where('main_table.referrer_id = ?', $referrerId)
               ->where('main_table.amount > 0');
        $select->order('priority ASC');
        $select->order('type ASC');
        $this->load();
        return $this;
    }

    public function setEligibleFilter()
    {
//        $this->getSelect()
//        ->join(array( 'rule'=>$this->getTable('referafriend/rule') ), 'main_table.rule_id = rule_rule_id', array())
//        ->where('rule.discount_usage > 0')
//        ->where('rule.action_type = ?', AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE)
//        ->whetre
//        ;
//



        return $this;
    }



}