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
class AW_Referafriend_Adminhtml_StatsController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('admin/referafriend/stats');
    }

    protected function _initAction() {
        if (Mage::helper('referafriend')->checkVersion('1.4.0.0')){
             $this->_title($this->__('Refer a Friend'))
             ->_title($this->__('Statistics'));
        }

        $this->loadLayout()
            ->_setActiveMenu('referafriend/stats')
            ->_addBreadcrumb(Mage::helper('referafriend')->__('Statistics'), Mage::helper('referafriend')->__('Statistics'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('referafriend/adminhtml_stats'))
            ->renderLayout();
    }
}