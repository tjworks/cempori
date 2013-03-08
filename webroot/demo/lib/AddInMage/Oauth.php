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
 * @version    $Id: Oauth.php 21070 2010-02-16 14:34:25Z padraic $
 */

class AddInMage_Oauth
{

	const REQUEST_SCHEME_HEADER = 'header';

	const REQUEST_SCHEME_POSTBODY = 'postbody';

	const REQUEST_SCHEME_QUERYSTRING = 'querystring';

	const GET = 'GET';

	const POST = 'POST';

	const PUT = 'PUT';

	const DELETE = 'DELETE';

	const HEAD = 'HEAD';
	
	/**
	 * Singleton instance if required of the HTTP client
	 * 
	 * @var Zend_Http_Client
	 */
	protected static $httpClient = null;

	/**
	 * Allows the external environment to make AddInMage_Oauth use a specific
	 * Client instance.
	 * 
	 * @param $httpClient Zend_Http_Client
	 * @return void
	 */
	public static function setHttpClient(Zend_Http_Client $httpClient)
	{
		self::$httpClient = $httpClient;
	}

	/**
	 * Return the singleton instance of the HTTP Client.
	 * Note that
	 * the instance is reset and cleared of previous parameters and
	 * Authorization header values.
	 * 
	 * @return Zend_Http_Client
	 */
	public static function getHttpClient()
	{
		if (! isset(self::$httpClient)) {
			self::$httpClient = new Zend_Http_Client();
		} else {
			self::$httpClient->setHeaders('Authorization', null);
			self::$httpClient->resetParameters();
		}
		return self::$httpClient;
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
}
