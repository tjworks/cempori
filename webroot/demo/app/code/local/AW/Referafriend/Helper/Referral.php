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
class AW_Referafriend_Helper_Referral extends Mage_Core_Helper_Abstract
{
    public function getInvite()
    {
        $ref = $this->_getRequest()->getCookie('referafriend', null);
        if ($ref){
            $ref = (int) base64_decode(rawurldecode($ref));
            $invite = Mage::getModel('referafriend/invite')->load($ref);
            if (($referrerId = $invite->getReferrerId()) && !$invite->getReferralId()){
                return $invite;
            }
        }
        return null;
    }

    public function setCookie($inviteId = null)
    {
        $version = Mage::getVersion();
        $cookie = Mage::getSingleton('core/cookie');
        if (version_compare($version, '1.2.1', '<')){
            $cookie->set('referafriend', base64_encode($inviteId));
        } else {
            $cookie->set('referafriend', base64_encode($inviteId), true, '/', null, null, true);
        }
    }

    public function deleteCookie()
    {
        $cookie = Mage::getSingleton('core/cookie');
        $cookie->delete('referafriend', '/', null, null, true);
    }
}
