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
 * @package    AddInMage_Oauth2
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Oauth.php 21071 2010-02-16 14:35:00Z padraic $
 */

class AddInMage_Oauth2
{
	
	/**
	 *
	 * @var string
	 */
	protected $_verificationCode = null;
	
	/**
	 *
	 * @var mixed
	 */
	protected $_config = null;
	
	/**
	 *
	 * @var <type>
	 */
	protected static $_localHttpClient = null;

	public function __construct($options = null)
	{
		$this->_config = new AddInMage_Oauth2_Config();
		if (! is_null($options)) {
			if ($options instanceof Zend_Config) {
				$options = $options->toArray();
			}
			$this->_config->setOptions($options);
		}
	}

	/**
	 * redirecting the end user's user-agent to the authorization page
	 * adter accepting or denying user will be redirected to callback page
	 * if user accepts url will include a parameter "code" and optional a parameter "state"
	 * if user denies url will include a parameter "error" set to "user_denied"
	 * and an optional parameter "state"
	 * - type REQUIRED (The type of user delegation authorization flow)
	 * - client_id REQUIRED (The client identifier)
	 * - redirect_uri REQUIRED (An absolute URI to which the authorization server will
	 * redirect the user-agent to when the end user authorization step is completed)
	 * - state OPTIONAL (An opaque value used by the client to maintain state between
	 * the request and callback)
	 * - immediate OPTIONAL (The parameter value must be set to "true" or "false"
	 * (case sensitive). If set to "true", the authorization server MUST NOT prompt
	 * the end user to authenticate or approve access. Instead, the authorization
	 * server attempts to establish the end user's identity via other means (e.g.
	 * browser cookies) and checks if the end user has previously approved an
	 * identical access request by the same client and if that access grant is
	 * still active. If the authorization server does not support an immediate
	 * check or if it is unable to establish the end user's identity or approval
	 * status, it MUST deny the request without prompting the end user. Defaults
	 * to "false" if omitted.)
	 * 
	 * @param $authorizeEndPoint <type>
	 * @param $callbackUrl <type>
	 * @param $clientId <type>
	 * @param $type <type>
	 * @param $state <type>
	 * @param $immediate <type>
	 * @param $requestedRights <type>
	 * @param $responseType <type>
	 */
	public function authorizationRedirect($authorizeEndPoint = null, $callbackUrl = null, $clientId = null, $type = null, $state = null, $immediate = null, $requestedRights = null, $responseType = null, $display = null)
	{
		if (is_null($authorizeEndPoint)) $authorizeEndPoint = $this->_config->getAuthorizeEndPoint();
		if (is_null($callbackUrl)) $callbackUrl = $this->_config->getCallbackUrl();
		if (is_null($clientId)) $clientId = $this->_config->getClientId();
		if (is_null($type)) $type = $this->_config->getType();
		if (is_null($state)) $state = $this->_config->getState();
		if (is_null($immediate)) $immediate = $this->_config->getImmediate();
		if (is_null($requestedRights)) $requestedRights = $this->_config->getRequestedRights();
		if (is_null($responseType)) $responseType = $this->_config->getResponseType();
		if (is_null($display)) $display = $this->_config->getDisplay();
		
		$requiredValuesArray = array ('authorizeEndPoint', 'callbackUrl', 'clientId', 'type');
		
		// throw exception if one of the required values is missing
		foreach ($requiredValuesArray as $requiredValue) {
			if (is_null($$requiredValue)) {

				throw new AddInMage_Oauth2_Exception('value ' . $requiredValue . ' is empty, pass ' . ucfirst($requiredValue) . ' as parameter when calling the ' . __METHOD__ . ' method or add it to the options array you pass when creating an instance of the ' . get_class($this) . ' class');}
		}
		
		// convert rights array to string
		$scope = '';
		if (is_array($requestedRights)) {
			$requestedRightsString = implode(',', $requestedRights);
			$scope = $requestedRightsString;
		} else {
			$scope = $requestedRights;
		}
		
		// construct request url with required values
		if (substr($authorizeEndPoint, - 1) == '/') $authorizeEndPoint = substr($authorizeEndPoint, 0, strlen($authorizeEndPoint) - 1);
		$requestUrl = $authorizeEndPoint . '?client_id=' . $clientId . '&redirect_uri=' . $callbackUrl . '&type=' . $type;
		
		// add optional values to request url
		if (! empty($scope)) $requestUrl .= '&scope=' . $scope;
		if (! empty($state)) $requestUrl .= '&state=' . $state;
		if (! empty($immediate)) $requestUrl .= '&immediate=' . $immediate;
		if (! empty($responseType)) $requestUrl .= '&response_type=' . $responseType;
		if (! empty($display)) $requestUrl .= '&display=' . $display;
		
		header('Location: ' . $requestUrl);
		exit(1);
	
	}

	/**
	 *
	 * @param $right string
	 */
	public function addRequestedRight($right)
	{
		
		$rights = $this->getRequestedRights();
		
		if (is_array($rights)) {
			$rights [] = $right;
		} elseif (is_string($rights)) {
			$rightsArray = array ();
			$rightsArray [] = $rights;
			$rightsArray [] = $right;
			$rights = $rightsArray;
		} else {
			$rights = $right;
		}
		
		$this->setRequestedRights($rights);
	}

	/**
	 *
	 * @param $right string
	 */
	public function removeRequestedRight($right)
	{
		
		$rights = $this->getRequestedRights();
		
		$key = array_search($right, $rights);
		
		if ($key !== false) {
			unset($rights [$key]);
		}
		
		$this->setRequestedRights($rights);
	
	}

	/**
	 * Set verification code
	 * 
	 * @param $verificationCode string
	 * @return AddInMage_Oauth2
	 */
	public function setVerificationCode($verificationCode)
	{
		$this->_verificationCode = $verificationCode;
		return $this;
	}

	/**
	 * Get verification code
	 * 
	 * @return string
	 */
	public function getVerificationCode()
	{
		return $this->_verificationCode;
	}

	/**
	 * Set local HTTP client as distinct from the static HTTP client
	 * as inherited from Zend_Rest_Client.
	 * 
	 * @param $client Zend_Http_Client
	 * @return self
	 */
	public static function setLocalHttpClient(Zend_Http_Client $httpClient)
	{
		self::$_localHttpClient = $httpClient;
	}

	/**
	 *
	 * @return <type>
	 */
	public static function getLocalHttpClient()
	{
		if (! isset(self::$_localHttpClient)) {
			self::$_localHttpClient = new Zend_Http_Client();
		}
		
		return self::$_localHttpClient;
	}

	/**
	 * Simple mechanism to delete the entire singleton HTTP Client instance
	 * which forces an new instantiation for subsequent requests.
	 * 
	 * @return void
	 */
	public static function clearHttpClient()
	{
		self::$httpClient = null;
	}

	/**
	 * requests an access token from the authorization server
	 * The client obtains an access token from the authorization server by
	 * making an HTTP "POST" request to the token endpoint. The client
	 * constructs a request URI by adding the following parameters to the
	 * request:
	 * - type REQUIRED (The type of user delegation authorization flow)
	 * - client_id REQUIRED (The client identifier)
	 * - client_secret REQUIRED (The matching client secret)
	 * - code REQUIRED (The verification code received from the authorization server)
	 * - redirect_uri REQUIRED (The redirection URI used in the initial request)
	 * - secret_type OPTIONAL (The access token secret type. If omitted, the authorization
	 * server will issue a bearer token (an access token without a matching secret))
	 * 
	 * @param $verificationCode string
	 * @return string
	 */
	public function requestAccessToken($verificationCode = null, $tokenEndPoint = null, $callbackUrl = null, $clientId = null, $clientSecret = null, $type = null, $secretTtype = null, $grandType = null, $display = null)
	{
		if (is_null($verificationCode)) $verificationCode = $this->getVerificationCode();
		if (is_null($tokenEndPoint)) $tokenEndPoint = $this->_config->getTokenEndPoint();
		if (is_null($callbackUrl)) $callbackUrl = $this->_config->getCallbackUrl();
		if (is_null($clientId)) $clientId = $this->_config->getClientId();
		if (is_null($clientSecret)) $clientSecret = $this->_config->getClientSecret();
		if (is_null($type)) $type = $this->_config->getType();
		if (is_null($secretTtype)) $secretTtype = $this->_config->getSecretType();
		if (is_null($grandType)) $grandType = $this->_config->getGrandType();
		if (is_null($display)) $display = $this->_config->getDisplay();
		if (is_null(self::$_localHttpClient)) $this->setLocalHttpClient($this->getLocalHttpClient());
		
		$requiredValuesArray = array ('verificationCode', 'type', 'clientId', 'clientSecret', 'callbackUrl', 'tokenEndPoint');
		
		// throw exception if one of the required values is missing
		foreach ($requiredValuesArray as $requiredValue) {
			if (is_null($$requiredValue)) {

				throw new AddInMage_Oauth2_Exception('value ' . $requiredValue . ' is empty, pass the ' . ucfirst($requiredValue) . ' as parameter when calling the ' . __METHOD__ . ' method or add it to the options array you pass when creating an instance of the ' . get_class($this) . ' class');}
		}
		
		if (substr($tokenEndPoint, - 1) == '/') $tokenEndPoint = substr($tokenEndPoint, 0, strlen($tokenEndPoint) - 1);
		
		self::$_localHttpClient->resetParameters()
			->setHeaders('Accept-Charset', 'ISO-8859-1,utf-8')
			->setUri($tokenEndPoint)
			->setParameterPost(array ('grant_type' => $grandType, 'type' => $type, 'client_id' => $clientId, 'client_secret' => $clientSecret, 'code' => $verificationCode, 'redirect_uri' => $callbackUrl, 'secret_type' => $secretTtype, 'display' => $display));
		
		$response = self::$_localHttpClient->request('POST');
		
		if (! is_null($response)) {
			$body = $response->getBody();
			$status = $response->getStatus();
		} else {
			
			throw new AddInMage_Oauth2_Exception('Communication error. Please, try again later.');
		
		}
		
		if ($status != '200') {
			$errorArray = Zend_Json::decode($body);
			
			throw new AddInMage_Oauth2_Exception('Status (' . $status . '). Error (' . Zend_Debug::dump($errorArray) . ')');
		
		}
		return $body;
	
	}

}
