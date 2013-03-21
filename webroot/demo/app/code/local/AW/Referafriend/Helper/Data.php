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
 * Basic helper class for Refer a Friend
 * Make more typically jobs
 */
class AW_Referafriend_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns right secured url
     * @return Srting
     */
    public function getReferafriendUrl()
    {
        return Mage::getUrl('referafriend/index/invite/', array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure()));
    }

    /**
     * Returns Invite Option flag
     * @return boolean
     */
    public function isInviteAllowed()
    {
        return Mage::getStoreConfigFlag('referafriend/invite/enabled');
    }

    public function isBonusEnabled(){
        return Mage::getStoreConfigFlag('referafriend/referral/bonus');
    }

    public function getBonusType(){
        return Mage::getStoreConfig('referafriend/referral/bonus_type');
    }

    public function getBonusAmount(){
        return Mage::getStoreConfig('referafriend/referral/bonus_amount');
    }

    /**
     * Check for invitation form the referrer with $referrerId to the referral 
     * with $email.
     * @param Integer|String $referrerId Referrer Id
     * @param String $email Referral email
     * @return boolean
     */
    public function emailReferred($referrerId, $email)
    {
        if ($referrerId && $email)
        {
            $collection = Mage::getModel('referafriend/invite')->getCollection();
            $collection->setEmailFilter($email);
            foreach ($collection as $invite)
            {
                if ( ($invite->getReferrerId() == $referrerId) || $invite->getReferralId() )
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Clear all discounts from customer
     * @return AW_Referafriend_Helper_Data
     */
    public function clearCustomerDiscount()
    {
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $session->setRafCustomerDiscount(null);
        return $this;
    }

    /**
     * Set discount for customer's session
     * @param Double $value
     * @return AW_Referafriend_Helper_Data
     */
    public function setCustomerDiscount($value)
    {
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $session->setRafCustomerDiscount($value);
        return $this;
    }

    /**
     * Returns Customer's discount from session.
     * You can clear discount value if set up flag $clear
     * @param boolean $clear Flag for clear after return
     * @return Double
     */
    public function getCustomerDiscount($clear = false)
    {
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $discount = $session->getRafCustomerDiscount();
        if ($clear)
        {
            $this->clearCustomerDiscount();
        }
        return $discount;
    }

    /**
     * Set broadcast referrer for customer's session
     * @param Integer|String $value
     * @return AW_Referafriend_Helper_Data
     */
    public function setBroadcastReferrer($value)
    {
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $session->setRafBroadcastReferrer($value);
        return $this;
    }

    /**
     * Returns broadcast referrer from customer's session
     * @return Integer|String
     */
    public function getBroadcastReferrer()
    {
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        return $session->getRafBroadcastReferrer();
    }

    /**
     * Compare param $version with magento version
     * @param String $version
     * @return boolean
     */
    public function checkVersion($version)
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    /**
     * Retrives Advanced Reviews Disabled Flag
     * @return boolean
     */
    public function getExtDisabled()
    {
        return Mage::getStoreConfig('advanced/modules_disable_output/AW_Referafriend');
    }

    /**
     * Returns Redirect link
     * @return string
     */
    public function getredirectLink()
    {
        return Mage::getStoreConfig('referafriend/invite/redirect_link');
    }

    public function getPurchaseCalculateType(){
        return Mage::getStoreConfig('referafriend/general/purchase');
    }
    
    public function setTrueDiscount($item,$discount){
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $discounts = $session->getTrueDiscount();

        $discounts[$item]['discounts'][] = $discount;
        $session->setTrueDiscount($discounts);
    }

    public function clearTrueDiscount(){
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $session->setTrueDiscount();
    }

    public function getTrueDiscount(){
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        return $session->getTrueDiscount();
    }

    public function getReqEmailConf(){
        return Mage::getStoreConfigFlag('customer/create_account/confirm');
    }

}
