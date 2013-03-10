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
 * @version    $Id: AccessToken.php 20217 2010-01-12 16:01:57Z matthew $
 */

class AddInMage_Oauth_Http_AccessToken extends AddInMage_Oauth_Http
{
	/**
	 * Singleton instance if required of the HTTP client
	 * 
	 * @var Zend_Http_Client
	 */
	protected $_httpClient = null;

	/**
	 * Initiate a HTTP request to retrieve an Access Token.
	 * 
	 * @return AddInMage_Oauth_Token_Access
	 */
	public function execute()
	{
		$params = $this->assembleParams();
		$response = $this->startRequestCycle($params);
		$return = new AddInMage_Oauth_Token_Access($response);
		return $return;
	}

	/**
	 * Assemble all parameters for an OAuth Access Token request.
	 * 
	 * @return array
	 */
	public function assembleParams()
	{
		$params = array ('oauth_consumer_key' => $this->_consumer->getConsumerKey(), 'oauth_nonce' => $this->_httpUtility->generateNonce(), 'oauth_signature_method' => $this->_consumer->getSignatureMethod(), 'oauth_timestamp' => $this->_httpUtility->generateTimestamp(), 'oauth_token' => $this->_consumer->getLastRequestToken()
			->getToken(), 'oauth_version' => $this->_consumer->getVersion());
		
		if (! empty($this->_parameters)) {
			$params = array_merge($params, $this->_parameters);
		}
		
		$params ['oauth_signature'] = $this->_httpUtility->sign($params, $this->_consumer->getSignatureMethod(), $this->_consumer->getConsumerSecret(), $this->_consumer->getLastRequestToken()
			->getTokenSecret(), $this->_preferredRequestMethod, $this->_consumer->getAccessTokenUrl());
		
		return $params;
	}

	/**
	 * Generate and return a HTTP Client configured for the Header Request Scheme
	 * specified by OAuth, for use in requesting an Access Token.
	 * 
	 * @param $params array
	 * @return Zend_Http_Client
	 */
	public function getRequestSchemeHeaderClient(array $params)
	{
		$params = $this->_cleanParamsOfIllegalCustomParameters($params);
		$headerValue = $this->_toAuthorizationHeader($params);
		$client = AddInMage_Oauth::getHttpClient();
		
		$client->setUri($this->_consumer->getAccessTokenUrl());
		$client->setHeaders('Authorization', $headerValue);
		$client->setMethod($this->_preferredRequestMethod);
		
		return $client;
	}

	/**
	 * Generate and return a HTTP Client configured for the POST Body Request
	 * Scheme specified by OAuth, for use in requesting an Access Token.
	 * 
	 * @param $params array
	 * @return Zend_Http_Client
	 */
	public function getRequestSchemePostBodyClient(array $params)
	{
		$params = $this->_cleanParamsOfIllegalCustomParameters($params);
		$client = AddInMage_Oauth::getHttpClient();
		$client->setUri($this->_consumer->getAccessTokenUrl());
		$client->setMethod($this->_preferredRequestMethod);
		$client->setRawData($this->_httpUtility->toEncodedQueryString($params));
		$client->setHeaders(Zend_Http_Client::CONTENT_TYPE, Zend_Http_Client::ENC_URLENCODED);
		return $client;
	}

	/**
	 * Generate and return a HTTP Client configured for the Query String Request
	 * Scheme specified by OAuth, for use in requesting an Access Token.
	 * 
	 * @param $params array
	 * @param $url string
	 * @return Zend_Http_Client
	 */
	public function getRequestSchemeQueryStringClient(array $params, $url)
	{
		$params = $this->_cleanParamsOfIllegalCustomParameters($params);
		return parent::getRequestSchemeQueryStringClient($params, $url);
	}

	/**
	 * Attempt a request based on the current configured OAuth Request Scheme and
	 * return the resulting HTTP Response.
	 * 
	 * @param $params array
	 * @return Zend_Http_Response
	 */
	protected function _attemptRequest(array $params)
	{
		switch ($this->_preferredRequestScheme) {
		case AddInMage_Oauth::REQUEST_SCHEME_HEADER:
		$httpClient = $this->getRequestSchemeHeaderClient($params);
		break;
		case AddInMage_Oauth::REQUEST_SCHEME_POSTBODY:
		$httpClient = $this->getRequestSchemePostBodyClient($params);
		break;
		case AddInMage_Oauth::REQUEST_SCHEME_QUERYSTRING:
		$httpClient = $this->getRequestSchemeQueryStringClient($params, $this->_consumer->getAccessTokenUrl());
		break;
		}
		return $httpClient->request();
	}

	/**
	 * Access Token requests specifically may not contain non-OAuth parameters.
	 * So these should be striped out and excluded. Detection is easy since
	 * specified OAuth parameters start with "oauth_", Extension params start
	 * with "xouth_", and no other parameters should use these prefixes.
	 * xouth params are not currently allowable.
	 * 
	 * @param $params array
	 * @return array
	 */
	protected function _cleanParamsOfIllegalCustomParameters(array $params)
	{
		foreach ($params as $key => $value) {
			if (! preg_match("/^oauth_/", $key)) {
				unset($params [$key]);
			}
		}
		return $params;
	}
}