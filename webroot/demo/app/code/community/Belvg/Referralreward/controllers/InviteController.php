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

class Belvg_Referralreward_InviteController extends Mage_Core_Controller_Front_Action{

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }

    /**
     * Sending messages to friends' emails
     */
    public function reminderfriendsAction(){
        $emails			= $this->getRequest()->getParam('email');
        $invite_message	= $this->getRequest()->getParam('invite-textarea-message');
        $customer_id	= (int)$this->_getSession()->getId();
        $points			= Mage::getModel('referralreward/points')->getItem($customer_id);
        if($customer_id){
            $count		= 0;
            $result     = array();
            try{
                foreach($emails AS $email){
                    $friend	= Mage::getModel('referralreward/friends')->getItem($customer_id, $email);
                    // Sending invitations
                    $this->sendToEmail( $email,
                                        $friend->getFriendName(),
                                        Mage::getUrl('invite/'.$points->getUrl()),
                                        $invite_message
                    );
                    $friend->setLatestSend(date('Y-m-d'));
                    $friend->setCountSend(1 + $friend->getCountSend());
                    $friend->save();
                    $count++;
                }
                $result['error']        = 0;
                if($count>1)
                     $result['message'] = $this->__("%s invitations have been send", $count);
                else $result['message'] = $this->__("%s invitation has been send", $count);
                $this->_getSession()->addSuccess($result['message']);
            }catch(Exception $e){
                $result['error']        = 1;
                $result['message']      = $e->getMessage();
                $this->_getSession()->addError($result['message']);
            }

            echo json_encode($result);
        }
    }

    /**
     * Send email
     *
     * @param string Recepient email
     * @param string Recepient first, last name
     * @param string Referral link current customer
     * @param string Adding a message to the email template
     * @return Mage_Referralreward_Model_Friends_Collection
     */
    public function sendToEmail($recepientEmail, $recepientName, $link, $invite_message){
        //Transactional Email Template's ID
        $settings    = Mage::helper('referralreward')->getSettings();
        $templateId  = $settings['email'];
        //Set sender information
        $customer_id = (int)$this->_getSession()->getId();
        $customer    = Mage::getModel('customer/customer')->load($customer_id);
        $senderName  = $customer['firstname'].' '.$customer['lastname'];
        $senderEmail = $customer['email'];
        $sender      = array('name'      => $senderName,
                             'email'	 => $senderEmail);
        //Set variables that can be used in email template
        $vars		 = array('sendername'=> $senderName,
                             'name'      => $customer['firstname'],
                             'message'   => $invite_message,
                             'link'      => $link);
        $translate   = Mage::getSingleton('core/translate');
        $storeId     = Mage::app()->getStore()->getId();
        //Send Transactional Email
        $template    = Mage::getModel('core/email_template')
            ->setTemplateSubject("{{var sendername}} has invited you")
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
        $translate->setTranslateInline(true);
    }

    /**
     *  Creating registration form, based on the referral link.
     *  rewrite:
     *  <from><![CDATA[#^/invite/[0-9a-z]#]]></from>
     *  <to>/referralreward/invite/index/</to>
     */
    public function indexAction(){
        if($this->_getSession()->isLoggedIn()){
            $this->_redirect('customer/account/');
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');		
        $contentBlock = $this->getLayout()->createBlock('customer/form_register')
            ->setTemplate('belvg/referralreward/registration-form.phtml');
        $this->getLayout()->getBlock('content')->append($contentBlock);
        $this->getLayout()->getBlock('head')
            #->setTitle($this->__('This is a personal registration link to the site'))
            #->setDescription($this->__('Hey, I find this site absolutely awesome. Take a look!'));
            ->setTitle("Join Legacy Row")
            ->setDescription("Legacy Row, enjoy the finer side of being a fan");
        $this->renderLayout();
    }


}