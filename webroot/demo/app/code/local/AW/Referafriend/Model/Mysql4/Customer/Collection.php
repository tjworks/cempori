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
 * Collection of referrers
 */
class AW_Referafriend_Model_Mysql4_Customer_Collection extends Mage_Customer_Model_Entity_Customer_Collection
{
    /**
     * Init collection of customers
     * @return AW_Referafriend_Model_Mysql4_Customer_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->join(
                array('inv' => $this->getTable('referafriend/invite')),
                "e.entity_id = inv.referrer_id AND inv.referral_id <> '0'"
            )
            ->group('inv.referrer_id');
        return $this;
    }

    /**
     * Set up additional data to collection
     * @return AW_Referafriend_Model_Mysql4_Customer_Collection
     */
    protected function _afterLoad()
    {
        # Find max orders limit
        $rules = Mage::getModel('referafriend/rule')->getCollection();
        $ordersLimit = 0;
        foreach ($rules as $rule){
            if (is_numeric($rule->getOrdersLimit()) && ($rule->getOrdersLimit() > $ordersLimit)){
                $ordersLimit = $rule->getOrdersLimit();
            }
        }
        $helper = Mage::helper('referafriend/referrer');
        $store = Mage::app()->getStore();
        foreach ($this->getItems() as $item){
            $referralIds = Mage::getResourceSingleton('referafriend/invite')->getReferralIds($item->getReferrerId());
            if (!empty($referralIds)){
                $item->setPurchaseAmount($store->formatPrice(Mage::getResourceSingleton('referafriend/turnover')->getTotalAmount($referralIds), false));
                $item->setPurchasedQty(Mage::getResourceSingleton('referafriend/turnover')->getTotalQty($referralIds));
                $item->setInvitesSent(Mage::getResourceSingleton('referafriend/invite')->getTotalInvites(false, $item->getReferrerId()));
                $item->setInvitesSignedup(Mage::getResourceSingleton('referafriend/invite')->getTotalInvites(true, $item->getReferrerId()));
                # update from ver.1.3
                $item->setDiscountEarned($helper->getReferrerDiscount($item->getReferrerId(), true, true));
                $usedDiscount = Mage::getModel('referafriend/history')->getCollection()->getReferrerAmount($item->getReferrerId());
                $item->setDiscountUsed($store->formatPrice($usedDiscount, false));
                $item->setEligibleAmount( $store->formatPrice( Mage::getResourceSingleton('referafriend/turnover')->getEligibleAmount($referralIds), false, $ordersLimit) );
            }
        }
        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $inviteTable = $this->getTable('aw_raf_invite');
        $customersTable = $this->getTable('customer_entity');
        $countSelect = Mage::getModel('customer/customer')->getCollection()->getSelect();
        $countSelect->reset();
        $countSelect->from(array('e'=>$customersTable), 'COUNT(e.entity_id)');
        $countSelect
            ->where("(SELECT COUNT(*) FROM {$inviteTable} WHERE `referrer_id` = `e`.`entity_id` AND `referral_id` <> '0' GROUP BY `referrer_id`) > '0'")
            ;
        return $countSelect;
    }
}