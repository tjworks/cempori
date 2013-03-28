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

class Belvg_Referralreward_YahooController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }
    
    protected function _getTimeStamp()
    {
        //return time();
        //echo date("d m Y h i s",Mage::getModel('core/date')->timestamp())."<br>".date("d m Y h i s",time())."<br>".date("d m Y h i s",time()); die;
        return Mage::getModel('core/date')->timestamp();// + (1 * 60 * 60);
    }

    public function saveAction()
    {
        $helper = Mage::helper('referralreward');
        if (isset($_REQUEST['openid_ax_value_email'])) {
            $tokenArray = $this->get_request_token(
                $helper->storeConfig('yahoo/appid'),
                $helper->storeConfig('yahoo/secret'),
                $helper->saveFriendsUrl('yahoo')
            );
            $tokenArray['openid_response_nonce'] = $_REQUEST['openid_response_nonce'];

            if (isset($tokenArray) && is_array($tokenArray)) {
                $_SESSION['accessToken'] = $tokenArray;
                $this->_redirectUrl('https://api.login.yahoo.com/oauth/v2/request_auth?oauth_token=' . $tokenArray['oauth_token']);
            }
        }

        if (isset($_REQUEST['oauth_verifier'])) {
            $tokenArray = $this->get_token(
                $helper->storeConfig('yahoo/appid'),
                $helper->storeConfig('yahoo/secret'),
                $_SESSION['accessToken'],
                array(
                    'oauth_token'    => $_REQUEST['oauth_token'],
                    'oauth_verifier' => $_REQUEST['oauth_verifier'],
                )
            );

            if (isset($tokenArray) && is_array($tokenArray)) {
                $contactsJson = $this->call_api(
                    $_SESSION['accessToken'],
                    $tokenArray,
                    $helper->storeConfig('yahoo/appid'),
                    $helper->storeConfig('yahoo/secret'),
                    $helper->saveFriendsUrl('yahoo')
                );
                //print_r($contactsJson); die;
                
				$names         = array();
				$emails        = array();
                $contactsArray = json_decode($contactsJson);
                $contactsArray = $contactsArray->contacts->contact;
                foreach($contactsArray AS $item) {
                    $field     = $item->fields[0];
                    $names[]   = $field->value;
                    $emails[]  = $field->value;
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
			}

            echo"<script>
                    window.opener.location.reload(true);
                    window.close();
                </script>";
        }
    }

    protected function get_token($consumer_key, $consumer_secret, $authToken, $token)
    {
        $url    = 'https://api.login.yahoo.com/oauth/v2/get_token';
        $params = array(
            'oauth_consumer_key'     => $consumer_key,
            'oauth_signature_method' => 'plaintext',
            'oauth_version'          => '1.0',
            'oauth_verifier'         => $token['oauth_verifier'],
            'oauth_token'            => $token['oauth_token'],
            'oauth_timestamp'        => $this->_getTimeStamp(),
            'oauth_nonce'            => $authToken['openid_response_nonce'],  
            'oauth_signature'        => ($consumer_secret . '&' . $authToken['oauth_token_secret']),
        );
        
        $curl   = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        parse_str($result, $accessToken);

        return $accessToken;
    }

    protected function get_request_token($consumer_key, $consumer_secret, $callback, $usePost=false, $useHmacSha1Sig=false, $passOAuthInHeader=false)
    {
        $url    = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
        $params = array(
            'oauth_nonce'            => $_REQUEST['openid_response_nonce'],  
            'oauth_timestamp'        => $this->_getTimeStamp(),
            'oauth_consumer_key'     => $consumer_key,
            'oauth_signature_method' => 'plaintext',
            'oauth_signature'        => ($consumer_secret . '&' . NULL),
            'oauth_version'          => '1.0',
            'xoauth_lang_pref'       => 'en-us',
            'oauth_callback'         => $callback,
        );

        $curl   = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        parse_str($result, $accessToken);

        return $accessToken;
    }

    protected function call_api($authToken, $token, $consumer_key, $consumer_secret, $callback)
    {
        $oauthHelp = New Yahoo_OauthHelper;
        $usePost   = false;

        $url = 'http://social.yahooapis.com/v1/user/' . $token['xoauth_yahoo_guid'] . '/contacts';
        $params['format']             = 'json';
        $params['view']               = 'compact';
        $params['oauth_version']      = '1.0';
        $params['oauth_nonce']        = mt_rand();
        $params['oauth_timestamp']    = $this->_getTimeStamp();
        $params['oauth_consumer_key'] = $consumer_key;
        $params['oauth_token']        = $token['oauth_token'];

        // compute hmac-sha1 signature and add it to the params list
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_signature'] =
        $oauthHelp->oauth_compute_hmac_sig('GET', $url, $params, $consumer_secret, $token['oauth_token_secret']);

        // Pass OAuth credentials in a separate header or in the query string
        $query_parameter_string = $oauthHelp->oauth_http_build_query($params, true);
        $header    = $oauthHelp->build_oauth_header($params, "yahooapis.com");
        $headers[] = $header;

        // GET the request
        $request_url = $url . ($query_parameter_string ? ('?' . $query_parameter_string) : '' );
        $response    = $this->do_get($request_url, 80, $headers);

        // extract successful response
        if (! empty($response)) {
            list($info, $header, $body) = $response;
            return $body;
        }

        return array();
    }

    /**
     * Do an HTTP GET
     * @param string $url
     * @param int $port (optional)
     * @param array $headers an array of HTTP headers (optional)
     * @return array ($info, $header, $response) on success or empty array on error.
     */
    public function do_get($url, $port=80, $headers=NULL)
    {
        $curl_opts = array(
            CURLOPT_URL  => $url,
            CURLOPT_PORT => $port,
            CURLOPT_POST => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        );

        if ($headers) {
            $curl_opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $response = $this->do_curl($curl_opts);
        if (!empty($response)) {
            return $response;
        }

        return array();
    }

    /**
     * Do an HTTP POST
     * @param string $url
     * @param int $port (optional)
     * @param array $headers an array of HTTP headers (optional)
     * @return array ($info, $header, $response) on success or empty array on error.
     */
    public function do_post($url, $postbody, $port=80, $headers=NULL)
    {
        $retarr = array();  // Return value

        $curl_opts = array(
            CURLOPT_URL  => $url,
            CURLOPT_PORT => $port,
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => $postbody,
            CURLOPT_RETURNTRANSFER => true
        );

        if ($headers) {
            $curl_opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $response = $this->do_curl($curl_opts);
        if (!empty($response)) {
            return $response;
        }

        return array();
    }

    /**
     * Make a curl call with given options.
     * @param array $curl_opts an array of options to curl
     * @return array ($info, $header, $response) on success or empty array on error.
     */
    public function do_curl($curl_opts)
    {
        $oauthHelp = New Yahoo_OauthHelper;
        $retarr    = array();

        if (!$curl_opts) {
            return $retarr;
        }

        // Open curl session
        $ch = curl_init();
        if (!$ch) {
            return $retarr;
        }

        curl_setopt_array($ch, $curl_opts);     // Set curl options that were passed in
        curl_setopt($ch, CURLOPT_HEADER, true); // Ensure that we receive full header
        $response = curl_exec($ch);             // Send the request and get the response

        // Check for errors
        if (curl_errno($ch)) {
            $errno  = curl_errno($ch);
            $errmsg = curl_error($ch);
            curl_close($ch);
            unset($ch);
            return $retarr;
        }

        // Get information about the transfer
        $info = curl_getinfo($ch);

        // Parse out header and body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header      = substr($response, 0, $header_size);
        $body        = substr($response, $header_size );

        curl_close($ch);
        unset($ch);

        // Set return value
        array_push($retarr, $info, $header, $body);

        return $retarr;
    }

}