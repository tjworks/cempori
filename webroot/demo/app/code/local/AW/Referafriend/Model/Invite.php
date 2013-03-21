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
class AW_Referafriend_Model_Invite extends Mage_Core_Model_Abstract
{
    /**
     * Path to email template
     */
    const XML_PATH_REFERAFRIEND_INVITE_TEMPLATE = 'referafriend/invite/template';

    /**
     * Event prefix
     * @var String
     */
    protected $_eventPrefix = 'raf_invite';

    /**
     * Event object
     * @var String
     */
    protected $_eventObject = 'invite';

    /**
     * This is constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('referafriend/invite');
    }

    /**
     * Load invite by referral id
     * @param String|Integer $referralId
     * @return AW_Referafriend_Model_Invite
     */
    public function loadByReferral($referralId)
    {
        return $this->load($referralId, 'referral_id');
    }

    /**
     * Load invite by referral email
     * @param String|Integer $referralId
     * @return AW_Referafriend_Model_Invite
     */
    public function loadByEmail($referralEmail)
    {
        return $this->load($referralEmail, 'referral_email');
    }

    /**
     * Is signed up
     * @return boolean
     */
    public function isSignedUp()
    {
        if ($this->getReferralId() > 0){
            return true;
        }
        return false;
    }

    /**
     * Send invite to referral
     * @return AW_Referafriend_Model_Invite
     */
    public function send()
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $errors = array();

        $this->_emailModel = Mage::getModel('core/email_template');
        $subject = htmlspecialchars($this->getSubject());
        $message = nl2br(htmlspecialchars($this->getMessage()));
        $sender  = array(
            'name' => strip_tags($this->_sender['name']),
            'email' => strip_tags($this->_sender['email'])
        );

        $referrerId = Mage::getSingleton('customer/session')->getCustomerId();

        $__emails = $this->_emails;
        foreach ($__emails as $key=>$email){
        //foreach ($this->_emails as $key => $email) {
//            $this->loadByEmail($email);

//            if ( $this->getId() && $this->getReferralId() && $this->getReferrerId() === $referrerId ){
//                Mage::throwException(Mage::helper('referafriend')->__('%s has been already referred.', $email));
//            }
            if ( Mage::helper('referafriend')->emailReferred($referrerId, $email) )
            {
                Mage::throwException(Mage::helper('referafriend')->__('%s has been already referred.', $email));
            }
            if ($this->isExistingCustomer($email)){
                Mage::throwException(Mage::helper('referafriend')->__('This customer is already registered.'));
            }
            if (strtolower($this->_sender['email']) == strtolower($email)){
                Mage::throwException(Mage::helper('referafriend')->__('You cannot invite yourself.'));
            }

            if (!$this->_emailModel)
            {
                $this->_emailModel = Mage::getModel('core/email_template');
            }

            $this->setId(null);
            $this->setReferrerId($referrerId);
            $this->setReferralName($this->_names[$key]);
            $this->setReferralEmail($email);
            $this->save();

            $this->_emailModel->setReplyTo($this->_sender['email']);
            $this->_emailModel->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_REFERAFRIEND_INVITE_TEMPLATE),
                    $sender,
                    $email,
                    $this->_names[$key],
                    array(
                        'name'          => $this->_names[$key],
                        'subject'        => $subject,
                        'message'        => $message,
                        'invite_link'    => $this->getInviteLink(),
                        'referrer_name' => Mage::getSingleton('customer/session')->getCustomer()->getName(),
                    )
            );
        }

        # Check for successfull sended
        if (!$this->_emailModel->getSentSuccess()){
            $this->delete();
            Mage::throwException(Mage::helper('referafriend')->__('Can not send invitation'));
        }

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Returnstrue if customer exists
     * @param String $email
     * @return boolean
     */
    public function isExistingCustomer($email)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);
        if ($customer && $customer->getId()){
            return true;
        }
        return false;
    }

    /**
     * Returns array with error messages with try to send email to referral
     * @return array
     */
    public function validate()
    {
        $errors = array();
        $helper = Mage::helper('referafriend');

        if (!$this->getSubject()) {
            $errors[] = $helper->__('Subject can\'t be empty');
        }

        if (!$this->getMessage()) {
            $errors[] = $helper->__('Message can\'t be empty');
        }

        $__emails = $this->_emails;
        foreach ($__emails as $email){
        //foreach ($this->_emails as $email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $errors[] = $helper->__('Email address is invalid');
                break;
            }
        }

        if (!$this->getTemplate()){
            $errors[] = $helper->__('Email template is not specified by administrator');
        }


        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Set up recepient for invite
     * @param array $recipients array('name'=>'Somebody', 'email'=>'sombody@same.com')
     */
    public function setRecipients($recipients)
    {
        $this->_emails = array_unique($recipients['email']);
        $this->_names = $recipients['name'];
    }

    /**
     * Set up sender for invite
     * @param array $recipients array('name'=>'Somebody', 'email'=>'sombody@same.com')
     */
    public function setSender($sender){
        $this->_sender = $sender;
    }

    /**
     * Returns template of email message
     * @return String
     */
    public function getTemplate()
    {
        return Mage::getStoreConfig(self::XML_PATH_REFERAFRIEND_INVITE_TEMPLATE);
    }

    /**
     * Returns url for referral
     * @param String|Integer $inviteId Invite id
     * @return String
     */
    public function getInviteLink($inviteId = null)
    {
        if (!$inviteId){
            $inviteId = $this->getId();
        }
        if (Mage::app()->isSingleStoreMode()){
            return Mage::getUrl('referafriend/', array('_query' => array('ref'=>$inviteId,'_secure' => Mage::app()->getStore(true)->isCurrentlySecure())));
        }
        if (Mage::app()->getStore()->getCode() != 'default')
        {
            $url = Mage::getUrl('referafriend/', array('_store_to_url' => true, '_store' => Mage::app()->getStore()->getId(),'_secure' => Mage::app()->getStore(true)->isCurrentlySecure()));
        }
        else
        {
            $url = Mage::getUrl('referafriend/', array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure()));
        }
        return $url . (false === strpos($url, '?') ? '?' : '&') . 'ref=' . $inviteId;
    }
}