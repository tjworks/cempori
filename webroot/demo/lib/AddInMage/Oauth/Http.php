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
 * @version    $Id: Http.php 22662 2010-07-24 17:37:36Z mabe $
 */

class AddInMage_Oauth_Http
{
	/**
	 * Array of all custom service parameters to be sent in the HTTP request
	 * in addition to the usual OAuth parameters.
	 * 
	 * @var array
	 */
	protected $_parameters = array ();
	
	/**
	 * Reference to the AddInMage_Oauth_Consumer instance in use.
	 * 
	 * @var string
	 */
	protected $_consumer = null;
	
	/**
	 * OAuth specifies three request methods, this holds the current preferred
	 * one which by default uses the Authorization Header approach for passing
	 * OAuth parameters, and a POST body for non-OAuth custom parameters.
	 * 
	 * @var string
	 */
	protected $_preferredRequestScheme = null;
	
	/**
	 * Request Method for the HTTP Request.
	 * 
	 * @var string
	 */
	protected $_preferredRequestMethod = AddInMage_Oauth::POST;
	
	/**
	 * Instance of the general AddInMage_Oauth_Http_Utility class.
	 * 
	 * @var AddInMage_Oauth_Http_Utility
	 */
	protected $_httpUtility = null;

	/**
	 * Constructor
	 * 
	 * @param $consumer AddInMage_Oauth_Consumer
	 * @param $parameters null|array
	 * @param $utility null|AddInMage_Oauth_Http_Utility
	 * @return void
	 */
	public function __construct(AddInMage_Oauth_Consumer $consumer, array $parameters = null, AddInMage_Oauth_Http_Utility $utility = null)
	{
		$this->_consumer = $consumer;
		$this->_preferredRequestScheme = $this->_consumer->getRequestScheme();
		if ($parameters !== null) {
			$this->setParameters($parameters);
		}
		if ($utility !== null) {
			$this->_httpUtility = $utility;
		} else {
			$this->_httpUtility = new AddInMage_Oauth_Http_Utility();
		}
	}

	/**
	 * Set a preferred HTTP request method.
	 * 
	 * @param $method string
	 * @return AddInMage_Oauth_Http
	 */
	public function setMethod($method)
	{
		if (! in_array($method, array (AddInMage_Oauth::POST, AddInMage_Oauth::GET))) {

			throw new AddInMage_Oauth_Exception('invalid HTTP method: ' . $method);}
		$this->_preferredRequestMethod = $method;
		return $this;
	}

	/**
	 * Preferred HTTP request method accessor.
	 * 
	 * @return string
	 */
	public function getMethod()
	{
		return $this->_preferredRequestMethod;
	}

	/**
	 * Mutator to set an array of custom parameters for the HTTP request.
	 * 
	 * @param $customServiceParameters array
	 * @return AddInMage_Oauth_Http
	 */
	public function setParameters(array $customServiceParameters)
	{
		$this->_parameters = $customServiceParameters;
		return $this;
	}

	/**
	 * Accessor for an array of custom parameters.
	 * 
	 * @return array
	 */
	public function getParameters()
	{
		return $this->_parameters;
	}

	/**
	 * Return the Consumer instance in use.
	 * 
	 * @return AddInMage_Oauth_Consumer
	 */
	public function getConsumer()
	{
		return $this->_consumer;
	}

	/**
	 * Commence a request cycle where the current HTTP method and OAuth
	 * request scheme set an upper preferred HTTP request style and where
	 * failures generate a new HTTP request style further down the OAuth
	 * preference list for OAuth Request Schemes.
	 * On success, return the Request object that results for processing.
	 * 
	 * @param $params array
	 * @return Zend_Http_Response
	 * @throws AddInMage_Oauth_Exception on HTTP request errors
	 * Remove cycling?; Replace with upfront do-or-die configuration
	 */
	public function startRequestCycle(array $params)
	{
		$response = null;
		$body = null;
		$status = null;
		try {
			$response = $this->_attemptRequest($params);
		} catch (Zend_Http_Client_Exception $e) {
			
			throw new AddInMage_Oauth_Exception('Error in HTTP request', null, $e);
		}
		if ($response !== null) {
			$body = $response->getBody();
			$status = $response->getStatus();
		}
		if ($response === null || 		// Request failure/exception
		$status == 500 || 		// Internal Server Error
		$status == 400 || 		// Bad Request
		$status == 401 || 		// Unauthorized
		empty($body)) 		// Missing token
		{
			$this->_assessRequestAttempt($response);
			$response = $this->startRequestCycle($params);
		}
		return $response;
	}

	/**
	 * Return an instance of Zend_Http_Client configured to use the Query
	 * String scheme for an OAuth driven HTTP request.
	 * 
	 * @param $params array
	 * @param $url string
	 * @return Zend_Http_Client
	 */
	public function getRequestSchemeQueryStringClient(array $params, $url)
	{
		$client = AddInMage_Oauth::getHttpClient();
		$client->setUri($url);
		$client->getUri()
			->setQuery($this->_httpUtility->toEncodedQueryString($params));
		$client->setMethod($this->_preferredRequestMethod);
		return $client;
	}

	/**
	 * Manages the switch from OAuth request scheme to another lower preference
	 * scheme during a request cycle.
	 * 
	 * @param Zend_Http_Response
	 * @return void
	 * @throws AddInMage_Oauth_Exception if unable to retrieve valid token response
	 */
	protected function _assessRequestAttempt(Zend_Http_Response $response = null)
	{
		switch ($this->_preferredRequestScheme) {
		case AddInMage_Oauth::REQUEST_SCHEME_HEADER:
		$this->_preferredRequestScheme = AddInMage_Oauth::REQUEST_SCHEME_POSTBODY;
		break;
		case AddInMage_Oauth::REQUEST_SCHEME_POSTBODY:
		$this->_preferredRequestScheme = AddInMage_Oauth::REQUEST_SCHEME_QUERYSTRING;
		break;
		default:
		
		throw new AddInMage_Oauth_Exception('Could not retrieve a valid Token response from Token URL:' . ($response !== null ? PHP_EOL . $response->getBody() : ' No body - check for headers'));
		}
	}

	/**
	 * Generates a valid OAuth Authorization header based on the provided
	 * parameters and realm.
	 * 
	 * @param $params array
	 * @param $realm string
	 * @return string
	 */
	protected function _toAuthorizationHeader(array $params, $realm = null)
	{
		$headerValue = array ();
		$headerValue [] = 'OAuth realm="' . $realm . '"';
		foreach ($params as $key => $value) {
			if (! preg_match("/^oauth_/", $key)) {
				continue;
			}
			$headerValue [] = AddInMage_Oauth_Http_Utility::urlEncode($key) . '="' . AddInMage_Oauth_Http_Utility::urlEncode($value) . '"';
		}
		return implode(",", $headerValue);
	}
}
