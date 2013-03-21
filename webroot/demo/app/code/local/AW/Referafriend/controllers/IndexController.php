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
class AW_Referafriend_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if ( $bl = $this->getRequest()->getQuery('bl') )
        {
            if ($email = Mage::helper('referafriend/email')->decode($bl))
            {
                $customer = Mage::getModel('customer/customer');
                if (!$customer->getWebsiteId())
                {
                    if ($website = Mage::app()->getWebsite())
                    $customer->setWebsiteId($website->getId());
                }
                $customer->loadByEmail($email);
                if ($customer->getId())
                {
                    #store referrer_id for broadcast register
                    Mage::helper('referafriend')->setBroadcastReferrer( $customer->getId() );
                }
            }
        }
        $ref = (int) $this->getRequest()->getQuery('ref', 0);
        if ($ref){
            $invite = Mage::getModel('referafriend/invite')->load($ref);
            if ($invite && $invite->getId() && !$invite->getReferralId()){
                Mage::helper('referafriend/referral')->setCookie($invite->getId());
            }
        }
        $this->_redirectReferer();
    }

    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        if (('index' == $action) && ((int)Mage::getStoreConfig('general/restriction/is_active')) && (((int)Mage::getStoreConfig('general/restriction/mode') == Enterprise_WebsiteRestriction_Model_Mode::ALLOW_LOGIN)||((int)Mage::getStoreConfig('general/restriction/mode') == Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER)))
            self::indexAction();
        if ('stats' == $action){
            $loginUrl = Mage::helper('customer')->getLoginUrl();

            if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }
        }
    }

    public function statsAction()
    {
        $helper = Mage::helper('referafriend/referrer');
        if ($helper->hasDiscount()){
            Mage::getSingleton('customer/session')->addSuccess($this->__('You qualify for a discount! The discount of <b>%s</b> will be applied to your next purchase. <br> Discount can be applied not for all orders.', $helper->getDiscount(true, true)));
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('Referred Friends'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    protected function _initInviteModel(){
        $invite = Mage::getModel('referafriend/invite');
        Mage::register('invite_model', $invite);
        return $invite;
    }

    public function inviteAction()
    {
        $this->_initLayoutMessages('customer/session');
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('referafriend/invite')
                ->addData((array) Mage::getSingleton('customer/session')->getFormData())
                ->toHtml()
        );
    }

    public function inviteSendAction()
    {
        $invite = $this->_initInviteModel();
        $data = $this->getRequest()->getPost('invite', array());
        $session = Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()){
            if (isset($data['login']) && isset($data['password'])){
                try {
                    $session->login($data['login'], $data['password']);
                } catch (Exception $e) {
                    $result['error'] = true;
                    $result['error_type'] = 'incorrect_login';
                    $result['message'] = $e->getMessage();
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
            } else {
                $result['error'] = true;
                $result['error_type'] = 'no_login';
                $result['message'] = $this->__('Please log in.');
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
        }

        $customer = $session->getCustomer();
        $invite->setSender(array('name'=>$customer->getName(), 'email'=>$customer->getEmail()));
        $invite->setRecipients(array('name'=>array($data['name']), 'email'=>array($data['email'])));
        $invite->setSubject($data['subject']);
        $invite->setMessage($data['message']);

        try {
            $validateRes = $invite->validate();
            if (true === $validateRes) {
                $invite->send();
                Mage::getSingleton('customer/session')->addSuccess($this->__('Message to a friend was sent.'));
                Mage::getSingleton('customer/session')->setFormData(array());
            }
            else {
                Mage::getSingleton('customer/session')->setFormData($data);
                if (is_array($validateRes)) {
                    foreach ($validateRes as $errorMessage) {
                        Mage::getSingleton('customer/session')->addError($errorMessage);
                    }
                } else {
                    Mage::getSingleton('customer/session')->addError($this->__('Some problems with data.'));
                }
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('customer/session')->setFormData($data);
            Mage::getSingleton('customer/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->setFormData($data);
            Mage::getSingleton('customer/session')
                ->addException($e, $this->__('Some emails were not sent.'));
        }
        $this->_forward('invite');
    }

    protected function _redirectReferer($defaultUrl=null)
    {
        $refererUrl = Mage::helper('referafriend')->getredirectLink();
        if(empty($refererUrl)) 
            $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
        elseif(!preg_match('/^http/i',$refererUrl))
            $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() . $refererUrl : $defaultUrl . $refererUrl;
        $this->getResponse()->setRedirect($refererUrl);
        return $this;
    }
}