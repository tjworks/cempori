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

class Belvg_Referralreward_HotmailController extends Mage_Core_Controller_Front_Action
{
    /**
     * Imported contacts
     * 
     * @var array
     * @access private
     */
    private $contacts = array();

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }

    public function saveAction()
    {
        $this->fetchWLContacts();

        $names         = array();
        $emails        = array();

        foreach($this->contacts AS $item) {
            $names[]   = $item->name;
            $emails[]  = $item->email;
        }

		$customer_id = (int)$this->_getSession()->getId();
        $count       = Mage::getModel('referralreward/friends')->saveFriends($customer_id, $emails, $names);
        if ($count['enable']) {
            if ($count['enable']>1) {
                $this->_getSession()->addSuccess($this->__('%s have been added', $count['enable']));
            } else {
                $this->_getSession()->addSuccess($this->__('%s has been added', $count['enable']));
            }
        }

        if ($count['disable']) {
            $this->_getSession()->addSuccess($this->__('%s already registered', $count['disable']));
        }

        if ($count['enable']==0 && $count['disable']==0) {
            $this->_getSession()->addError($this->__("no emails were added"));
        }

        echo"<script>
                window.opener.location.reload(true);
                window.close();
            </script>";
    }
    
    /**
     * Fetch Hotmail contacts 
     *
     * @access public
     * @return void
     */
    public function fetchWLContacts () {
        $helper         = Mage::helper('referralreward');
        $this->contacts = array ();
        $settings = array(
            'appid'             => $helper->storeConfig('hotmail/appid'),
            'secret'            => $helper->storeConfig('hotmail/secret'),
            'securityalgorithm' => 'wsignin1.0',
            'returnurl'         => $helper->saveFriendsUrl('hotmail'),
            'policyurl'         => $helper->policyUrl(),
        );
        $WLL  = Hotmail_Windowslivelogin::initFromArray($settings);
        $conn = $WLL->processConsent($_REQUEST);

        if ($conn != null) {
            $delegationToken = $conn->getDelegationToken();
            $locationId64    = $this->hexaTo64SignedDecimal($conn->getLocationID(), 16, 10);
            $WLLContactsURL  = 'https://livecontacts.services.live.com/users/@C@' . $locationId64 . '/rest/livecontacts';
            $headers         = array('Authorization: DelegatedToken dt="' . $delegationToken . '"');
          
            $cURLHandle = curl_init();
            curl_setopt($cURLHandle, CURLOPT_URL, $WLLContactsURL);
            curl_setopt($cURLHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cURLHandle, CURLOPT_TIMEOUT, 60);
            curl_setopt($cURLHandle, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($cURLHandle, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($cURLHandle);
            curl_close($cURLHandle);
            $contacts = new SimpleXMLElement($response);

            if (!empty($contacts->Contacts->Contact)) {
                foreach ($contacts->Contacts->Contact as $contact) {
                    $name   = strip_tags($contact->Profiles->Personal->FirstName) . " " . strip_tags($contact->Profiles->Personal->LastName);
                    $insert = (object) array ('name' => $name, 'email' => strip_tags($contact->Emails->Email->Address));
                    array_push($this->contacts, $insert);
                }
            }
        }
    }

    /**
     * Convert to 64Decimal
     *
     * @access private
     * @return string
     */  
    private function hexaTo64SignedDecimal($hexa) {
        $bin = $this->extended_base_convert($hexa, 16, 2);
        if (64 === strlen($bin) and 1 == $bin[0]) {
            $inv_bin = strtr($bin, '01', '10');
            $i = 63;
            while (0 !== $i) {
                if (0 == $inv_bin[$i]) {
                    $inv_bin[$i] = 1;
                    $i = 0;
                } else {
                    $inv_bin[$i] = 0;
                    $i--;
                }
            }
            return '-' . $this->extended_base_convert($inv_bin, 2, 10);
        } else {
            return $this->extended_base_convert($hexa, 16, 10);
        }
    }

    /**
     * Extended version of PHP base_convert
     *
     * @access private
     * @return string
     */
    private function extended_base_convert($numstring, $frombase, $tobase) {
        $chars    = "0123456789abcdefghijklmnopqrstuvwxyz";
        $tostring = substr($chars, 0, $tobase);

        $length   = strlen($numstring);
        $result   = '';
        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($chars, $numstring{$i});
        }

        do {
            $divide = 0;
            $newlen = 0;
            for ($i = 0; $i < $length; $i++) {
                $divide = $divide * $frombase + $number[$i];
                if ($divide >= $tobase) {
                    $number[$newlen++] = (int)($divide / $tobase);
                    $divide = $divide % $tobase;
                } elseif ($newlen > 0) {
                    $number[$newlen++] = 0;
                }
            }

            $length = $newlen;
            $result = $tostring{$divide} . $result;
        } while ($newlen != 0);

        return $result;
    }

}