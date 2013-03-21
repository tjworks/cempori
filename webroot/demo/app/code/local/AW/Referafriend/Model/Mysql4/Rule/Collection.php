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
class AW_Referafriend_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('referafriend/rule');
    }

    public function setPriorityOrder($dir = 'ASC')
    {
        $this->setOrder('main_table.priority', $dir);
        return $this;
    }

    public function resetForCollect()
    {
        $ruleTable    = $this->getTable('rule');
        $this->getSelect()->reset();
        $this->getSelect()->from(array('main_table'=>$ruleTable), array());
        return $this;
    }

    public function setOnlyUseLimited()
    {
        $this->getSelect()->where('main_table.discount_usage >= ?', 1);
        return $this;
    }

    public function getOnlyEnabled()
    {
        $this->getSelect()->where('main_table.status >= ?', 1);
        return $this;
    }

    public function addCollectTables()
    {
        $discountTable  = $this->getTable('discount');
        $turnoverTable  = $this->getTable('turnover');
        $inviteTable    = $this->getTable('invite');
        $usedlinkTable = $this->getTable('usedlink');
        $this->getSelect()
             ->join(array('discount'=>$discountTable), 'discount.rule_id = main_table.rule_id', array('aw_rule_id'=>'rule_id', 'referrer_id', 'discount_id'))
             ->join(array('invite'=>$inviteTable), 'discount.referrer_id = invite.referrer_id', array())
             ->join(array('turnover'=>$turnoverTable), 'turnover.referral_id = invite.referral_id', array('order_id'))
             ->joinLeft(array('usedlink'=>$usedlinkTable), 'usedlink.referrer_id = discount.referrer_id AND usedlink.order_id = turnover.order_id', array())
             ->where('discount.discount_used >= main_table.discount_usage')
//             ->where('main_table.trig_count > 0')
             ->where('usedlink.used IS NULL')
             ->where('main_table.action_type = ?', AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE)
            ;
        return $this;
    }

    public function setReferrerFilter($referrerId)
    {
        $this->getSelect()
            ->where('discount.referrer_id = ?', $referrerId);
        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        // convert StoreId from string to Array
        foreach ($this->_items as $item) {
            $item->setStoreId(explode(',',$item->getStoreId()));
        }

        Mage::dispatchEvent('core_collection_abstract_load_after', array('collection' => $this));
        return $this;
    }

}