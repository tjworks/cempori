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
 * @version    $Id: Plaintext.php 22662 2010-07-24 17:37:36Z mabe $
 */

class AddInMage_Oauth_Signature_Plaintext extends AddInMage_Oauth_Signature_SignatureAbstract
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
		if ($this->_tokenSecret === null) {return $this->_consumerSecret . '&';}
		$return = implode('&', array ($this->_consumerSecret, $this->_tokenSecret));
		return $return;
	}
}
