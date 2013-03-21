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
class AW_Referafriend_Model_Rule extends Mage_Core_Model_Abstract
{
    /**
     * Signup quantity
     */
    const TARGET_SIGNUPS_QTY     = 1;

    /**
     * Purchased items quantity
     */
    const TARGET_PURCHASED_QTY   = 2;

    /**
     * Purchase amount
     */
    const TARGET_PURCHASE_AMOUNT = 3;

    /**
     * Contains targers
     * @var array
     */
    protected $_targets;

    /**
     * Apply flat rate fixed discount
     */
    const ACTION_FLATRATE                = 1;

    /**
     * Apply fixed % discount
     */
    const ACTION_PERCENT                = 2;

    /**
     * Apply % from all referrals purchases
     */
    const ACTION_PERCENT_REF_FLATRATE    = 3;

    /**
     * Contains actions
     * @var array
     */
    protected $_actions;

    /**
     * Per customer
     */
    const APPLY_PER_CUSTOMER  = 1;

    /**
     * For all referred customers
     */
    const APPLY_ALL_CUSTOMERS = 2;

    /**
     * Contains applies
     * @var array
     */
    protected $_applies;

    /**
     * Event prefix
     * @var String
     */
    protected $_eventPrefix = 'raf_rule';

    /**
     * Event object
     * @var String
     */
    protected $_eventObject = 'rule';

    /**
     * This is constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('referafriend/rule');

        $this->_targets = array(
            self::TARGET_SIGNUPS_QTY     => Mage::helper('referafriend')->__('Signups quantity'),
            self::TARGET_PURCHASED_QTY   => Mage::helper('referafriend')->__('Purchased items quantity'),
            self::TARGET_PURCHASE_AMOUNT => Mage::helper('referafriend')->__('Purchase amount'),
        );

        $this->_actions = array(
            self::ACTION_FLATRATE              => Mage::helper('referafriend')->__('Apply flat rate fixed discount'),
            self::ACTION_PERCENT              => Mage::helper('referafriend')->__('Apply fixed % discount'),
            self::ACTION_PERCENT_REF_FLATRATE => Mage::helper('referafriend')->__('Apply % from all referrals purchases'),
        );

        $this->_applies = array(
            self::APPLY_PER_CUSTOMER  => Mage::helper('referafriend')->__('Per customer'),
            self::APPLY_ALL_CUSTOMERS  => Mage::helper('referafriend')->__('For all referred customers'),
        );
    }
    
    public static function getSignupTargetAction() {
        
       return Mage::helper('referafriend')->__('Apply % from all referrals purchases');
     
    }

    /**
     * Returns tergets
     * @return array
     */
    public function getTargetsArray()
    {
        return $this->_targets;
    }

    /**
     * Returns actions
     * @return array
     */
    public function getActionsArray()
    {
        return $this->_actions;
    }

    /**
     * Returns applies
     * @return array
     */
    public function getAppliesArray()
    {
        return $this->_applies;
    }

    /**
     * Returns tergets
     * @return array
     */
    public function targetsToOptionArray()
    {
        return $this->_toOptionArray($this->_targets);
    }

    /**
     * Returns actions
     * @return array
     */
    public function actionsToOptionArray()
    {
        return $this->_toOptionArray($this->_actions);
    }

    /**
     * Returns applies
     * @return array
     */
    public function appliesToOptionArray()
    {
        return $this->_toOptionArray($this->_applies);
    }

    /**
     * Convert simple array to Options Array
     * @param array $array Array to convert
     * @return array
     */
    protected function _toOptionArray($array)
    {
        $res = array();
        foreach ($array as $value => $label) {
            $res[] = array('value' => $value, 'label' => $label);
        }
        return $res;
    }

    public function toOptionArray()
    {
        return array_slice($this->actionsToOptionArray(), 0, 2);;
    }

    /**
     * Returns status array
     * @return array
     */
    public function getStatusArray()
    {
        return array(
            '0' => Mage::helper('referafriend')->__('Disabled'),
            '1' => Mage::helper('referafriend')->__('Enabled'),
        );
    }


    /**
     * Returns final rule array
     * @return array
     */
    public function getFinalRuleArray()
    {
        return array(
            '0' => Mage::helper('referafriend')->__('No'),
            '1' => Mage::helper('referafriend')->__('Yes'),
        );
    }
    protected function _beforeSave()
    {
        // convert StoreId from Array to String
        $storeId = $this->getStoreId();

        if(is_array($storeId))
            $storeId = implode(',',$storeId);
        if (isset($storeId))
            $this->setStoreId($storeId);

        return $this;
    }
}