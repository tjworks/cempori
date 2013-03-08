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
 * @version    $Id: ConfigInterface.php 20217 2010-01-12 16:01:57Z matthew $
 */

interface AddInMage_Oauth_Config_ConfigInterface
{

	public function setOptions(array $options);

	public function setConsumerKey($key);

	public function getConsumerKey();

	public function setConsumerSecret($secret);

	public function getConsumerSecret();

	public function setSignatureMethod($method);

	public function getSignatureMethod();

	public function setRequestScheme($scheme);

	public function getRequestScheme();

	public function setVersion($version);

	public function getVersion();

	public function setCallbackUrl($url);

	public function getCallbackUrl();

	public function setRequestTokenUrl($url);

	public function getRequestTokenUrl();

	public function setRequestMethod($method);

	public function getRequestMethod();

	public function setAccessTokenUrl($url);

	public function getAccessTokenUrl();

	public function setUserAuthorizationUrl($url);

	public function getUserAuthorizationUrl();

	public function setToken(AddInMage_Oauth_Token $token);

	public function getToken();
}
