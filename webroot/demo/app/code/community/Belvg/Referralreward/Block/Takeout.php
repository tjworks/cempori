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

class Belvg_Referralreward_Block_Takeout extends Mage_Core_Block_Template{

    /**
     *
     *
     * @return
     */
    protected function getProviders() {
        $helper    = Mage::helper('referralreward');
        $providers = array();

        // GOOGLE
        $scope     = 'https://www.google.com/m8/feeds/';
        if ($helper->storeConfig('gmail/enabled')) {
            $providers['gmail']['appid']  = $helper->storeConfig('gmail/appid');
            $providers['gmail']['secret'] = $helper->storeConfig('gmail/secret');
            $providers['gmail']['url']    = 'https://accounts.google.com/o/oauth2/auth' .
                                            '?client_id=' . $providers['gmail']['appid'] .
                                            '&redirect_uri=' . $helper->saveFriendsUrl('gmail') .
                                            '&scope=' . $scope .
                                            '&response_type=code';
        }

        // YAHOO
        if ($helper->storeConfig('yahoo/enabled')) {
            $providers['yahoo']['appid']  = $helper->storeConfig('yahoo/appid');
            $providers['yahoo']['secret'] = $helper->storeConfig('yahoo/secret');
            $url = '';
            try {
                $openid = new Yahoo_OpenID;
                if(!$openid->mode) {
                    $service_url       = 'me.yahoo.com/';
                    $openid->required  = array('namePerson/friendly', 'contact/email');
                    $openid->optional  = array('namePerson/first');                    
                    $openid->identity  = $service_url;
                    $openid->returnUrl = $helper->saveFriendsUrl('yahoo');
                    $url = $openid->authUrl();
                } elseif($openid->mode == 'cancel') {
                    echo 'User has canceled authentication!';
                } else {
                    echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';                     
                    $this->userLog($openid->getAttributes());
                }
            } catch(ErrorException $e) {
                //echo $e->getMessage();
            }
            $providers['yahoo']['url'] = $url;
        }

        // HOTMAIL / LIVE
        if ($helper->storeConfig('hotmail/enabled')) {
            $providers['hotmail']['appid']  = $helper->storeConfig('hotmail/appid');
            $providers['hotmail']['secret'] = $helper->storeConfig('hotmail/secret');
            $providers['hotmail']['url']    = 'https://consent.live.com/Delegation.aspx' .
											  '?RU=' . urlencode($helper->saveFriendsUrl('hotmail')) .
											  '&ps=Contacts.View' .
											  '&pl=' . urlencode($helper->policyUrl());
        }

        return $providers;
    }

}