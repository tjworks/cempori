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
 * @version    $Id: Consumer.php 23170 2010-10-19 18:29:24Z mabe $
 */

class AddInMage_Oauth_Consumer extends AddInMage_Oauth
{
	public $switcheroo = false; // replace later when this works
	
	/**
	 * Request Token retrieved from OAuth Provider
	 * 
	 * @var AddInMage_Oauth_Token_Request
	 */
	protected $_requestToken = null;
	
	/**
	 * Access token retrieved from OAuth Provider
	 * 
	 * @var AddInMage_Oauth_Token_Access
	 */
	protected $_accessToken = null;
	
	/**
	 *
	 * @var AddInMage_Oauth_Config
	 */
	protected $_config = null;

	/**
	 * Constructor; create a new object with an optional array|Zend_Config
	 * instance containing initialising options.
	 * 
	 * @param $options array|Zend_Config
	 * @return void
	 */
	public function __construct($options = null)
	{
		$this->_config = new AddInMage_Oauth_Config();
		if ($options !== null) {
			if ($options instanceof Zend_Config) {
				$options = $options->toArray();
			}
			$this->_config->setOptions($options);
		}
	}

	/**
	 * Attempts to retrieve a Request Token from an OAuth Provider which is
	 * later exchanged for an authorized Access Token used to access the
	 * protected resources exposed by a web service API.
	 * 
	 * @param $customServiceParameters null|array Non-OAuth Provider-specified parameters
	 * @param $httpMethod null|string
	 * @param $request null|AddInMage_Oauth_Http_RequestToken
	 * @return AddInMage_Oauth_Token_Request
	 */
	public function getRequestToken(array $customServiceParameters = null, $httpMethod = null, AddInMage_Oauth_Http_RequestToken $request = null)
	{
		if ($request === null) {
			$request = new AddInMage_Oauth_Http_RequestToken($this, $customServiceParameters);
		} elseif ($customServiceParameters !== null) {
			$request->setParameters($customServiceParameters);
		}
		if ($httpMethod !== null) {
			$request->setMethod($httpMethod);
		} else {
			$request->setMethod($this->getRequestMethod());
		}
		$this->_requestToken = $request->execute();
		return $this->_requestToken;
	}

	/**
	 * After a Request Token is retrieved, the user may be redirected to the
	 * OAuth Provider to authorize the application's access to their
	 * protected resources - the redirect URL being provided by this method.
	 * Once the user has authorized the application for access, they are
	 * redirected back to the application which can now exchange the previous
	 * Request Token for a fully authorized Access Token.
	 * 
	 * @param $customServiceParameters null|array
	 * @param $token null|AddInMage_Oauth_Token_Request
	 * @param $redirect null|AddInMage_Oauth_Http_UserAuthorization
	 * @return string
	 */
	public function getRedirectUrl(array $customServiceParameters = null, AddInMage_Oauth_Token_Request $token = null, AddInMage_Oauth_Http_UserAuthorization $redirect = null)
	{
		if ($redirect === null) {
			$redirect = new AddInMage_Oauth_Http_UserAuthorization($this, $customServiceParameters);
		} elseif ($customServiceParameters !== null) {
			$redirect->setParameters($customServiceParameters);
		}
		if ($token !== null) {
			$this->_requestToken = $token;
		}
		return $redirect->getUrl();
	}

	/**
	 * Rather than retrieve a redirect URL for use, e.g.
	 * from a controller,
	 * one may perform an immediate redirect.
	 * Sends headers and exit()s on completion.
	 * 
	 * @param $customServiceParameters null|array
	 * @param $token null|AddInMage_Oauth_Token_Request
	 * @param $request null|AddInMage_Oauth_Http_UserAuthorization
	 * @return void
	 */
	public function redirect(array $customServiceParameters = null, AddInMage_Oauth_Token_Request $token = null, AddInMage_Oauth_Http_UserAuthorization $request = null)
	{
		if ($token instanceof AddInMage_Oauth_Http_UserAuthorization) {
			$request = $token;
			$token = null;
		}
		$redirectUrl = $this->getRedirectUrl($customServiceParameters, $token, $request);
		header('Location: ' . $redirectUrl);
		exit(1);
	}

	/**
	 * Retrieve an Access Token in exchange for a previously received/authorized
	 * Request Token.
	 * 
	 * @param $queryData array GET data returned in user's redirect from Provider
	 * @param AddInMage_Oauth_Token_Request Request Token information
	 * @param $httpMethod string
	 * @param $request AddInMage_Oauth_Http_AccessToken
	 * @return AddInMage_Oauth_Token_Access
	 * @throws AddInMage_Oauth_Exception on invalid authorization token, non-matching response authorization token, or unprovided authorization token
	 */
	public function getAccessToken($queryData, AddInMage_Oauth_Token_Request $token, $httpMethod = null, AddInMage_Oauth_Http_AccessToken $request = null)
	{
		$authorizedToken = new AddInMage_Oauth_Token_AuthorizedRequest($queryData);
		if (! $authorizedToken->isValid()) {throw new AddInMage_Oauth_Exception('Response from Service Provider is not a valid authorized request token');}
		if ($request === null) {
			$request = new AddInMage_Oauth_Http_AccessToken($this);
		}
		
		// OAuth 1.0a Verifier
		if ($authorizedToken->getParam('oauth_verifier') !== null) {
			$params = array_merge($request->getParameters(), array ('oauth_verifier' => $authorizedToken->getParam('oauth_verifier')));
			$request->setParameters($params);
		}
		if ($httpMethod !== null) {
			$request->setMethod($httpMethod);
		} else {
			$request->setMethod($this->getRequestMethod());
		}
		if (isset($token)) {
			if ($authorizedToken->getToken() !== $token->getToken()) {throw new AddInMage_Oauth_Exception('Authorized token from Service Provider does not match' . ' supplied Request Token details');}
		} else {
			throw new AddInMage_Oauth_Exception('Request token must be passed to method');
		}
		$this->_requestToken = $token;
		$this->_accessToken = $request->execute();
		return $this->_accessToken;
	}

	/**
	 * Return whatever the last Request Token retrieved was while using the
	 * current Consumer instance.
	 * 
	 * @return AddInMage_Oauth_Token_Request
	 */
	public function getLastRequestToken()
	{
		return $this->_requestToken;
	}

	/**
	 * Return whatever the last Access Token retrieved was while using the
	 * current Consumer instance.
	 * 
	 * @return AddInMage_Oauth_Token_Access
	 */
	public function getLastAccessToken()
	{
		return $this->_accessToken;
	}

	/**
	 * Alias to self::getLastAccessToken()
	 * 
	 * @return AddInMage_Oauth_Token_Access
	 */
	public function getToken()
	{
		return $this->_accessToken;
	}

	/**
	 * Simple Proxy to the current AddInMage_Oauth_Config method.
	 * It's that instance
	 * which holds all configuration methods and values this object also presents
	 * as it's API.
	 * 
	 * @param $method string
	 * @param $args array
	 * @return mixed
	 * @throws AddInMage_Oauth_Exception if method does not exist in config object
	 */
	public function __call($method, array $args)
	{
		if (! method_exists($this->_config, $method)) {throw new AddInMage_Oauth_Exception('Method does not exist: ' . $method);}
		return call_user_func_array(array ($this->_config, $method), $args);
	}
}
