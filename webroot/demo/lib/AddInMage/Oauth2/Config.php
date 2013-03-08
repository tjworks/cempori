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
 * @version    $Id: Config.php 20232 2010-01-12 17:56:33Z matthew $
 */

class AddInMage_Oauth2_Config
{
	
	/**
	 * The URL root to append default OAuth endpoint paths.
	 * 
	 * @var string
	 */
	protected $_authorizeEndPoint = null;
	
	/**
	 * The URL root to append default OAuth endpoint paths.
	 * 
	 * @var string
	 */
	protected $_tokenEndPoint = null;
	/**
	 * The authorization type that the server returns.
	 * Valid values are "authorization_code" or "refresh_token".
	 * 
	 * @var string
	 */
	protected $_grandType = null;
	
	/**
	 * This optional value is used to define where the user is redirected to
	 * 
	 * @var string
	 */
	protected $_callbackUrl = null;
	
	/**
	 * An OAuth application's client id
	 * 
	 * @var string
	 */
	protected $_clientId = null;
	
	protected $_display = null;
	
	/**
	 * An OAuth application's client secret
	 * 
	 * @var string
	 */
	protected $_clientSecret = null;
	
	/**
	 *
	 * @var string
	 */
	protected $_type = 'web_server';
	
	/**
	 * the secret type
	 * 
	 * @var string
	 */
	protected $_secretType = null;
	
	/**
	 *
	 * @var string
	 */
	protected $_state = null;
	
	/**
	 *
	 * @var string
	 */
	protected $_immediate = null;
	
	/**
	 * rights that the application wants to get from website, can be string (single) or array (multiple)
	 * 
	 * @var string array
	 */
	protected $_requestedRights = null;
	/**
	 * The type of data to be returned in the response from the authorization server.
	 * Valid values are 'code' or 'token'.
	 * 
	 * @var string array
	 */
	protected $_responseType = null;

	/**
	 * Constructor; create a new object with an optional array|Zend_Config
	 * instance containing initialising options.
	 * 
	 * @param $options array|Zend_Config
	 * @return void
	 */
	public function __construct($options = null)
	{
		if (! is_null($options)) {
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
			case 'authorizeEndPoint':
			$this->setAuthorizeEndPoint($value);
			break;
			case 'tokenEndPoint':
			$this->setTokenEndPoint($value);
			break;
			case 'callbackUrl':
			$this->setCallbackUrl($value);
			break;
			case 'display':
			$this->setDisplay($value);
			break;
			case 'clientId':
			$this->setClientId($value);
			break;
			case 'clientSecret':
			$this->setClientSecret($value);
			break;
			case 'type':
			$this->setType($value);
			break;
			case 'secretType':
			$this->setSecretType($value);
			break;
			case 'state':
			$this->setState($value);
			break;
			case 'immediate':
			$this->setImmediate($value);
			break;
			case 'requestedRights':
			$this->setRequestedRights($value);
			break;
			case 'responseType':
			$this->setResponseType($value);
			break;
			case 'grandType':
			$this->setGrandType($value);
			break;
			}
		}
		
		return $this;
	}

	/**
	 * Set site autorization url
	 * 
	 * @param $key string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setAuthorizeEndPoint($authorizeEndPoint)
	{
		$this->_authorizeEndPoint = $authorizeEndPoint;
		return $this;
	}

	/**
	 * Get autorization url
	 * 
	 * @return string
	 */
	public function getAuthorizeEndPoint()
	{
		return $this->_authorizeEndPoint;
	}

	/**
	 * Set site token url
	 * 
	 * @param $key string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setTokenEndPoint($tokenEndPoint)
	{
		$this->_tokenEndPoint = $tokenEndPoint;
		return $this;
	}

	/**
	 * Get token url
	 * 
	 * @return string
	 */
	public function getTokenEndPoint()
	{
		return $this->_tokenEndPoint;
	}

	/**
	 * Set callback url
	 * 
	 * @param $callbackUrl string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setCallbackUrl($callbackUrl)
	{
		$this->_callbackUrl = $callbackUrl;
		return $this;
	}

	/**
	 * Get callback url
	 * 
	 * @return string
	 */
	public function getCallbackUrl()
	{
		return $this->_callbackUrl;
	}

	/**
	 * Set client id
	 * 
	 * @param $id string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setClientId($clientId)
	{
		$this->_clientId = $clientId;
		return $this;
	}

	/**
	 * Get client id
	 * 
	 * @return string
	 */
	public function getClientId()
	{
		return $this->_clientId;
	}

	public function setDisplay($display)
	{
		$this->_display = $display;
		return $this;
	}

	public function getDisplay()
	{
		return $this->_display;
	}

	/**
	 * Set client secret
	 * 
	 * @param $id string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setClientSecret($clientSecret)
	{
		$this->_clientSecret = $clientSecret;
		return $this;
	}

	/**
	 * Get client secret
	 * 
	 * @return string
	 */
	public function getClientSecret()
	{
		return $this->_clientSecret;
	}

	/**
	 * Set type
	 * 
	 * @param array rights
	 * @return AddInMage_Oauth2_Config
	 */
	public function setType($type)
	{
		$this->_type = $type;
		return $this;
	}

	/**
	 * Get type
	 * 
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Set secret type
	 * 
	 * @param array rights
	 * @return AddInMage_Oauth2_Config
	 */
	public function setSecretType($secretType)
	{
		$this->_secretType = $secretType;
		return $this;
	}

	/**
	 * Get secret type
	 * 
	 * @return string
	 */
	public function getSecretType()
	{
		return $this->_secretType;
	}

	/**
	 * Set state
	 * 
	 * @param string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setState($state)
	{
		$this->_state = $state;
		return $this;
	}

	/**
	 * Get state
	 * 
	 * @return string
	 */
	public function getState()
	{
		return $this->_state;
	}

	/**
	 * Set immediate
	 * 
	 * @param string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setImmediate($immediate)
	{
		$this->_immediate = $immediate;
		return $this;
	}

	/**
	 * Get immediate
	 * 
	 * @return string
	 */
	public function getImmediate()
	{
		return $this->_immediate;
	}

	/**
	 * Set requested rights
	 * 
	 * @param string|array
	 * @return AddInMage_Oauth2_Config
	 */
	public function setRequestedRights($rights)
	{
		$this->_requestedRights = $rights;
		return $this;
	}

	/**
	 * Get requested rights
	 * 
	 * @return string array
	 */
	public function getRequestedRights()
	{
		return $this->_requestedRights;
	}

	/**
	 * Set response type
	 * 
	 * @param string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setResponseType($type)
	{
		$this->_responseType = $type;
		return $this;
	}

	/**
	 * Get response type
	 * 
	 * @return string
	 */
	public function getResponseType()
	{
		return $this->_responseType;
	}

	/**
	 * Set grand type
	 * 
	 * @param string
	 * @return AddInMage_Oauth2_Config
	 */
	public function setGrandType($type)
	{
		$this->_grandType = $type;
		return $this;
	}

	/**
	 * Get grand type
	 * 
	 * @return string
	 */
	public function getGrandType()
	{
		return $this->_grandType;
	}

}
