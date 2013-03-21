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
class AW_Referafriend_Block_Broadcastlink extends Mage_Core_Block_Template
{
    const TEMPLATE_PATH = 'referafriend/broadcastlink.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate( self::TEMPLATE_PATH );
    }

    public function getReferralLink()
    {

        $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        $email = Mage::helper('referafriend/email')->encode($email);
        if (Mage::app()->isSingleStoreMode()){
            return Mage::getUrl('referafriend/', array('_secure'=>Mage::app()->getStore(true)->isCurrentlySecure(),'_query' => array('bl'=>$email)));
        }
        if (Mage::app()->getStore()->getCode() != 'default')
        {
            $url = Mage::getUrl('referafriend/', array('_secure'=>Mage::app()->getStore(true)->isCurrentlySecure(),'_store_to_url' => true, '_store' => Mage::app()->getStore()->getId()));
        }
        else
        {
            $url = Mage::getUrl('referafriend/',array('_secure'=>Mage::app()->getStore(true)->isCurrentlySecure()));
        }
        return $url.(false === strpos($url, '?') ? '?' : '&' ) . 'bl=' . $email;
    }
}