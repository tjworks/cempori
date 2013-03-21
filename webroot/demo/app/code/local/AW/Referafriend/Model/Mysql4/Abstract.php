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
abstract class AW_Referafriend_Model_Mysql4_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Primery key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = true;

    /**
     *  Magento bug fix
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->_beforeSave($object);
        $this->_checkUnique($object);

        if (!is_null($object->getId())) {
            $condition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName().'=?', $object->getId());
            /**
             * Not auto increment primary key support
             */
            if ($this->_isPkAutoIncrement) {
                $this->_getWriteAdapter()->update($this->getMainTable(), $this->_prepareDataForSave($object), $condition);
            } else {
                $select = $this->_getWriteAdapter()->select()->from($this->getMainTable(), array($this->getIdFieldName()))
                    ->where($condition);
                if ($this->_getWriteAdapter()->fetchOne($select) !== false) {
                    $this->_getWriteAdapter()->update($this->getMainTable(), $this->_prepareDataForSave($object), $condition);
                } else {
                    $this->_getWriteAdapter()->insert($this->getMainTable(), $this->_prepareDataForSave($object));
                }
            }
        } else {
            $this->_getWriteAdapter()->insert($this->getMainTable(), $this->_prepareDataForSave($object));
            $object->setId($this->_getWriteAdapter()->lastInsertId($this->getMainTable()));
        }

        $this->_afterSave($object);

        return $this;
    }

    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        if ($this->_isPkAutoIncrement && !$object->getId()) {
            $object->setCreatedAt(now());
        } else {
            if($object->getCreatedAt() == '0000-00-00 00:00:00' || $object->getCreatedAt() == '')
                $object->setCreatedAt(now());
        }
        $object->setUpdatedAt(now());
        $data = parent::_prepareDataForSave($object);
        return $data;
    }
}