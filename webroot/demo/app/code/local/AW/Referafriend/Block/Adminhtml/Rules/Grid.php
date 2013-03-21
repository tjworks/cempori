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
class AW_Referafriend_Block_Adminhtml_Rules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('rulesGrid');
      $this->setDefaultSort('priority');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('referafriend/rule')->getCollection();
      $collection->getSelect()->where('visibility = 1');
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      /*$this->addColumn('rule_id', array(
          'header'    => Mage::helper('referafriend')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'rule_id',
      ));*/

      $rule = Mage::getModel('referafriend/rule');

      $this->addColumn('target_type', array(
          'header'    => Mage::helper('referafriend')->__('Target Type'),
          'align'     => 'left',
          /*'width'     => '160px',*/
          'index'     => 'target_type',
          'type'      => 'options',
          'options'   => $rule->getTargetsArray(),
      ));

      $this->addColumn('target_amount', array(
          'header'    => Mage::helper('referafriend')->__('Target Amount'),
          'align'     => 'right',
          'width'     => '100px',
          'index'     => 'target_amount',
      ));

      $this->addColumn('action_type', array(
          'header'    => Mage::helper('referafriend')->__('Action Type'),
          'align'     => 'left',
          'index'     => 'action_type',
          'type'      => 'options',
          'options'   => $rule->getActionsArray(),
      ));

      $this->addColumn('action_amount', array(
          'header'    => Mage::helper('referafriend')->__('Action Amount'),
          'align'     => 'right',
          'width'     => '100px',
          'index'     => 'action_amount',
      ));

      $this->addColumn('priority', array(
          'header'    => Mage::helper('referafriend')->__('Priority'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'priority',
      ));

      $this->addColumn('last_rule', array(
          'header'    => Mage::helper('referafriend')->__('Final rule'),
          'align'     => 'center',
          'width'     => '50px',
          'index'     => 'last_rule',
          'type'      => 'options',
          'options'   => $rule->getFinalRuleArray(),

      ));

      $this->addColumn('applies', array(
          'header'    => Mage::helper('referafriend')->__('Rule applies'),
          'align'     => 'left',
          'width'     => '160px',
          'index'     => 'applies',
          'type'      => 'options',
          'options'   => $rule->getAppliesArray(),
      ));

      if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('referafriend')->__('Used for stores'),
                'index'     => 'store_id',
                'width'     => '100px',
                'align'     => 'left',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => false,
            ));
        }

      $this->addColumn('trig_count', array(
          'header'    => Mage::helper('referafriend')->__('Triggers'),
          'align'     => 'right',
          'width'     => '70px',
          'index'     => 'trig_count',
      ));

      $this->addColumn('discount_usage', array(
          'header'    => Mage::helper('referafriend')->__('Usage limit'),
          'align'     => 'right',
          'width'     => '90px',
          'index'     => 'discount_usage',
      ));

      $this->addColumn('orders_limit', array(
          'header'    => Mage::helper('referafriend')->__("Referral's orders limit"),
          'align'     => 'right',
          'index'     => 'orders_limit',
      ));


      $this->addColumn('total_greater', array(
          'header'    => Mage::helper('referafriend')->__('Min order total'),
          'align'     => 'right',
          'width'     => '100px',
          'index'     => 'total_greater',
      ));


      $this->addColumn('discount_greater', array(
          'header'    => Mage::helper('referafriend')->__('Min discount'),
          'align'     => 'right',
          'width'     => '100px',
          'index'     => 'discount_greater',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('referafriend')->__('Status'),
          'align'     => 'center',
          'width'     => '60px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => $rule->getStatusArray(),
      ));


        $this->addColumn('admin_action',
            array(
                'header'    =>  Mage::helper('referafriend')->__('Action'),
                'width'     => '60px',
                'align'     => 'center',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('referafriend')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rules');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('referafriend')->__('Delete&nbsp;&nbsp;'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('referafriend')->__('Are you sure?')
        ));

//        $statuses = Mage::getSingleton('referafriend/status')->getOptionArray();
//
//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        $this->getMassactionBlock()->addItem('status', array(
//             'label'=> Mage::helper('referafriend')->__('Change status'),
//             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//             'additional' => array(
//                    'visibility' => array(
//                         'name' => 'status',
//                         'type' => 'select',
//                         'class' => 'required-entry',
//                         'label' => Mage::helper('referafriend')->__('Status'),
//                         'values' => $statuses
//                     )
//             )
//        ));
        return $this;
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}