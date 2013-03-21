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
 *//**
 * Rule Explain block
 */
class AW_Referafriend_Block_Adminhtml_Rules_Edit_Explain extends Mage_Adminhtml_Block_Template
{
    /**
     * Path to Explain block template
     */
    const EXPLAIN_TEMPLATE = 'referafriend/explain.phtml';

    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate( self::EXPLAIN_TEMPLATE );
    }

    /**
     * Retrives pattern with local date format
     * @return string
     */
    public function getFormat()
    {
        $currency = new Zend_Currency(Mage::app()->getStore()->getBaseCurrency()->getCode(), Mage::app()->getLocale()->getLocaleCode());
        $format = $currency->toCurrency('0');
        $format = preg_replace('/\d+.\d+/', '%f', $format);
        $format = str_replace(' ', '', $format);
        return $format;
    }
}
