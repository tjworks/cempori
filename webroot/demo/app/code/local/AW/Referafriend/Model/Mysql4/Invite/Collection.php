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
 * Collection of Referrer's Invites
 */
class AW_Referafriend_Model_Mysql4_Invite_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Class constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('referafriend/invite');
    }

    /**
     * Fill colection instance with statistics data
     * @return AW_Referafriend_Model_Mysql4_Invite_Collection
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
        foreach ($this->getItems() as $item){
            $item->setPurchaseAmount(Mage::getResourceSingleton('referafriend/turnover')->getTotalAmount($item->getReferralId()));
            $item->setPurchasedQty((int)Mage::getResourceSingleton('referafriend/turnover')->getTotalQty($item->getReferralId()));
//            $item->setEligibleAmount(Mage::getResourceSingleton('referafriend/turnover')->getTotalAmount($item->getReferralId()));
            $item->setEligibleAmount( Mage::getResourceSingleton('referafriend/turnover')->getEligibleAmount($item->getReferralId(), $ordersLimit) );
        }
        return $this;
    }

    /**
     * Set up Signed Up filter
     * @return AW_Referafriend_Model_Mysql4_Invite_Collection
     */
    public function setSignupFilter()
    {
        $this->getSelect()
            ->where('main_table.referral_id > 0');
        return $this;
    }

    /**
     * Set up Referrer(s) filter
     * @param int|string|array $referrerId Referrer Filter
     * @return AW_Referafriend_Model_Mysql4_Invite_Collection
     */
    public function setReferrerFilter($referrerId)
    {
        if (is_array($referrerId)){
            $this->getSelect()->where('main_table.referrer_id IN (?)', $referrerId);
        } else {
            $this->getSelect()->where('main_table.referrer_id = ?', $referrerId);
        }        
        return $this;
    }

    /**
     * Set up Email Filter
     * @param string $email Email
     * @return AW_Referafriend_Model_Mysql4_Invite_Collection
     */
    public function setEmailFilter($email)
    {
        $this->getSelect()
            ->where('main_table.referral_email = ?', strtolower($email));
        return $this;
    }

}