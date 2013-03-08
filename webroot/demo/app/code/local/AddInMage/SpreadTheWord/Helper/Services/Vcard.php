<?php

/**
 * Add In Mage::
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the EULA that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL: http://add-in-mage.com/support/presales/eula/
 *
 *
 * PROPRIETARY DATA
 * 
 * This file contains trade secret data which is the property of Add In Mage:: Ltd. 
 * This file is submitted to recipient in confidence.
 * Information and source code contained herein may not be used, copied, sold, distributed, 
 * sub-licensed, rented, leased or disclosed in whole or in part to anyone except as permitted by written
 * agreement signed by an officer of Add In Mage:: Ltd.
 * 
 * 
 * MAGENTO EDITION NOTICE
 * 
 * This software is designed for Magento Community edition.
 * Add In Mage:: Ltd. does not guarantee correct work of this extension on any other Magento edition.
 * Add In Mage:: Ltd. does not provide extension support in case of using a different Magento edition.
 * 
 * 
 * @category    AddInMage
 * @package     AddInMage_SpreadTheWord
 * @copyright   Copyright (c) 2012 Add In Mage:: Ltd. (http://www.add-in-mage.com)
 * @license     http://add-in-mage.com/support/presales/eula/  End User License Agreement (EULA)
 * @author      Add In Mage:: Team <team@add-in-mage.com>
 */

class AddInMage_SpreadTheWord_Helper_Services_Vcard extends Mage_Core_Helper_Abstract
{

	public function getInfo()
	{
		$service_data = array ();
		$service_data ['service_name'] = Mage::helper('spreadtheword')->__('vCard Address Book');
		$service_data ['type'] = 'file';
		$service_data ['specific_view'] = true;
		$service_data ['controller'] = 'file';
		$service_data ['extension'] = array ('vcf', 'vcard');
		$service_data ['mime'] = array ('text/x-vcard', 'text/directory;profile=vCard', 'application/octet-stream', 'text/directory');
		return $service_data;
	}

	public function getContact($file_data)
	{
		$contacts = $this->processVcard($file_data);
		$string_to_lower = new Zend_Filter_StringToLower('UTF-8');
		$string_to_lower->setEncoding('UTF-8');
		$helper = Mage::helper('spreadtheword');
		$contact_list = array ();
		foreach ($contacts as $entry) {
			$c = array ();
			if (isset($entry ['email'])) $c ['id'] = $string_to_lower->filter($entry ['email']);
			
			if (isset($entry ['name'])) {
				$c ['name'] = '';
				$full_name = explode(' ', $entry ['name']);
				
				if (isset($full_name [0])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [0]));
				
				if (isset($full_name [0]) && isset($full_name [1])) $c ['name'] .= ' ';
				
				if (isset($full_name [1])) $c ['name'] .= $helper->firstToUpper($string_to_lower->filter($full_name [1]));
			}
			
			if (empty($c ['name'])) $c ['name'] = $c ['id'];
			if (! empty($c ['name']) && ! empty($c ['id'])) $contact_list [] = $c;
		
		}
		
		Mage::getSingleton('customer/session')->setCurrentService(array ('service' => $this->__('vCard Address Book'), 'type' => 'file', 'code' => 'vcard'));
		$contact_list = array_values(array_filter($contact_list));
		return Mage::getModel('spreadtheword/friends')->handleContacts($contact_list, 'file', 'vcard');
	}

	public function processVcard($data)
	{
		$validator = new Zend_Validate_EmailAddress();
		$contact_list = array ();
		$tokenizer = new Tokenizer($data);
		$in_vcard = false;
		$contact_name = '';
		$contact_emails = array ();
		while(true) {
			$field = $tokenizer->next();
			if ($field == null) break;
			if ($in_vcard) {
				$name = $field->name;
				if ('END' == $name && "VCARD" == $field->getStringValue()) {
					
					if (count($contact_emails) > 0) {
						foreach ($contact_emails as $email) {
							$contact_list [] = array ('name' => $contact_name, 'email' => $email);
						}
					}
					$in_vcard = false;
				} else 
					if ('FN' == $name) {
						$contact_name = $field->getStringValue();
					} else 
						if ('EMAIL' == $name) {
							$e = trim($field->getStringValue());
							if ($validator->isValid($e)) $contact_emails [] = $e;
						}
			} else {
				$name = $field->name;
				if ('BEGIN' == $name && "VCARD" == $field->getStringValue()) {
					$in_vcard = true;
					$contact_name = '';
					$contact_emails = array ();
				}
			}
		}
		
		return $contact_list;
	}
}

class Process_Vcard
{
	var $name;
	var $rawValue;
	var $params = array ();

	public function Process_Vcard($rawName, $rawValue)
	{
		$sa = explode(';', $rawName);
		$this->name = trim($sa [0]);
		$n = count($sa);
		for($i = 1; $i < $n; $i ++) {
			$s = $sa [$i];
			$idx = strpos($s, '=');
			if ($idx === false) {
				$this->addParam(trim($s), null);
			} else {
				$key = strtoupper(trim(substr($s, 0, $idx)));
				$vals = explode(',', substr($s, $idx + 1));
				foreach ($vals as $val) {
					$this->addParam($key, trim($val));
				}
			}
		}
		$this->rawValue = $rawValue;
	}

	protected function addParam($paramName, $value)
	{
		$paramName = strtoupper($paramName);
		if (isset($this->params [$paramName])) {
			$this->params [$paramName] [] = $value;
		} else {
			$a = array ();
			$a [] = $value;
			$this->params [$paramName] = $a;
		}
	}

	protected function hasParam($paramName)
	{
		return array_key_exists(strtoupper($paramName), $this->params);
	}

	protected function &getParamValues($paramName)
	{
		$paramName = strtoupper($paramName);
		if (isset($this->params [$paramName])) {
			return $this->params [$paramName];
		} else {
			return null;
		}
	}

	public function getFirstParamValue($paramName)
	{
		$paramName = strtoupper($paramName);
		if (isset($this->params [$paramName])) {
			$v = & $this->params [$paramName];
			if (count($v) == 0) return null;
			else return $v [0];
		} else {
			return null;
		}
	}

	protected function getBinaryValue()
	{
		$encoding = $this->getFirstParamValue('ENCODING');
		return $this->vCardToBinary($this->rawValue, $encoding);
	}

	public function getStringValue()
	{
		$encoding = $this->getFirstParamValue('ENCODING');
		$charset = $this->getFirstParamValue('CHARSET');
		return $this->vCardToString($this->rawValue, $encoding, $charset);
	}

	protected function getStringValues($delim)
	{
		$encoding = $this->getFirstParamValue('ENCODING');
		$charset = $this->getFirstParamValue('CHARSET');
		$al = array ();
		$a = $this->splitVcard($this->rawValue, $delim);
		foreach ($a as $v)
			$al [] = $this->vCardToString($v, $encoding, $charset);
		return $al;
	}

	protected function vCardToString($ba, $encoding, $charset)
	{
		if ($encoding != null) {
			$encoding = strtoupper($encoding);
			if ('QUOTED-PRINTABLE' == $encoding) $ba = quoted_printable_decode($ba);
			else 
				if ('B' == $encoding) $ba = base64_decode($ba);
		}
		if ($charset == null) {
			return $ba;
		} else {
			$charset = strtoupper($charset);
			if ($charset == 'UTF-8' || $charset == 'UTF8') return $ba;
			else return iconv($charset, 'UTF-8', $ba);
		}
	}

	protected function vCardToBinary($ba, $encoding)
	{
		if ($encoding != null) {
			$encoding = strtoupper($encoding);
			if ('QUOTED-PRINTABLE' == $encoding) return quoted_printable_decode($ba);
			else 
				if ('B' == $encoding) return base64_decode($ba);
		}
		return $ba;
	}

	protected function vCardUnescape($str)
	{
		if ($str == null) return null;
		$ba2 = '';
		$n = strlen($str);
		for($i = 0; $i < $n; $i ++) {
			$v = $str [$i];
			if ($v == '\\' && $i + 1 < $n) {
				$v2 = $str [++ $i];
				if ($v2 == 'r') $ba2 .= "\r";
				else 
					if ($v2 == 'n') $ba2 .= "\n";
					else $ba2 .= $v2;
			} else {
				$ba2 .= $v;
			}
		}
	}

	protected function splitVcard($ba, $delim = ',')
	{
		$a = array ();
		$n = strlen($ba);
		$i1 = 0;
		for($i = 0; $i < $n; $i ++) {
			$c = $ba [$i];
			if ($c == '\\') {
				if ($i + 1 < $n) {
					$i ++;
				}
			} else 
				if ($c == $delim) {
					$a [] = substr($ba, $i1, $i - $i1);
					$i1 = $i + 1;
				}
		}
		if ($i1 < $n) {
			if ($i1 == 0) $a [] = $ba;
			else $a [] = substr($ba, $i1, $n - $i1);
		}
		return $a;
	}
}

class Tokenizer
{
	var $src;
	var $len;
	var $pos;
	var $previousKey;
	var $previousValue;

	public function Tokenizer($data)
	{
		$this->src = $data;
		$this->pos = 0;
		$this->len = strlen($data);
		$this->previousKey = null;
		$this->previousValue = '';
	}

	protected function readUntilColonOrCRLF(&$buf)
	{
		$i = $this->pos;
		$n = $this->len;
		while($i < $n) {
			$v = $this->src [$i ++];
			if ($v == "\r") continue;
			else 
				if ($v == "\n") break;
				else 
					if ($v == ':') {
						$this->pos = $i;
						return true;
					} else 
						if ($v == '\\') {
							if ($i >= $n) break;
							$v2 = $this->src [$i ++];
							if ($v2 == 'r') $buf .= "\r";
							else 
								if ($v2 == 'n') $buf .= "\n";
								else $buf .= $v2;
						} else
							$buf .= $v;
		}
		$this->pos = $i;
		return false;
	}

	protected function readUntilCRLF(&$buf)
	{
		$i = $this->pos;
		$n = $this->len;
		while($i < $n) {
			$v = $this->src [$i ++];
			if ($v == "\r") continue;
			else 
				if ($v == "\n") break;
				else 
					if ($v == '\\') {
						if ($i >= $n) break;
						$v2 = $this->src [$i ++];
						if ($v2 == 'r') $buf .= "\r";
						else 
							if ($v2 == 'n') $buf .= "\n";
							else $buf .= $v2;
					} else
						$buf .= $v;
		}
		$this->pos = $i;
	}

	public function next()
	{
		if ($this->pos < $this->len) {
			while($this->pos < $this->len) {
				$ba = '';
				$hitColon = $this->readUntilColonOrCRLF($ba);
				if (empty($ba)) {
					if ($hitColon) $this->readUntilCRLF($ba);
					continue;
				}
				if ($hitColon) {
					$c = $ba [0];
					if (($c == ' ' || $c == '\t') && $this->previousKey != null) {
						$this->previousValue .= substr($ba, 1);
						$this->previousValue .= ':';
						$this->readUntilCRLF($this->previousValue);
					} else {
						$field = null;
						if ($this->previousKey != null) {
							$field = new Process_Vcard($this->previousKey, $this->previousValue);
						}
						$this->previousKey = $ba;
						$this->previousValue = '';
						$this->readUntilCRLF($this->previousValue);
						$tmpf = new Process_Vcard($this->previousKey, null);
						$enc = $tmpf->getFirstParamValue('ENCODING');
						if ($enc != null && strtoupper($enc) == 'QUOTED-PRINTABLE') {
							while($this->pos < $this->len) {
								$ln = strlen($this->previousValue);
								if ($ln == 0 || $this->previousValue [$ln - 1] != '=') {
									break;
								}
								$this->previousValue = substr($this->previousValue, 0, $ln - 1);
								$this->readUntilCRLF($this->previousValue);
							}
						}
						if ($field != null) return $field;
					}
				} else {
					$c = $ba [0];
					if (($c == ' ' || $c == '\t') && $this->previousKey != null) {
						$this->previousValue .= substr($ba, 1);
					} else {
						// Junk line
					}
				}
			}
		}
		if ($this->previousKey != null) {
			$field = new Process_Vcard($this->previousKey, $this->previousValue);
			$this->previousKey = null;
			$this->previousValue = '';
			return $field;
		} else {
			return null;
		}
	}
}