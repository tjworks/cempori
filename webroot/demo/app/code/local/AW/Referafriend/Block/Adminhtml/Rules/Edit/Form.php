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
 * Rule Edit Form Template
 */
class AW_Referafriend_Block_Adminhtml_Rules_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML
     * @return AW_Referafriend_Block_Adminhtml_Rules_Edit_Form
     */
    protected function _prepareForm()
    {
        $rule = Mage::getModel('referafriend/rule');
        $yesno = Mage::getModel('adminhtml/system_config_source_yesno');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('target_group', array('legend'=>Mage::helper('referafriend')->__('Target')));

        $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('referafriend')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('referafriend')->__('Enabled'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('referafriend')->__('Disabled'),
              ),
          ),
        ));

        $fieldset->addField('last_rule', 'select', array(
          'label'     => Mage::helper('referafriend')->__('Stop further rules processing'),
          'name'      => 'last_rule',
          'values'    => array(
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('referafriend')->__('No'),
              ),

              array(
                  'value'     => 1,
                  'label'     => Mage::helper('referafriend')->__('Yes'),
              ),
          ),
        ));

      if (!Mage::app()->isSingleStoreMode()){
      $fieldset->addField('store_id', 'multiselect', array(
          'label'     => Mage::helper('referafriend')->__('Apply to store'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'store_id',
          'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
      ));
      }

        $fieldset->addField('target_type', 'select', array(
            'label'     => Mage::helper('referafriend')->__('Target Type'),
            'name'      => 'target_type',
            'values'    => $rule->targetsToOptionArray(),
            'onchange'  => 'checkAction()',
            'after_element_html' => '',
        ));

        $fieldset->addField('target_amount', 'text', array(
            'label'     => Mage::helper('referafriend')->__('Target Amount'),
            //'class'     => 'validate-greater-than-zero',
            'required'  => true,
            'name'      => 'target_amount',
            'onchange'  => 'explainRule()',
        ));

        $fieldset = $form->addFieldset('action_group', array('legend'=>Mage::helper('referafriend')->__('Action')));

        $fieldset->addField('action_type', 'select', array(
            'label'     => Mage::helper('referafriend')->__('Action Type'),
            'name'      => 'action_type',
            'values'    => $rule->actionsToOptionArray(),
            'onchange'  => 'checkAction()',
        ));

        $fieldset->addField('action_amount', 'text', array(
            'label'     => Mage::helper('referafriend')->__('Action Amount'),
            'class'     => 'validate-greater-than-zero',
            'required'  => true,
            'name'      => 'action_amount',
            'onchange'  => 'explainRule()',
        ));

        $fieldset = $form->addFieldset('settings_group', array('legend'=>Mage::helper('referafriend')->__('Rule Settings')));

        $fieldset->addField('priority', 'text', array(
          'label'     => Mage::helper('referafriend')->__('Processing priority'),
          'class'     => 'validate-zero-or-greater',
          'required'  => true,
          'name'      => 'priority',
          'onchange'  => 'explainRule()',
        ));

        $fieldset->addField('applies', 'select', array(
            'label'     => Mage::helper('referafriend')->__('Rule applies'),
            'name'      => 'applies',
            'values'    => $rule->appliesToOptionArray(),
            'note'        => Mage::helper('referafriend')->__('Should we take all orders from all referrals, or orders for each referral separately, to check against the target'),
//            'onchange'  => 'checkTrig()',
            'onchange'  => 'explainRule()',
        ));

        $fieldset->addField('trig_count', 'text', array(
            'label'     => Mage::helper('referafriend')->__('Triggers limit'),
            'class'     => 'validate-zero-or-greater',
            'required'  => true,
            'name'      => 'trig_count',
            'note'        => Mage::helper('referafriend')->__('How many times the discount can be earned by a referrer'),
            'onchange'  => 'explainRule()',
        ));

        $fieldset->addField('pre_trig_count', 'text', array(
            'label'     => Mage::helper('referafriend')->__('Per-order trigger limit'),
            'class'     => 'validate-zero-or-greater',
            'required'  => true,
            'name'      => 'pre_trig_count',
            'onchange'  => 'explainRule()',
        ));
        
        
        $fieldset->addField('discount_usage', 'text', array(
            'label'     => Mage::helper('referafriend')->__('Discount usage limit'),
            'class'     => 'validate-zero-or-greater',
            'required'  => true,
            'name'      => 'discount_usage',
            'note'    => Mage::helper('referafriend')->__('How many times the discount can be used by a referrer'),
            'onchange'  => 'explainRule()',
        ));

        $fieldset->addField('orders_limit', 'text', array(
            'label'     => Mage::helper('referafriend')->__('Referral orders limit'),
            'class'     => 'validate-zero-or-greater',
            'required'  => true,
            'name'      => 'orders_limit',
            'note'    => Mage::helper('referafriend')->__("How many referral's orders used to earn a discount"),
            'onchange'  => 'explainRule()',
        ));

        $fieldset->addField('allow_additional_discount', 'select', array(
            'label'     => Mage::helper('referafriend')->__('Allow usage with coupons'),
            'name'      => 'allow_additional_discount',
            'values'    => $yesno->toOptionArray(),
            'note'        => 'Whether discounts generated by this rule can be used with shopping cart coupons (Shopping cart price rules)',
            'onchange'  => 'explainRule()',
        ));

        $fieldset = $form->addFieldset('conditions_group', array('legend'=>Mage::helper('referafriend')->__('Discount applicable only if')));

        $fieldset->addField('total_greater', 'text', array(
            'label'     => Mage::helper('referafriend')->__('Order total is equal or greater than'),
            'class'     => 'validate-zero-or-greater',
            'required'  => true,
            'name'      => 'total_greater',
            'onchange'  => 'explainRule()',
        ));

        $fieldset->addField('discount_greater', 'text', array(
            'label'     => Mage::helper('referafriend')->__('Discount amount is equal or greater than'),
            'class'     => 'validate-zero-or-greater',
            'required'  => true,
            'name'      => 'discount_greater',
            'onchange'  => 'explainRule()',
        ));

        if ( Mage::getSingleton('adminhtml/session')->getRuleData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getRuleData());
            Mage::getSingleton('adminhtml/session')->setRuleData(null);
        } elseif ( Mage::registry('rule_data') ) {
            $form->setValues(Mage::registry('rule_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}