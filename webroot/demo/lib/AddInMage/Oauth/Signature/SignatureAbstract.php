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
 * @version    $Id: SignatureAbstract.php 22662 2010-07-24 17:37:36Z mabe $
 */

abstract class AddInMage_Oauth_Signature_SignatureAbstract
{
	/**
	 * Hash algorithm to use when generating signature
	 * 
	 * @var string
	 */
	protected $_hashAlgorithm = null;
	
	/**
	 * Key to use when signing
	 * 
	 * @var string
	 */
	protected $_key = null;
	
	/**
	 * Consumer secret
	 * 
	 * @var string
	 */
	protected $_consumerSecret = null;
	
	/**
	 * Token secret
	 * 
	 * @var string
	 */
	protected $_tokenSecret = '';

	/**
	 * Constructor
	 * 
	 * @param $consumerSecret string
	 * @param $tokenSecret null|string
	 * @param $hashAlgo null|string
	 * @return void
	 */
	public function __construct($consumerSecret, $tokenSecret = null, $hashAlgo = null)
	{
		$this->_consumerSecret = $consumerSecret;
		if (isset($tokenSecret)) {
			$this->_tokenSecret = $tokenSecret;
		}
		$this->_key = $this->_assembleKey();
		if (isset($hashAlgo)) {
			$this->_hashAlgorithm = $hashAlgo;
		}
	}

	/**
	 * Sign a request
	 * 
	 * @param $params array
	 * @param $method null|string
	 * @param $url null|string
	 * @return string
	 */
	public abstract function sign(array $params, $method = null, $url = null);

	/**
	 * Normalize the base signature URL
	 * 
	 * @param $url string
	 * @return string
	 */
	public function normaliseBaseSignatureUrl($url)
	{
		$uri = Zend_Uri_Http::fromString($url);
		if ($uri->getScheme() == 'http' && $uri->getPort() == '80') {
			$uri->setPort('');
		} elseif ($uri->getScheme() == 'https' && $uri->getPort() == '443') {
			$uri->setPort('');
		}
		$uri->setQuery('');
		$uri->setFragment('');
		$uri->setHost(strtolower($uri->getHost()));
		return $uri->getUri(true);
	}

	/**
	 * Assemble key from consumer and token secrets
	 * 
	 * @return string
	 */
	protected function _assembleKey()
	{
		$parts = array ($this->_consumerSecret);
		if ($this->_tokenSecret !== null) {
			$parts [] = $this->_tokenSecret;
		}
		foreach ($parts as $key => $secret) {
			$parts [$key] = AddInMage_Oauth_Http_Utility::urlEncode($secret);
		}
		return implode('&', $parts);
	}

	/**
	 * Get base signature string
	 * 
	 * @param $params array
	 * @param $method null|string
	 * @param $url null|string
	 * @return string
	 */
	protected function _getBaseSignatureString(array $params, $method = null, $url = null)
	{
		$encodedParams = array ();
		foreach ($params as $key => $value) {
			$encodedParams [AddInMage_Oauth_Http_Utility::urlEncode($key)] = AddInMage_Oauth_Http_Utility::urlEncode($value);
		}
		$baseStrings = array ();
		if (isset($method)) {
			$baseStrings [] = strtoupper($method);
		}
		if (isset($url)) {
			// should normalise later
			$baseStrings [] = AddInMage_Oauth_Http_Utility::urlEncode($this->normaliseBaseSignatureUrl($url));
		}
		if (isset($encodedParams ['oauth_signature'])) {
			unset($encodedParams ['oauth_signature']);
		}
		$baseStrings [] = AddInMage_Oauth_Http_Utility::urlEncode($this->_toByteValueOrderedQueryString($encodedParams));
		return implode('&', $baseStrings);
	}

	/**
	 * Transform an array to a byte value ordered query string
	 * 
	 * @param $params array
	 * @return string
	 */
	protected function _toByteValueOrderedQueryString(array $params)
	{
		$return = array ();
		uksort($params, 'strnatcmp');
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				natsort($value);
				foreach ($value as $keyduplicate) {
					$return [] = $key . '=' . $keyduplicate;
				}
			} else {
				$return [] = $key . '=' . $value;
			}
		}
		return implode('&', $return);
	}
}
