<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/******************************************
 *      MAGENTO EDITION USAGE NOTICE      *
 ******************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/******************************************
 *      DISCLAIMER                        *
 ******************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 ******************************************
 * @category   Belvg
 * @package    Belvg_Referralreward
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Referralreward_CustomerController extends Mage_Core_Controller_Front_Action{

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }

    /**
     * Default customer account referral system page
     */
    public function indexAction(){
        if(!$this->_getSession()->isLoggedIn()){
            $this->_redirect('customer/account/');
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Adding freinds to the database via the form
     */
    public function addfriendsAction(){
        $customer_id    = (int)$this->_getSession()->getId();
        if($customer_id){
            $emails     = $this->getRequest()->getParam('invite-textarea-to');
            $emails     = explode(',', $emails);
            $emails     = str_replace(' ', '', $emails);
            $names      = $emails;
            $count      = Mage::getModel('referralreward/friends')->saveFriends($customer_id, $emails, $names);
            if($count['enable'])
                if($count['enable']>1)
                     $this->_getSession()->addSuccess($this->__('%s have been added', $count['enable']));
                else $this->_getSession()->addSuccess($this->__('%s has been added', $count['enable']));
            if($count['disable'])
                $this->_getSession()->addSuccess($this->__('%s already registered', $count['disable']));
            if($count['enable']==0 && $count['disable']==0)
                $this->_getSession()->addError($this->__("no emails added"));
        }
        $this->_redirect('referralreward/customer/');
    }

    /**
     * Adding freinds to the database via the import /importer.php
     */
    public function savefriendsAction(){
        $customer_id    = (int)$this->_getSession()->getId();
        if($customer_id){
            $friends    = htmlspecialchars($this->getRequest()->getParam('data'));
            $friends    = explode('||',$friends);
            $names      = array();
            $emails     = array();
            foreach($friends AS $friend){
                $friend     = explode('|',$friend);
                $names[]    = $friend[0];
                $emails[]   = $friend[1];
            }
            $count      = Mage::getModel('referralreward/friends')->saveFriends($customer_id, $emails, $names);
            if($count['enable'])
                if($count['enable']>1)
                        $this->_getSession()->addSuccess($this->__('%s have been added', $count['enable']));
                else    $this->_getSession()->addSuccess($this->__('%s has been added', $count['enable']));
            if($count['disable'])
                $this->_getSession()->addSuccess($this->__('%s already registered', $count['disable']));
            if($count['enable']==0 && $count['disable']==0)
                $this->_getSession()->addError($this->__("no emails were added"));
        }
    }

    /**
     * Saving customer's referral link
     */
    public function savelinkAction(){
        $customer_id    = (int)$this->_getSession()->getId();
        if($customer_id){
            $renamelink = strtolower(trim(htmlspecialchars($this->getRequest()->getParam('renamelink'))));
            $points     = Mage::getModel('referralreward/points')->getItemByUrl($renamelink);
            $result     = array();
            if($points->getId()){
                $result['error']        = 1;
                $result['message']      = $this->__("This referral link already exists");
            }else{
                $result['error']        = 0;
                $result['message']      = '';
                if(4>strlen($renamelink) || 14<strlen($renamelink)){
                    $result['error']    = 1;
                    $result['message']  = $this->__("Links should contain 4 - 14 symbols");
                }
                if(!preg_match("/(^[a-z0-9_\-]+)$/", $renamelink)){
                    $result['error']    = 1;
                    $result['message']  = $this->__("The link should start with a letter. Allowed symbols - A-Z, 0-9, '-', '_'");
                }
                if($result['error']==0){
                    try{
                        Mage::getModel('referralreward/points')->saveInviteLink($customer_id, $renamelink);
                        $this->_getSession()->addSuccess($this->__("Your personal invite link has been successfully changed"));
                    }catch(Exception $e){
                        $this->_getSession()->addError($e->getMessage());
                    }
                }
            }
            echo json_encode($result);
            return;
        }else
            $this->_redirect('customer/account/');
    }


    /**
     * Removing email list only for a current customer
     */
    public function removefriendsAction(){
        $emails         = $this->getRequest()->getParam('email');
        $customer_id    = (int)$this->_getSession()->getId();
        if($customer_id){
            try{
                $count  = Mage::getModel('referralreward/friends')->removeFriends($customer_id, $emails);
                if($count>1)
                        $this->_getSession()->addSuccess($this->__("%s emails have been deleted", $count));
                else    $this->_getSession()->addSuccess($this->__("%s email has been deleted", $count));
            }catch(Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }
    }

}