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
class AW_Referafriend_Model_Validator extends Mage_SalesRule_Model_Validator
{
    protected $_discount;

    public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        parent::process($item);

        if ( Mage::helper('referafriend')->checkVersion('1.4.0.0') ){
            if (!$this->_discount){
                $this->_discount = Mage::getModel('referafriend/discount');
            }
            if ($this->_discount){
                $this->_discount->apply($item);
            }
        }
        return $this;
    }
}
