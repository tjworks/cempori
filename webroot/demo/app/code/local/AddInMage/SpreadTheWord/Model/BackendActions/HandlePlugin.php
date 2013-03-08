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

class AddInMage_SpreadTheWord_Model_BackendActions_HandlePlugin extends Mage_Core_Model_Config_Data
{
	protected $_manifest;
	protected $_upl_dir;
	protected $_tmp;

	protected function _beforeSave()
	{
		if ($_FILES ['groups'] ['tmp_name'] [$this->getGroupId()] ['fields'] [$this->getField()] ['value']) {
			
			$fieldConfig = $this->getFieldConfig();
			
			$uploadDir = (string) $fieldConfig->upload_dir;
			
			$el = $fieldConfig->descend('upload_dir');
			$file = array ();
			$file ['tmp_name'] = $_FILES ['groups'] ['tmp_name'] [$this->getGroupId()] ['fields'] [$this->getField()] ['value'];
			$file ['name'] = $_FILES ['groups'] ['name'] [$this->getGroupId()] ['fields'] [$this->getField()] ['value'];
			
			$name = pathinfo($file ['name'], PATHINFO_FILENAME);
			
			if (! empty($el ['config'])) {
				$uploadRoot = (string) Mage::getConfig()->getNode((string) $el ['config'], $this->getScope(), $this->getScopeId());
				$uploadRoot = Mage::getConfig()->substDistroServerVars($uploadRoot);
				$this->_tmp = $uploadRoot . '/' . $uploadDir;
				$uploadDir = $uploadRoot . '/' . $uploadDir . '/' . strtolower($name);
			}
			
			try {
				
				$uploader = new Varien_File_Uploader($file);
				$uploader->setAllowedExtensions($this->_getAllowedExtensions());
				$uploader->setAllowRenameFiles(false);
				
				$uploader->save($uploadDir);
				$extension = pathinfo($file ['name'], PATHINFO_EXTENSION);
				$this->decompressPlugin($file, $uploadDir, $extension);
				$this->checkPlugin($name, $uploadDir);
				$this->installPlugin();
			
			} catch (Exception $e) {
				$this->_deleteTmp($this->_tmp);
				Mage::throwException($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
				return $this;
			}
		}
		return $this;
	}

	protected function _getAllowedExtensions()
	{
		return array ('zip');
	}

	protected function installPlugin()
	{
		$code = $this->_manifest ['package'] ['code'];
		$type = $this->_manifest ['package'] ['type'];
		$upl_dir = $this->_upl_dir;
		
		$plugin_dir = Mage::getModuleDir('', 'AddInMage_SpreadTheWord') . DS . 'Helper' . DS . 'Services';
		$block_dir = Mage::getModuleDir('', 'AddInMage_SpreadTheWord') . DS . 'Block' . DS . 'Elements' . DS . 'SpecificServices';
		$template_dir = Mage::getBaseDir('design') . DS . 'frontend' . DS . 'base' . DS . 'default' . DS . 'template' . DS . 'addinmage' . DS . 'spreadtheword' . DS . 'elements' . DS . 'specific-services';
		$style_dir = Mage::getBaseDir('skin') . DS . 'frontend' . DS . 'base' . DS . 'default' . DS . 'css' . DS . 'addinmage' . DS . 'spreadtheword' . DS . 'ext' . DS . $code;
		
		$table_prefix = Mage::getConfig()->getTablePrefix();
		$tableName = $table_prefix . 'spreadtheword_services';
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		$model = Mage::getModel('spreadtheword/services');
		$service = $model->getCollection()
			->addFieldToFilter('name', array ('eq' => $code))
			->getFirstItem();
		
		if ($service->hasId()) {
			$sql = "UPDATE {$tableName} SET `type` = '$type' WHERE `id` = {$service->getId()}; commit;";
			$connection->query($sql);
		} else {
			$sql = "INSERT INTO {$tableName} (`name`,`type`) VALUES ('$code','$type'); commit;";
			$connection->query($sql);
		}
		
	
		rename($upl_dir . DS . $this->_manifest ['package'] ['plugin'] ['file'] ['_attribute'] ['name'], $plugin_dir . DS . $this->_manifest ['package'] ['plugin'] ['file'] ['_attribute'] ['name']);
		
	
		$this->_createDestinationFolder($style_dir);
		rename($upl_dir . DS . 'style' . DS . $this->_manifest ['package'] ['style'] ['css'] ['file'] ['_attribute'] ['name'], $style_dir . DS . $this->_manifest ['package'] ['style'] ['css'] ['file'] ['_attribute'] ['name']);
		rename($upl_dir . DS . 'style' . DS . $this->_manifest ['package'] ['style'] ['image'] ['file'] ['_attribute'] ['name'], $style_dir . DS . $this->_manifest ['package'] ['style'] ['image'] ['file'] ['_attribute'] ['name']);
		
		
		if (isset($this->_manifest ['package'] ['specific'] ['block'])) {
			$this->_createDestinationFolder($block_dir);
			foreach ($this->_manifest ['package'] ['specific'] ['block'] as $specific_block) {
				rename($upl_dir . DS . 'block' . DS . $specific_block ['_attribute'] ['name'], $block_dir . DS . $specific_block ['_attribute'] ['name']);
			}
		}
		
		
		if (isset($this->_manifest ['package'] ['specific'] ['template'])) {
			$this->_createDestinationFolder($template_dir);
			foreach ($this->_manifest ['package'] ['specific'] ['template'] as $specific_template) {
				rename($upl_dir . DS . 'template' . DS . $specific_template ['_attribute'] ['name'], $template_dir . DS . $specific_template ['_attribute'] ['name']);
			}
		}
		$this->_deleteTmp($this->_tmp);
		$this->_getSession()
			->addNotice($this->_getHelper()
			->__('%s plugin is successfully installed.', $this->_manifest ['package'] ['name']));
	}

	protected function decompressPlugin($file, $uploadDir, $extension)
	{
		$file_to_decompress = $uploadDir . DS . $file ['name'];
		
		$filter = new Zend_Filter_Decompress(array ('adapter' => $extension, 'options' => array ('target' => $uploadDir)));
		$compressed = @$filter->filter($file_to_decompress);
		unlink($file_to_decompress);
	
	}

	protected function _getSession()
	{
		return Mage::getSingleton('adminhtml/session');
	}

	protected function _deleteTmp($dirname)
	{
		if (is_dir($dirname)) $dir_handle = opendir($dirname);
		if (! $dir_handle) return false;
		while($file = readdir($dir_handle)) {
			if ($file != "." && $file != "..") {
				if (! is_dir($dirname . "/" . $file)) unlink($dirname . "/" . $file);
				else $this->_deleteTmp($dirname . '/' . $file);
			}
		}
		closedir($dir_handle);
		rmdir($dirname);
		return true;
	}

	protected function checkPlugin($file, $uploadDir)
	{
		$install = $uploadDir . DS . 'install.xml';
		if (! file_exists($install)) {
			throw new Exception($this->_getHelper()
				->__('Plugin installation instructions not found.'));
			return;
		}
		
		$xml = new Mage_Xml_Parser();
		$xml->load($install);
		$manifest = $xml->xmlToArray();
		
		if (! isset($manifest ['package'])) throw new Exception($this->_getHelper()
			->__('Installation file does not contain the necessary instructions.'));
		if (! isset($manifest ['package'] ['name'])) throw new Exception($this->_getHelper()
			->__('Installation file does not contain the required "name" element.'));
		if (! isset($manifest ['package'] ['code'])) throw new Exception($this->_getHelper()
			->__('Installation file does not contain the required "code" element.'));
		if (! in_array($manifest ['package'] ['type'], array ('social', 'mail', 'file'))) throw new Exception($this->_getHelper()
			->__('This plugin type is not allowed.'));
		if (! isset($manifest ['package'] ['type'])) throw new Exception($this->_getHelper()
			->__('Installation file does not contain the required "type" element.'));
		if (! isset($manifest ['package'] ['plugin'] ['file'] ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
			->__('The plugin file is not specified.'));
		if (! file_exists($uploadDir . DS . $manifest ['package'] ['plugin'] ['file'] ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
			->__('The plugin file does not exist.'));
		
		if (! isset($manifest ['package'] ['style'] ['css'] ['file'] ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
			->__('The plugin css file is not specified.'));
		if (! file_exists($uploadDir . DS . 'style' . DS . $manifest ['package'] ['style'] ['css'] ['file'] ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
			->__('The plugin css file "%s" does not exist.', $manifest ['package'] ['style'] ['css'] ['file'] ['_attribute'] ['name']));
		
		if (! isset($manifest ['package'] ['style'] ['image'] ['file'] ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
			->__('The plugin image file is not specified.'));
		if (! file_exists($uploadDir . DS . 'style' . DS . $manifest ['package'] ['style'] ['image'] ['file'] ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
			->__('The plugin image file "%s" does not exist.', $manifest ['package'] ['style'] ['image'] ['file'] ['_attribute'] ['name']));
		
		if (isset($manifest ['package'] ['specific'])) {
			foreach ($manifest ['package'] ['specific'] ['block'] as $specific_block) {
				if (! isset($specific_block ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
					->__('Specified view block is not found.'));
				
				if (! file_exists($uploadDir . DS . 'block' . DS . $specific_block ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
					->__('Specified view block file "%s" is not found.', $specific_block ['_attribute'] ['name']));
				
				if (str_replace('.php', '', strtolower($specific_block ['_attribute'] ['name'])) !== $manifest ['package'] ['code']) throw new Exception($this->_getHelper()
					->__('Specified view block file must have the same name as the "code" installation package element.'));
			}
			
			foreach ($manifest ['package'] ['specific'] ['template'] as $specific_template) {
				if (! isset($specific_template ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
					->__('Specific view template is not found.'));
				
				if (! file_exists($uploadDir . DS . 'template' . DS . $specific_template ['_attribute'] ['name'])) throw new Exception($this->_getHelper()
					->__('Specified view template file "%s" is not found.', $specific_template ['_attribute'] ['name']));
				
				if (str_replace('.phtml', '', strtolower($specific_template ['_attribute'] ['name'])) !== $manifest ['package'] ['code']) throw new Exception($this->_getHelper()
					->__('Specified view template file must have the same name as the "code" installation package element.'));
			}
		
		}
		
		if (str_replace('.php', '', strtolower($manifest ['package'] ['plugin'] ['file'] ['_attribute'] ['name'])) !== $manifest ['package'] ['code']) throw new Exception($this->_getHelper()
			->__('The plugin common file name must have the same name as the "code" installation package element.'));
		
		if (str_replace('.css', '', strtolower($manifest ['package'] ['style'] ['css'] ['file'] ['_attribute'] ['name'])) !== $manifest ['package'] ['code']) throw new Exception($this->_getHelper()
			->__('The plugin css file name must have the same name as the "code" installation package element.'));
		
		$this->_manifest = $manifest;
		$this->_upl_dir = $uploadDir;
	
	}

	protected function _getHelper()
	{
		return Mage::helper('spreadtheword');
	}

	private function _createDestinationFolder($destinationFolder)
	{
		if (substr($destinationFolder, - 1) == DIRECTORY_SEPARATOR) {
			$destinationFolder = substr($destinationFolder, 0, - 1);
		}
		
		if (! (@is_dir($destinationFolder) || @mkdir($destinationFolder, 0777, true))) {throw new Exception("Unable to create directory '{$destinationFolder}'.");}
		return $this;
		
		$destinationFolder = str_replace('/', DIRECTORY_SEPARATOR, $destinationFolder);
		$path = explode(DIRECTORY_SEPARATOR, $destinationFolder);
		$newPath = null;
		$oldPath = null;
		foreach ($path as $key => $directory) {
			if (trim($directory) == '') {
				continue;
			}
			if (strlen($directory) === 2 && $directory {1} === ':') {
				$newPath = $directory;
				continue;
			}
			$newPath .= ($newPath != DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR . $directory : $directory;
			if (is_dir($newPath)) {
				$oldPath = $newPath;
				continue;
			} else {
				if (is_writable($oldPath)) {
					mkdir($newPath, 0777);
				} else {
					throw new Exception("Unable to create directory '{$newPath}'. Access is denied.");
				}
			}
			$oldPath = $newPath;
		}
		return $this;
	}
}
