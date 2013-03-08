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
 * @version    $Id: Config.php 22662 2010-07-24 17:37:36Z mabe $
 */

class AddInMage_Oauth_Config implements AddInMage_Oauth_Config_ConfigInterface
{
	/**
	 * Signature method used when signing all parameters for an HTTP request
	 * 
	 * @var string
	 */
	protected $_signatureMethod = 'HMAC-SHA1';
	
	/**
	 * Three request schemes are defined by OAuth, of which passing
	 * all OAuth parameters by Header is preferred.
	 * The other two are
	 * POST Body and Query String.
	 * 
	 * @var string
	 */
	protected $_requestScheme = AddInMage_Oauth::REQUEST_SCHEME_HEADER;
	
	/**
	 * Preferred request Method - one of GET or POST - which AddInMage_Oauth
	 * will enforce as standard throughout the library.
	 * Generally a default
	 * of POST works fine unless a Provider specifically requires otherwise.
	 * 
	 * @var string
	 */
	protected $_requestMethod = AddInMage_Oauth::POST;
	
	/**
	 * OAuth Version; This defaults to 1.0 - Must not be changed!
	 * 
	 * @var string
	 */
	protected $_version = '1.0';
	
	protected $_xoauthLangPref = 'en-US';
	
	/**
	 * This optional value is used to define where the user is redirected to
	 * after authorizing a Request Token from an OAuth Providers website.
	 * It's optional since a Provider may ask for this to be defined in advance
	 * when registering a new application for a Consumer Key.
	 * 
	 * @var string
	 */
	protected $_callbackUrl = null;
	
	/**
	 * The URL root to append default OAuth endpoint paths.
	 * 
	 * @var string
	 */
	protected $_siteUrl = null;
	
	/**
	 * The URL to which requests for a Request Token should be directed.
	 * When absent, assumed siteUrl+'/request_token'
	 * 
	 * @var string
	 */
	protected $_requestTokenUrl = null;
	
	/**
	 * The URL to which requests for an Access Token should be directed.
	 * When absent, assumed siteUrl+'/access_token'
	 * 
	 * @var string
	 */
	protected $_accessTokenUrl = null;
	
	/**
	 * The URL to which users should be redirected to authorize a Request Token.
	 * When absent, assumed siteUrl+'/authorize'
	 * 
	 * @var string
	 */
	protected $_authorizeUrl = null;
	
	/**
	 * An OAuth application's Consumer Key.
	 * 
	 * @var string
	 */
	protected $_consumerKey = null;
	
	/**
	 * Every Consumer Key has a Consumer Secret unless you're in RSA-land.
	 * 
	 * @var string
	 */
	protected $_consumerSecret = null;
	
	/**
	 * If relevant, a PEM encoded RSA private key encapsulated as a
	 * Zend_Crypt_Rsa Key
	 * 
	 * @var Zend_Crypt_Rsa_Key_Private
	 */
	protected $_rsaPrivateKey = null;
	
	/**
	 * If relevant, a PEM encoded RSA public key encapsulated as a
	 * Zend_Crypt_Rsa Key
	 * 
	 * @var Zend_Crypt_Rsa_Key_Public
	 */
	protected $_rsaPublicKey = null;
	
	/**
	 * Generally this will nearly always be an Access Token represented as a
	 * AddInMage_Oauth_Token_Access object.
	 * 
	 * @var AddInMage_Oauth_Token
	 */
	protected $_token = null;

	/**
	 * Constructor; create a new object with an optional array|Zend_Config
	 * instance containing initialising options.
	 * 
	 * @param $options array|Zend_Config
	 * @return void
	 */
	public function __construct($options = null)
	{
		if ($options !== null) {
			if ($options instanceof Zend_Config) {
				$options = $options->toArray();
			}
			$this->setOptions($options);
		}
	}

	/**
	 * Parse option array or Zend_Config instance and setup options using their
	 * relevant mutators.
	 * 
	 * @param $options array|Zend_Config
	 * @return AddInMage_Oauth_Config
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $value) {
			switch ($key) {
			case 'consumerKey':
			$this->setConsumerKey($value);
			break;
			case 'consumerSecret':
			$this->setConsumerSecret($value);
			break;
			case 'signatureMethod':
			$this->setSignatureMethod($value);
			break;
			case 'version':
			$this->setVersion($value);
			break;
			case 'xoauthLangPref':
			$this->setXoauthLangPref($value);
			break;
			case 'callbackUrl':
			$this->setCallbackUrl($value);
			break;
			case 'siteUrl':
			$this->setSiteUrl($value);
			break;
			case 'requestTokenUrl':
			$this->setRequestTokenUrl($value);
			break;
			case 'accessTokenUrl':
			$this->setAccessTokenUrl($value);
			break;
			case 'userAuthorizationUrl':
			$this->setUserAuthorizationUrl($value);
			break;
			case 'authorizeUrl':
			$this->setAuthorizeUrl($value);
			break;
			case 'requestMethod':
			$this->setRequestMethod($value);
			break;
			case 'rsaPrivateKey':
			$this->setRsaPrivateKey($value);
			break;
			case 'rsaPublicKey':
			$this->setRsaPublicKey($value);
			break;
			}
		}
		if (isset($options ['requestScheme'])) {
			$this->setRequestScheme($options ['requestScheme']);
		}
		
		return $this;
	}

	/**
	 * Set consumer key
	 * 
	 * @param $key string
	 * @return AddInMage_Oauth_Config
	 */
	public function setConsumerKey($key)
	{
		$this->_consumerKey = $key;
		return $this;
	}

	/**
	 * Get consumer key
	 * 
	 * @return string
	 */
	public function getConsumerKey()
	{
		return $this->_consumerKey;
	}

	/**
	 * Set consumer secret
	 * 
	 * @param $secret string
	 * @return AddInMage_Oauth_Config
	 */
	public function setConsumerSecret($secret)
	{
		$this->_consumerSecret = $secret;
		return $this;
	}

	/**
	 * Get consumer secret
	 * Returns RSA private key if set; otherwise, returns any previously set
	 * consumer secret.
	 * 
	 * @return string
	 */
	public function getConsumerSecret()
	{
		if ($this->_rsaPrivateKey !== null) {return $this->_rsaPrivateKey;}
		return $this->_consumerSecret;
	}

	/**
	 * Set signature method
	 * 
	 * @param $method string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception if unsupported signature method specified
	 */
	public function setSignatureMethod($method)
	{
		$method = strtoupper($method);
		if (! in_array($method, array ('HMAC-SHA1', 'HMAC-SHA256', 'RSA-SHA1', 'PLAINTEXT'))) {

			throw new AddInMage_Oauth_Exception('Unsupported signature method: ' . $method . '. Supported are HMAC-SHA1, RSA-SHA1, PLAINTEXT and HMAC-SHA256');}
		$this->_signatureMethod = $method;
		;
		return $this;
	}

	/**
	 * Get signature method
	 * 
	 * @return string
	 */
	public function getSignatureMethod()
	{
		return $this->_signatureMethod;
	}

	/**
	 * Set request scheme
	 * 
	 * @param $scheme string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception if invalid scheme specified, or if POSTBODY set when request method of GET is specified
	 */
	public function setRequestScheme($scheme)
	{
		$scheme = strtolower($scheme);
		if (! in_array($scheme, array (AddInMage_Oauth::REQUEST_SCHEME_HEADER, AddInMage_Oauth::REQUEST_SCHEME_POSTBODY, AddInMage_Oauth::REQUEST_SCHEME_QUERYSTRING))) {

			throw new AddInMage_Oauth_Exception('\'' . $scheme . '\' is an unsupported request scheme');}
		if ($scheme == AddInMage_Oauth::REQUEST_SCHEME_POSTBODY && $this->getRequestMethod() == AddInMage_Oauth::GET) {

			throw new AddInMage_Oauth_Exception('Cannot set POSTBODY request method if HTTP method set to GET');}
		$this->_requestScheme = $scheme;
		return $this;
	}

	/**
	 * Get request scheme
	 * 
	 * @return string
	 */
	public function getRequestScheme()
	{
		return $this->_requestScheme;
	}

	/**
	 * Set version
	 * 
	 * @param $version string
	 * @return AddInMage_Oauth_Config
	 */
	public function setVersion($version)
	{
		$this->_version = $version;
		return $this;
	}

	/**
	 * Get version
	 * 
	 * @return string
	 */
	public function getVersion()
	{
		return $this->_version;
	}

	public function setXoauthLangPref($lang)
	{
		$this->_xoauthLangPref = $lang;
		return $this;
	}

	public function getXoauthLangPref()
	{
		return $this->_xoauthLangPref;
	}

	/**
	 * Set callback URL
	 * 
	 * @param $url string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception for invalid URLs
	 */
	public function setCallbackUrl($url)
	{
		if (! Zend_Uri::check($url)) {

			throw new AddInMage_Oauth_Exception('\'' . $url . '\' is not a valid URI');}
		$this->_callbackUrl = $url;
		return $this;
	}

	/**
	 * Get callback URL
	 * 
	 * @return string
	 */
	public function getCallbackUrl()
	{
		return $this->_callbackUrl;
	}

	/**
	 * Set site URL
	 * 
	 * @param $url string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception for invalid URLs
	 */
	public function setSiteUrl($url)
	{
		if (! Zend_Uri::check($url)) {

			throw new AddInMage_Oauth_Exception('\'' . $url . '\' is not a valid URI');}
		$this->_siteUrl = $url;
		return $this;
	}

	/**
	 * Get site URL
	 * 
	 * @return string
	 */
	public function getSiteUrl()
	{
		return $this->_siteUrl;
	}

	/**
	 * Set request token URL
	 * 
	 * @param $url string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception for invalid URLs
	 */
	public function setRequestTokenUrl($url)
	{
		if (! Zend_Uri::check($url)) {

			throw new AddInMage_Oauth_Exception('\'' . $url . '\' is not a valid URI');}
		$this->_requestTokenUrl = rtrim($url, '/');
		return $this;
	}

	/**
	 * Get request token URL
	 * If no request token URL has been set, but a site URL has, returns the
	 * site URL with the string "/request_token" appended.
	 * 
	 * @return string
	 */
	public function getRequestTokenUrl()
	{
		if (! $this->_requestTokenUrl && $this->_siteUrl) {return $this->_siteUrl . '/request_token';}
		return $this->_requestTokenUrl;
	}

	/**
	 * Set access token URL
	 * 
	 * @param $url string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception for invalid URLs
	 */
	public function setAccessTokenUrl($url)
	{
		if (! Zend_Uri::check($url)) {

			throw new AddInMage_Oauth_Exception('\'' . $url . '\' is not a valid URI');}
		$this->_accessTokenUrl = rtrim($url, '/');
		return $this;
	}

	/**
	 * Get access token URL
	 * If no access token URL has been set, but a site URL has, returns the
	 * site URL with the string "/access_token" appended.
	 * 
	 * @return string
	 */
	public function getAccessTokenUrl()
	{
		if (! $this->_accessTokenUrl && $this->_siteUrl) {return $this->_siteUrl . '/access_token';}
		return $this->_accessTokenUrl;
	}

	/**
	 * Set user authorization URL
	 * 
	 * @param $url string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception for invalid URLs
	 */
	public function setUserAuthorizationUrl($url)
	{
		return $this->setAuthorizeUrl($url);
	}

	/**
	 * Set authorization URL
	 * 
	 * @param $url string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception for invalid URLs
	 */
	public function setAuthorizeUrl($url)
	{
		if (! Zend_Uri::check($url)) {

			throw new AddInMage_Oauth_Exception('\'' . $url . '\' is not a valid URI');}
		$this->_authorizeUrl = rtrim($url, '/');
		return $this;
	}

	/**
	 * Get user authorization URL
	 * 
	 * @return string
	 */
	public function getUserAuthorizationUrl()
	{
		return $this->getAuthorizeUrl();
	}

	/**
	 * Get authorization URL
	 * If no authorization URL has been set, but a site URL has, returns the
	 * site URL with the string "/authorize" appended.
	 * 
	 * @return string
	 */
	public function getAuthorizeUrl()
	{
		if (! $this->_authorizeUrl && $this->_siteUrl) {return $this->_siteUrl . '/authorize';}
		return $this->_authorizeUrl;
	}

	/**
	 * Set request method
	 * 
	 * @param $method string
	 * @return AddInMage_Oauth_Config
	 * @throws AddInMage_Oauth_Exception for invalid request methods
	 */
	public function setRequestMethod($method)
	{
		$method = strtoupper($method);
		if (! in_array($method, array (AddInMage_Oauth::GET, AddInMage_Oauth::POST, AddInMage_Oauth::PUT, AddInMage_Oauth::DELETE))) {

			throw new AddInMage_Oauth_Exception('Invalid method: ' . $method);}
		$this->_requestMethod = $method;
		return $this;
	}

	/**
	 * Get request method
	 * 
	 * @return string
	 */
	public function getRequestMethod()
	{
		return $this->_requestMethod;
	}

	/**
	 * Set RSA public key
	 * 
	 * @param $key Zend_Crypt_Rsa_Key_Public
	 * @return AddInMage_Oauth_Config
	 */
	public function setRsaPublicKey(Zend_Crypt_Rsa_Key_Public $key)
	{
		$this->_rsaPublicKey = $key;
		return $this;
	}

	/**
	 * Get RSA public key
	 * 
	 * @return Zend_Crypt_Rsa_Key_Public
	 */
	public function getRsaPublicKey()
	{
		return $this->_rsaPublicKey;
	}

	/**
	 * Set RSA private key
	 * 
	 * @param $key Zend_Crypt_Rsa_Key_Private
	 * @return AddInMage_Oauth_Config
	 */
	public function setRsaPrivateKey(Zend_Crypt_Rsa_Key_Private $key)
	{
		$this->_rsaPrivateKey = $key;
		return $this;
	}

	/**
	 * Get RSA private key
	 * 
	 * @return Zend_Crypt_Rsa_Key_Private
	 */
	public function getRsaPrivateKey()
	{
		return $this->_rsaPrivateKey;
	}

	/**
	 * Set OAuth token
	 * 
	 * @param $token AddInMage_Oauth_Token
	 * @return AddInMage_Oauth_Config
	 */
	public function setToken(AddInMage_Oauth_Token $token)
	{
		$this->_token = $token;
		return $this;
	}

	/**
	 * Get OAuth token
	 * 
	 * @return AddInMage_Oauth_Token
	 */
	public function getToken()
	{
		return $this->_token;
	}
}
