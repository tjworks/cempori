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
 * @version    $Id: Request.php 20217 2010-01-12 16:01:57Z matthew $
 */

class AddInMage_Oauth_Token_Request extends AddInMage_Oauth_Token
{

	/**
	 * Constructor
	 * 
	 * @param $response null|Zend_Http_Response
	 * @param $utility null|AddInMage_Oauth_Http_Utility
	 */
	public function __construct(Zend_Http_Response $response = null, AddInMage_Oauth_Http_Utility $utility = null)
	{
		parent::__construct($response, $utility);
		
		// detect if server supports OAuth 1.0a
		if (isset($this->_params [AddInMage_Oauth_Token::TOKEN_PARAM_CALLBACK_CONFIRMED])) {
			AddInMage_Oauth_Client::$supportsRevisionA = true;
		}
	}
}
