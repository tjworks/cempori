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
class AW_Referafriend_Block_Stats extends Mage_Core_Block_Template
{
    const XML_CONFIG_PATH_ALLOW_B_LINK = 'referafriend/invite/allow_b_link';

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('referafriend/stats.phtml');
        $invites = Mage::getResourceModel('referafriend/invite_collection')
            ->setReferrerFilter(Mage::getSingleton('customer/session')->getCustomer()->getId());
        $this->setInvites($invites);
        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('referafriend')->__('Referred Friends'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'referafriend.stats.pager')
            ->setCollection($this->getInvites());
        $this->setChild('pager', $pager);

        //$discount = $this->getLayout()->createBlock('referafriend/discount', 'referafriend.stats.discount');
        $inviteButton = $this->getLayout()->createBlock('core/template', 'referafriend.invite_button')->setTemplate('referafriend/invite_button.phtml');
        $this->setChild('invite_button', $inviteButton);

        $this->getInvites()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /*public function getDiscountHtml()
    {
        return $this->getChildHtml('discount');
    }*/

    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getCustomer()->getId();
    }

    public function getInviteButtonHtml()
    {
        return $this->getChildHtml('invite_button');
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function showEligible()
    {
        if ($this->getEligibleAmount() && $this->getTotalAmount()
                && ($this->getEligibleAmount() != $this->getTotalAmount()) )
        {
            return true;
        }
        return false;
    }

    public function getConverted($value)
    {
        $store = Mage::app()->getStore();
        return $store->formatPrice($value);
    }

    public function getTotalAmount()
    {
        $referralIds = Mage::getResourceSingleton('referafriend/invite')->getReferralIds($this->getCustomerId());
        return $this->getConverted(Mage::getResourceSingleton('referafriend/turnover')->getTotalAmount($referralIds, false));
    }

    public function getEligibleAmount()
    {
        $referralIds = Mage::getResourceSingleton('referafriend/invite')->getReferralIds($this->getCustomerId());
        return $this->getConverted(Mage::getResourceSingleton('referafriend/turnover')->getEligibleAmount($referralIds));
    }

    /*
     * Getting broadcast link block
     */
    public function getBLinkHtml()
    {
        if ( Mage::getStoreConfig( self::XML_CONFIG_PATH_ALLOW_B_LINK ) )
        {
            return $this->getLayout()->createBlock('referafriend/broadcastlink')->toHtml();
        }
    }
}
