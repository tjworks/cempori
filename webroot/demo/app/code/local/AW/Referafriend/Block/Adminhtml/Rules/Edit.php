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
 * Rule Edit Block
 */
class AW_Referafriend_Block_Adminhtml_Rules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     * Class constructor/
     * Init JavaScript
     */
    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'referafriend';
        $this->_controller = 'adminhtml_rules';

        $this->_updateButton('save', 'label', Mage::helper('referafriend')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('referafriend')->__('Delete Rule'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_formScripts[] = "
            
           document.observe('dom:loaded', function() {  
            
                if($('target_type').getValue() == " . AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY . ") {                      
                   processOptionsForSignUp('none');                   
                }
                
           }); 
           

         function processOptionsForSignUp(type) {           

                var  actionOpt = $('action_type').options;

                var addOption = true;
                for(i in actionOpt) {   
                    try{                
                        if(actionOpt[i].value == " . AW_Referafriend_Model_Rule::TARGET_PURCHASE_AMOUNT . ") {                                             
                            addOption = false;  
                            if(type == 'none') { $(actionOpt[i]).remove(); }
                         }    
                      } catch(e)  {  }
                 } 
            
                if(type == 'block' && addOption === true) {  
                    var option= document.createElement('option');
                    option.text= '".AW_Referafriend_Model_Rule::getSignUpTargetAction()."';
                    option.value = ".AW_Referafriend_Model_Rule::TARGET_PURCHASE_AMOUNT.";
                     try
                       {
                        // for IE earlier than version 8
                        actionOpt.add(option,actionOpt.options[null]);
                       }
                     catch (e)
                       {
                        actionOpt.add(option,null);
                       }        
                 }         
               
                   if(type == 'none') {    
                        var orderTrig = document.getElementsByName('pre_trig_count');

                            for(e in orderTrig) {
                                if(orderTrig[e].type == 'text') {
                                    orderTrig[e].value = '0';                        
                                }                    
                            }           

                         $('pre_trig_count').up(1).hide();
                   }
                   else {
                        $('pre_trig_count').up(1).show();
                   }     
               
           }
 

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            checkTarget = function(){
             
                if($('target_type').getValue() == " . AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY . ") {                    
                    processOptionsForSignUp('none');
                }
                else {                   
                    processOptionsForSignUp('block');
                }
 
                if ( ( $('target_type').getValue() == " . AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY . " )
                    || ($('action_type').getValue() == " . AW_Referafriend_Model_Rule::ACTION_PERCENT_REF_FLATRATE . ") ){
                    $('applies').value = " . AW_Referafriend_Model_Rule::APPLY_ALL_CUSTOMERS . ";
                    $('applies').up(1).hide();
                } else {
                    $('applies').up(1).show();
                }
                if ( $('target_type').getValue() == " . AW_Referafriend_Model_Rule::TARGET_SIGNUPS_QTY . " ){
                    $('orders_limit').value = '0';
                    $('orders_limit').up(1).hide();
                } else {
                    $('orders_limit').up(1).show();
                }                
                explainRule();
            };
            checkAction = function(){
                checkTarget();
                explainRule();
            };
            checkAction();
        ";
    }

    /**
     * Retrives Header text
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('rule_data') && Mage::registry('rule_data')->getId()) {
            return Mage::helper('referafriend')->__('Edit Rule');
        } else {
            return Mage::helper('referafriend')->__('Add Rule');
        }
    }

    /**
     * Retrives Explain Block html and Edit Form html
     * @return string
     */
    public function getFormHtml() {
        # create explain
        $html = $this->getLayout()
                ->createBlock('referafriend/adminhtml_rules_edit_explain')
                ->toHtml();
        # create form
        $html .= $this->getLayout()
                ->createBlock('referafriend/adminhtml_rules_edit_form')
                ->setAction($this->getSaveUrl())
                ->toHtml();
        return $html;
    }

    /**
     * Retrives save url
     * @return string
     */
    public function getSaveUrl() {
        return $this->getUrl('*/*/save', array('_current' => true));
    }

}