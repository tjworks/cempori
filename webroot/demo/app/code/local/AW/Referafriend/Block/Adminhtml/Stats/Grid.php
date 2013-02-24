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
/**
 * Statistics grid
 */
class AW_Referafriend_Block_Adminhtml_Stats_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * This is constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('statsGrid');
        $this->setDefaultSort('entity_id');
        $this->setVarNameDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare referrres collection to show in grid
     * @return AW_Referafriend_Block_Adminhtml_Stats_Grid
     */
    protected function _prepareCollection()
    {        
        $collection = Mage::getResourceModel('referafriend/customer_collection')->addNameToSelect();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for grid
     * @return AW_Referafriend_Block_Adminhtml_Stats_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('referafriend')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('referafriend')->__('Referrer full name'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('referafriend')->__('Referrer email'),
            'align'     => 'left',
            'index'     => 'email',
        ));

        $this->addColumn('invites_sent', array(
            'header'    => Mage::helper('referafriend')->__('Referrals sent'),
            'align'     => 'right',
            'index'     => 'invites_sent',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('invites_signedup', array(
            'header'    => Mage::helper('referafriend')->__('Referrals signed up'),
            'align'     => 'right',
            'index'     => 'invites_signedup',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('purchased_qty', array(
            'header'    => Mage::helper('referafriend')->__('Referrals purchase quantity'),
            'align'     => 'right',
            'index'     => 'purchased_qty',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('purchase_amount', array(
            'header'    => Mage::helper('referafriend')->__('Referrals Total'),
            'align'     => 'right',
            'index'     => 'purchase_amount',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('discount_earned', array(
            'header'    => Mage::helper('referafriend')->__('Discounts earned'),
            'align'     => 'right',
            'index'     => 'discount_earned',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('eligible_amount', array(
            'header'    => Mage::helper('referafriend')->__('Eligible for discount'),
            'align'     => 'right',
            'index'     => 'eligible_amount',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('discount_used', array(
            'header'    => Mage::helper('referafriend')->__('Discounts used'),
            'align'     => 'right',
            'index'     => 'discount_used',
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }
}