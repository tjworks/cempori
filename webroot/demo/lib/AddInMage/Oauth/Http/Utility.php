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
 * @version    $Id: Utility.php 22662 2010-07-24 17:37:36Z mabe $
 */

class AddInMage_Oauth_Http_Utility
{

	/**
	 * Assemble all parameters for a generic OAuth request - i.e.
	 * no special
	 * params other than the defaults expected for any OAuth query.
	 * 
	 * @param $url string
	 * @param $config AddInMage_Oauth_Config_ConfigInterface
	 * @param $serviceProviderParams null|array
	 * @return array
	 */
	public function assembleParams($url, AddInMage_Oauth_Config_ConfigInterface $config, array $serviceProviderParams = null)
	{
		$params = array ('oauth_consumer_key' => $config->getConsumerKey(), 'oauth_nonce' => $this->generateNonce(), 'oauth_signature_method' => $config->getSignatureMethod(), 'oauth_timestamp' => $this->generateTimestamp(), 'oauth_version' => $config->getVersion());
		if ($config->getToken()
			->getToken() != null) {
			$params ['oauth_token'] = $config->getToken()
				->getToken();
		}
		
		if ($serviceProviderParams !== null) {
			$params = array_merge($params, $serviceProviderParams);
		}
		
		$params ['oauth_signature'] = $this->sign($params, $config->getSignatureMethod(), $config->getConsumerSecret(), $config->getToken()
			->getTokenSecret(), $config->getRequestMethod(), $url);
		
		return $params;
	}

	/**
	 * Given both OAuth parameters and any custom parametere, generate an
	 * encoded query string.
	 * This method expects parameters to have been
	 * assembled and signed beforehand.
	 * 
	 * @param $params array
	 * @param $customParamsOnly bool Ignores OAuth params e.g. for requests using OAuth Header
	 * @return string
	 */
	public function toEncodedQueryString(array $params, $customParamsOnly = false)
	{
		if ($customParamsOnly) {
			foreach ($params as $key => $value) {
				if (preg_match("/^oauth_/", $key)) {
					unset($params [$key]);
				}
			}
		}
		$encodedParams = array ();
		foreach ($params as $key => $value) {
			$encodedParams [] = self::urlEncode($key) . '=' . self::urlEncode($value);
		}
		return implode('&', $encodedParams);
	}

	/**
	 * Cast to authorization header
	 * 
	 * @param $params array
	 * @param $realm null|string
	 * @param $excludeCustomParams bool
	 * @return void
	 */
	public function toAuthorizationHeader(array $params, $realm = null, $excludeCustomParams = true)
	{
		$headerValue = array ('OAuth realm="' . $realm . '"');
		
		foreach ($params as $key => $value) {
			if ($excludeCustomParams) {
				if (! preg_match("/^oauth_/", $key)) {
					continue;
				}
			}
			$headerValue [] = self::urlEncode($key) . '="' . self::urlEncode($value) . '"';
		}
		return implode(",", $headerValue);
	}

	/**
	 * Sign request
	 * 
	 * @param $params array
	 * @param $signatureMethod string
	 * @param $consumerSecret string
	 * @param $tokenSecret null|string
	 * @param $method null|string
	 * @param $url null|string
	 * @return string
	 */
	public function sign(array $params, $signatureMethod, $consumerSecret, $tokenSecret = null, $method = null, $url = null)
	{
		$className = '';
		$hashAlgo = null;
		$parts = explode('-', $signatureMethod);
		if (count($parts) > 1) {
			$className = 'AddInMage_Oauth_Signature_' . ucfirst(strtolower($parts [0]));
			$hashAlgo = $parts [1];
		} else {
			$className = 'AddInMage_Oauth_Signature_' . ucfirst(strtolower($signatureMethod));
		}
		
		$signatureObject = new $className($consumerSecret, $tokenSecret, $hashAlgo);
		return $signatureObject->sign($params, $method, $url);
	}

	/**
	 * Parse query string
	 * 
	 * @param $query mixed
	 * @return array
	 */
	public function parseQueryString($query)
	{
		$params = array ();
		if (empty($query)) {return array ();}
		
		// Not remotely perfect but beats parse_str() which converts
		// periods and uses urldecode, not rawurldecode.
		$parts = explode('&', $query);
		foreach ($parts as $pair) {
			$kv = explode('=', $pair);
			$params [rawurldecode($kv [0])] = rawurldecode($kv [1]);
		}
		return $params;
	}

	/**
	 * Generate nonce
	 * 
	 * @return string
	 */
	public function generateNonce()
	{
		return md5(uniqid(rand(), true));
	}

	/**
	 * Generate timestamp
	 * 
	 * @return int
	 */
	public function generateTimestamp()
	{
		return time();
	}

	/**
	 * urlencode a value
	 * 
	 * @param $value string
	 * @return string
	 */
	public static function urlEncode($value)
	{
		$encoded = rawurlencode($value);
		$encoded = str_replace('%7E', '~', $encoded);
		return $encoded;
	}
}
