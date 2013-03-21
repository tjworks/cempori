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
class AW_Referafriend_Adminhtml_RulesController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('admin/referafriend/rules');
    }

    protected function _initAction() {
        if (Mage::helper('referafriend')->checkVersion('1.4.0.0')){
             $this->_title($this->__('Refer a Friend'))
             ->_title($this->__('Rules'));
        }

        $this->loadLayout()
            ->_setActiveMenu('referafriend/rules')
            ->_addBreadcrumb(Mage::helper('referafriend')->__('Manage Rules'), Mage::helper('referafriend')->__('Manage Rules'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('referafriend/adminhtml_rules'))
            ->renderLayout();
    }

    public function editAction() {
        
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('referafriend/rule')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            # Default for priority is '1'
            if (!$model->getPriority())
            {
                $model->setPriority('1');
            }
            if (!$model->getDiscountUsage())
            {
                $model->setDiscountUsage('1');
            }
            if ($model->getAllowAdditionalDiscount() === null)
            {
                $model->setAllowAdditionalDiscount(1);
            }

            Mage::register('rule_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('referafriend/rules');
            
            if ($id === null) {
                $this->_title($this->__('Refer a Friend'))
                        ->_title($this->__('New Rule'));
            } else {

                $this->_title($this->__('Refer a Friend'))
                        ->_title($this->__('Edit Rule'));
            }
            

            $this->_addBreadcrumb(Mage::helper('referafriend')->__('Manage Rules'), Mage::helper('adminhtml')->__('Manage Rules'));
            $this->_addBreadcrumb(Mage::helper('referafriend')->__('Rule Configuration'), Mage::helper('adminhtml')->__('Rule Configuration'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('referafriend/adminhtml_rules_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('referafriend')->__('Rule does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('referafriend/rule');

            if($id = (int)$this->getRequest()->getParam('id')){

                $newRule = $data;
                $oldRule = $model->load($id)->getData();

                $unsetFromOldRule = array('rule_id','updated');
                foreach($unsetFromOldRule as $field){
                    unset($oldRule[$field]);
                }
                $unsetFromNewRule = array('form_key');
                foreach($unsetFromNewRule as $field){
                    unset($newRule[$field]);
                }

                if($newRule != $oldRule)
                    $data['updated'] = date('Y-m-d H:i:s',time());
            }
            else
                $data['updated'] = date('Y-m-d H:i:s',time());

            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('referafriend')->__('Rule was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('referafriend')->__('Unable to find rule to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $rule = Mage::getModel('referafriend/rule')->load($this->getRequest()->getParam('id'));
                if($rule->getId()){
                    $rule->setStatus(0)
                         ->setVisibility(0)
                         ->save();
                }
                /*
                $model = Mage::getModel('referafriend/rule');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                */

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('referafriend')->__('Rule was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $ruleIds = $this->getRequest()->getParam('rules');
        if(!is_array($ruleIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rule(s)'));
        } else {
            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = Mage::getModel('referafriend/rule')->load($ruleId);
                    if($rule->getId()){
                        $rule->setStatus(0)
                             ->setVisibility(0)
                             ->save();
                    }
                    /*
                    $rule = Mage::getModel('referafriend/rule')->load($ruleId);
                    $rule->delete();
                    */
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('referafriend')->__(
                        'Total of %d rule(s) were successfully deleted', count($ruleIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}