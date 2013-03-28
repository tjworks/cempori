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

class Belvg_Referralreward_GmailController extends Mage_Core_Controller_Front_Action
{
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
		//Oauth 2.0: exchange token for session token so multiple calls can be made to api
		if(isset($_REQUEST['code'])){
			$_SESSION['accessToken'] = $this->get_oauth2_token($_REQUEST['code']);
			if(isset($_SESSION['accessToken'])){
				$contactsXml = $this->call_api($_SESSION['accessToken'], "https://www.google.com/m8/feeds/contacts/default/full/?max-results=9999999");
				$parser      = xml_parser_create();
				xml_parse_into_struct($parser, $contactsXml, $contactsArray, $index);
				xml_parser_free($parser);

				$names   = array();
				$emails  = array();
				$isName  = FALSE;
				$isEmail = TRUE;
				foreach ($contactsArray AS $key => $item) {
					if (isset($item['tag']) && $item['tag'] == 'TITLE' && isset($item['value'])) {
						$names[] = $item['value'];
						if ($isName) {
							$emails[] = '';
						}

						$isName  = TRUE;
						$isEmail = FALSE;
					}

					if (isset($item['tag']) && $item['tag'] == 'GD:EMAIL' && isset($item['attributes']['ADDRESS'])) {
						$emails[] = $item['attributes']['ADDRESS'];
						if ($isEmail) {
							$names[] = '';
						}

						$isName  = FALSE;
						$isEmail = TRUE;
					}
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
			}else{
				//echo"<p class='errorMessage'>Google Error</p>";
            }

            echo"<script>
                    window.opener.location.reload(true);
                    window.close();
                </script>";
		}
	}

	protected function get_oauth2_token($code){
		$helper = Mage::helper('referralreward');

		$oauth2token_url  = "https://accounts.google.com/o/oauth2/token";
		$clienttoken_post = array(
			"code"			=> $code,
			"client_id"		=> $helper->storeConfig('gmail/appid'),
			"client_secret"	=> $helper->storeConfig('gmail/secret'),
			"redirect_uri"	=> $helper->saveFriendsUrl('gmail'),
			"grant_type"	=> "authorization_code",
		);

		$curl = curl_init($oauth2token_url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $clienttoken_post);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$json_response = curl_exec($curl);
		curl_close($curl);
		$authObj = json_decode($json_response);

		if(isset($authObj->refresh_token)){
			global $refreshToken;
			$refreshToken = $authObj->refresh_token;
		}
		$accessToken = $authObj->access_token;

		return $accessToken;
	}

	protected function call_api($accessToken,$url){

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);	
		$curlheader[0]	= "Authorization: Bearer " . $accessToken;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheader);
		$json_response	= curl_exec($curl);
		curl_close($curl);

		return $json_response;
	}



}