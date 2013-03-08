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
 * @version    $Id: Token.php 22662 2010-07-24 17:37:36Z mabe $
 */

abstract class AddInMage_Oauth_Token
{

	/**
	 * @+
	 * Token constants
	 */
	const TOKEN_PARAM_KEY = 'oauth_token';

	const TOKEN_SECRET_PARAM_KEY = 'oauth_token_secret';

	const TOKEN_PARAM_CALLBACK_CONFIRMED = 'oauth_callback_confirmed';
	/**
	 * @-
	 */
	
	/**
	 * Token parameters
	 * 
	 * @var array
	 */
	protected $_params = array ();
	
	/**
	 * OAuth response object
	 * 
	 * @var Zend_Http_Response
	 */
	protected $_response = null;
	
	/**
	 *
	 * @var AddInMage_Oauth_Http_Utility
	 */
	protected $_httpUtility = null;

	/**
	 * Constructor; basic setup for any Token subclass.
	 * 
	 * @param $response null|Zend_Http_Response
	 * @param $utility null|AddInMage_Oauth_Http_Utility
	 * @return void
	 */
	public function __construct(Zend_Http_Response $response = null, AddInMage_Oauth_Http_Utility $utility = null)
	{
		if ($response !== null) {
			$this->_response = $response;
			$params = $this->_parseParameters($response);
			if (count($params) > 0) {
				$this->setParams($params);
			}
		}
		if ($utility !== null) {
			$this->_httpUtility = $utility;
		} else {
			$this->_httpUtility = new AddInMage_Oauth_Http_Utility();
		}
	}

	/**
	 * Attempts to validate the Token parsed from the HTTP response - really
	 * it's just very basic existence checks which are minimal.
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		if (isset($this->_params [self::TOKEN_PARAM_KEY]) && ! empty($this->_params [self::TOKEN_PARAM_KEY]) && isset($this->_params [self::TOKEN_SECRET_PARAM_KEY])) {return true;}
		return false;
	}

	/**
	 * Return the HTTP response object used to initialise this instance.
	 * 
	 * @return Zend_Http_Response
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Sets the value for the this Token's secret which may be used when signing
	 * requests with this Token.
	 * 
	 * @param $secret string
	 * @return AddInMage_Oauth_Token
	 */
	public function setTokenSecret($secret)
	{
		$this->setParam(self::TOKEN_SECRET_PARAM_KEY, $secret);
		return $this;
	}

	/**
	 * Retrieve this Token's secret which may be used when signing
	 * requests with this Token.
	 * 
	 * @return string
	 */
	public function getTokenSecret()
	{
		return $this->getParam(self::TOKEN_SECRET_PARAM_KEY);
	}

	/**
	 * Sets the value for a parameter (e.g.
	 * token secret or other) and run
	 * a simple filter to remove any trailing newlines.
	 * 
	 * @param $key string
	 * @param $value string
	 * @return AddInMage_Oauth_Token
	 */
	public function setParam($key, $value)
	{
		$this->_params [$key] = trim($value, "\n");
		return $this;
	}

	/**
	 * Sets the value for some parameters (e.g.
	 * token secret or other) and run
	 * a simple filter to remove any trailing newlines.
	 * 
	 * @param $params array
	 * @return AddInMage_Oauth_Token
	 */
	public function setParams(array $params)
	{
		foreach ($params as $key => $value) {
			$this->setParam($key, $value);
		}
		return $this;
	}

	/**
	 * Get the value for a parameter (e.g.
	 * token secret or other).
	 * 
	 * @param $key string
	 * @return mixed
	 */
	public function getParam($key)
	{
		if (isset($this->_params [$key])) {return $this->_params [$key];}
		return null;
	}

	/**
	 * Sets the value for a Token.
	 * 
	 * @param $token string
	 * @return AddInMage_Oauth_Token
	 */
	public function setToken($token)
	{
		$this->setParam(self::TOKEN_PARAM_KEY, $token);
		return $this;
	}

	/**
	 * Gets the value for a Token.
	 * 
	 * @return string
	 */
	public function getToken()
	{
		return $this->getParam(self::TOKEN_PARAM_KEY);
	}

	/**
	 * Generic accessor to enable access as public properties.
	 * 
	 * @return string
	 */
	public function __get($key)
	{
		return $this->getParam($key);
	}

	/**
	 * Generic mutator to enable access as public properties.
	 * 
	 * @param $key string
	 * @param $value string
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->setParam($key, $value);
	}

	/**
	 * Convert Token to a string, specifically a raw encoded query string.
	 * 
	 * @return string
	 */
	public function toString()
	{
		return $this->_httpUtility->toEncodedQueryString($this->_params);
	}

	/**
	 * Convert Token to a string, specifically a raw encoded query string.
	 * Aliases to self::toString()
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Parse a HTTP response body and collect returned parameters
	 * as raw url decoded key-value pairs in an associative array.
	 * 
	 * @param $response Zend_Http_Response
	 * @return array
	 */
	protected function _parseParameters(Zend_Http_Response $response)
	{
		$params = array ();
		$body = $response->getBody();
		if (empty($body)) {return;}
		
		// validate body based on acceptable characters...
		$parts = explode('&', $body);
		foreach ($parts as $kvpair) {
			$pair = explode('=', $kvpair);
			$params [rawurldecode($pair [0])] = rawurldecode($pair [1]);
		}
		return $params;
	}

	/**
	 * Limit serialisation stored data to the parameters
	 */
	public function __sleep()
	{
		return array ('_params');
	}

	/**
	 * After serialisation, re-instantiate a HTTP utility class for use
	 */
	public function __wakeup()
	{
		if ($this->_httpUtility === null) {
			$this->_httpUtility = new AddInMage_Oauth_Http_Utility();
		}
	}
}
