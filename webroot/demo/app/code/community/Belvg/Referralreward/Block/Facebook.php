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

class Belvg_Referralreward_Block_Facebook extends Mage_Core_Block_Template{

    public function _construct(){
        parent::_construct();
        $helper = Mage::helper('referralreward/facebook');
        $this->setAppId($helper->getAppId());
        $this->setSecret($helper->getSecret());
    }


    /**
     * Return current facebook user id
     *
     * @return string
     */
    public function checkFbUser(){
        $cookie = $this->get_facebook_cookie($this->getAppId(), $this->getSecret());
        if (isset($cookie['access_token'])) {
            $me     = json_decode($this->getFbData('https://graph.facebook.com/me?access_token='.$cookie['access_token']));
            if (!empty($me->id)) {
                    return $me->id;
            }
        }

        return 0;
    }

    /**
     * @param string Facebook App ID/API Key
     * @param string Facebook App Secret
     * @return array
     */
    private function get_facebook_cookie($app_id, $app_secret){
        if(isset($_COOKIE['fbsr_'.$app_id]) && $_COOKIE['fbsr_'.$app_id] != '')
                return $this->get_new_facebook_cookie($app_id, $app_secret);
        else    return $this->get_old_facebook_cookie($app_id, $app_secret);
    }

    /**
     * @param string Facebook App ID/API Key
     * @param string Facebook App Secret
     * @return array
     */
    private function get_old_facebook_cookie($app_id, $app_secret){
        $args       = array();
        $payload    = '';
        if (isset($_COOKIE['fbsr_'.$app_id])) {
            parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
            ksort($args);
            foreach($args as $key => $value){
                if($key != 'sig')
                    $payload    .= $key.'='.$value;
            }
            if(isset($args['sig']) && md5($payload.$app_secret) != $args['sig'])
                return array();
            return $args;
        } else {
            return array();
        }
    }

    /**
     * @param string Facebook App ID/API Key
     * @param string Facebook App Secret
     * @return array
     */
    private function get_new_facebook_cookie($app_id, $app_secret){
        $signed_request             = $this->parse_signed_request($_COOKIE['fbsr_' . $app_id], $app_secret);
        $signed_request['uid']      = $signed_request['user_id'];
        if(!is_null($signed_request)){
            $access_token_response  = $this->getFbData("https://graph.facebook.com/oauth/access_token?client_id=".$app_id."&redirect_uri=&client_secret=".$app_secret."&code=".$signed_request['code']);
            parse_str($access_token_response);
            $signed_request['access_token'] = $access_token;
            $signed_request['expires']      = time() + $expires;
        }
        return $signed_request;
    }

    /**
     * @param string Facebook COOKIE[ fbsr_[App ID] ]
     * @param string Facebook App Secret
     * @return mixed
     */
    private function parse_signed_request($signed_request, $secret){
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);
        // decode the data
        $sig	= $this->base64_url_decode($encoded_sig);
        $data	= json_decode($this->base64_url_decode($payload), true);
        if(strtoupper($data['algorithm']) !== 'HMAC-SHA256'){
            error_log('Unknown algorithm. Expected HMAC-SHA256');
            return null;
        }
        // check sig
        $expected_sig   = hash_hmac('sha256', $payload, $secret, $raw = true);
        if($sig !== $expected_sig){
            error_log('Bad Signed JSON signature!');
            return null;
        }
        return $data;
    }

    /**
     * @param string
     * @return string
     */
    private function base64_url_decode($input){
        return base64_decode(strtr($input, '-_', '+/'));
    }
    
    /**
     * @param string URL
     * @return string
     */
    private function getFbData($url){
        $ch		= curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data	= curl_exec($ch);

        return $data;
    }


}