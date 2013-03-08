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
 * @version    $Id: AuthorizedRequest.php 22662 2010-07-24 17:37:36Z mabe $
 */

class AddInMage_Oauth_Token_AuthorizedRequest extends AddInMage_Oauth_Token
{
	/**
	 *
	 * @var array
	 */
	protected $_data = array ();

	/**
	 * Constructor
	 * 
	 * @param $data null|array
	 * @param $utility null|AddInMage_Oauth_Http_Utility
	 * @return void
	 */
	public function __construct(array $data = null, AddInMage_Oauth_Http_Utility $utility = null)
	{
		if ($data !== null) {
			$this->_data = $data;
			$params = $this->_parseData();
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
	 * Retrieve token data
	 * 
	 * @return array
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Indicate if token is valid
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		if (isset($this->_params [self::TOKEN_PARAM_KEY]) && ! empty($this->_params [self::TOKEN_PARAM_KEY])) {return true;}
		return false;
	}

	/**
	 * Parse string data into array
	 * 
	 * @return array
	 */
	protected function _parseData()
	{
		$params = array ();
		if (empty($this->_data)) {return;}
		foreach ($this->_data as $key => $value) {
			$params [rawurldecode($key)] = rawurldecode($value);
		}
		return $params;
	}
}
