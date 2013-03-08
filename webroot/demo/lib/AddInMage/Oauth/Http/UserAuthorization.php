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
 * @version    $Id: UserAuthorization.php 20217 2010-01-12 16:01:57Z matthew $
 */

class AddInMage_Oauth_Http_UserAuthorization extends AddInMage_Oauth_Http
{

	/**
	 * Generate a redirect URL from the allowable parameters and configured
	 * values.
	 * 
	 * @return string
	 */
	public function getUrl()
	{
		$params = $this->assembleParams();
		$uri = Zend_Uri_Http::fromString($this->_consumer->getUserAuthorizationUrl());
		
		$uri->setQuery($this->_httpUtility->toEncodedQueryString($params));
		
		return $uri->getUri();
	}

	/**
	 * Assemble all parameters for inclusion in a redirect URL.
	 * 
	 * @return array
	 */
	public function assembleParams()
	{
		$params = array ('oauth_token' => $this->_consumer->getLastRequestToken()
			->getToken());
		
		if (! AddInMage_Oauth_Client::$supportsRevisionA) {
			$callback = $this->_consumer->getCallbackUrl();
			if (! empty($callback)) {
				$params ['oauth_callback'] = $callback;
			}
		}
		
		if (! empty($this->_parameters)) {
			$params = array_merge($params, $this->_parameters);
		}
		
		return $params;
	}
}
