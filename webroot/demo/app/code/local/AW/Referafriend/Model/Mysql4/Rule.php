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
class AW_Referafriend_Model_Mysql4_Rule extends AW_Referafriend_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('referafriend/rule', 'rule_id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $store = Mage::app()->getStore();
        switch ($object->getTargetType()){
            case AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY:
            case AW_Referafriend_Model_Rule::TARGET_PURCHASED_QTY:
                $object->setTargetAmount((int) $object->getTargetAmount());
                break;
            case AW_Referafriend_Model_Rule::TARGET_PURCHASE_AMOUNT:
                $object->setTargetAmount($store->roundPrice($object->getTargetAmount()));
                break;
        }
        $object->setActionAmount($store->roundPrice($object->getActionAmount()));
        $object->setTotalGreater($store->roundPrice($object->getTotalGreater()));
        $object->setDiscountGreater($store->roundPrice($object->getDiscountGreater()));

        parent::_afterLoad($object);
        return $this;
    }

    public function afterLoad(Mage_Core_Model_Abstract $object)
    {
        $store = Mage::app()->getStore();
        switch ($object->getTargetType()){
            case AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY:
            case AW_Referafriend_Model_Rule::TARGET_PURCHASED_QTY:
                $object->setTargetAmount((int) $object->getTargetAmount());
                break;
            case AW_Referafriend_Model_Rule::TARGET_PURCHASE_AMOUNT:
                $object->setTargetAmount($store->formatPrice($object->getTargetAmount(), false));
                break;
        }
        switch ($object->getActionType()){
            case AW_Referafriend_Model_Rule::ACTION_PERCENT:
                $object->setActionAmount($store->roundPrice($object->getActionAmount()).'%');
                break;
            case AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE:
                $object->setActionAmount($store->roundPrice($object->getActionAmount()).'%');
                break;
            case AW_Referafriend_Model_Rule::ACTION_FLATRATE:
                $object->setActionAmount($store->formatPrice($object->getActionAmount(), false));
                break;
        }
        if ($object->getTotalGreater()){
            $object->setTotalGreater($store->formatPrice($object->getTotalGreater(), false));
        }
        if ($object->getDiscountGreater()){
            $object->setDiscountGreater($store->formatPrice($object->getDiscountGreater(), false));
        }
        return $this;
    }

    public function getTotalGreater()
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(),array('rule_id','total_greater'))
            ->where('total_greater > 0')
            ;

        $result = $this->_getReadAdapter()->fetchAll($select);

        if($result){
            $total = array();
            foreach($result as $res){
                $total[$res['rule_id']] = $res['total_greater'];
            }
            return $total;
        }
        else
            return null;
    }

    public function getDiscountGreater()
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(),array('rule_id','discount_greater'))
            ->where('discount_greater > 0')
            ;

        $result = $this->_getReadAdapter()->fetchAll($select);

        if($result){
            $total = array();
            foreach($result as $res){
                $total[$res['rule_id']] = $res['discount_greater'];
            }
            return $total;
        }
        else
            return null;

    }

}