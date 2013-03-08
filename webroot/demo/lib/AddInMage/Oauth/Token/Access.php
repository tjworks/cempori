<?php

/**
 * 
 * LICENSE
 *
 * Copyright (c) 2005-2010, Zend Technologies USA, Inc. All rights reserved.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THISSOFTWARE, 
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   AddInMage
 * @package    AddInMage_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Access.php 20217 2010-01-12 16:01:57Z matthew $
 */

class AddInMage_Oauth_Token_Access extends AddInMage_Oauth_Token
{

	/**
	 * Cast to HTTP header
	 * 
	 * @param $url string
	 * @param $config AddInMage_Oauth_Config_ConfigInterface
	 * @param $customParams null|array
	 * @param $realm null|string
	 * @return string
	 */
	public function toHeader($url, AddInMage_Oauth_Config_ConfigInterface $config, array $customParams = null, $realm = null)
	{
		if (! Zend_Uri::check($url)) {

			throw new AddInMage_Oauth_Exception('\'' . $url . '\' is not a valid URI');}
		$params = $this->_httpUtility->assembleParams($url, $config, $customParams);
		return $this->_httpUtility->toAuthorizationHeader($params, $realm);
	}

	/**
	 * Cast to HTTP query string
	 * 
	 * @param $url mixed
	 * @param $config AddInMage_Oauth_Config_ConfigInterface
	 * @param $params null|array
	 * @return string
	 */
	public function toQueryString($url, AddInMage_Oauth_Config_ConfigInterface $config, array $params = null)
	{
		if (! Zend_Uri::check($url)) {

			throw new AddInMage_Oauth_Exception('\'' . $url . '\' is not a valid URI');}
		$params = $this->_httpUtility->assembleParams($url, $config, $params);
		return $this->_httpUtility->toEncodedQueryString($params);
	}

	/**
	 * Get OAuth client
	 * 
	 * @param $oauthOptions array
	 * @param $uri null|string
	 * @param $config null|array|Zend_Config
	 * @param $excludeCustomParamsFromHeader bool
	 * @return AddInMage_Oauth_Client
	 */
	public function getHttpClient(array $oauthOptions, $uri = null, $config = null, $excludeCustomParamsFromHeader = true)
	{
		$client = new AddInMage_Oauth_Client($oauthOptions, $uri, $config, $excludeCustomParamsFromHeader);
		$client->setToken($this);
		return $client;
	}
}
