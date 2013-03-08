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
 * @version    $Id: Rsa.php 20217 2010-01-12 16:01:57Z matthew $
 */

class AddInMage_Oauth_Signature_Rsa extends AddInMage_Oauth_Signature_SignatureAbstract
{

	/**
	 * Sign a request
	 * 
	 * @param $params array
	 * @param $method null|string
	 * @param $url null|string
	 * @return string
	 */
	public function sign(array $params, $method = null, $url = null)
	{
		$rsa = new Zend_Crypt_Rsa();
		$rsa->setHashAlgorithm($this->_hashAlgorithm);
		$sign = $rsa->sign($this->_getBaseSignatureString($params, $method, $url), $this->_key, Zend_Crypt_Rsa::BASE64);
		return $sign;
	}

	/**
	 * Assemble encryption key
	 * 
	 * @return string
	 */
	protected function _assembleKey()
	{
		return $this->_consumerSecret;
	}
}
