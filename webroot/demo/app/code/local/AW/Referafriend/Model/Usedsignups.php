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
class AW_Referafriend_Model_Usedsignups extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('referafriend/usedsignups');
    }

    public function saveUsedSignups($referrerId,$ruleId,$usedSignups){

        $id = Mage::getResourceModel('referafriend/usedsignups')->getUsedSignup($referrerId);

        $usedSignup = $this;
        $usedSignup->load($id);
        $usedSignup->setReferrerId($referrerId);
        $usedSignup->setRuleId($ruleId);
        $usedSignup->setUsedSignups($usedSignup->getUsedSignups() + $usedSignups);
        $usedSignup->setUpdated(now());
        $usedSignup->save();
    }

}