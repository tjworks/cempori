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


class AddInMage_SpreadTheWord_FileController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$core_session = Mage::getSingleton('core/session');
		$too_big = false;
		if (!$this->getRequest()->isPost()) return $this->_redirect('*');
		
		if (!$this->_validateFormKey()) {
			$core_session->addNotice($this->__('Secure token failed. Please try re-uploading your address book.'));
			return $this->_redirect('*');
		}
		
		$file_type = $this->getRequest()->getPost('file_type');
		
		if (!$file_type) {
			$core_session->addError($this->__('Address book upload failed. Please try to re-upload it.'));
			return $this->_redirect('*');
		}
		$session = Mage::getSingleton('customer/session');
		if (!$session->isLoggedIn()) {
			$sender_name = $this->getRequest()->getPost('sender_name');
			$sender_email = $this->getRequest()->getPost('sender_email');
			if (!$sender_name) {
				$core_session->addError($this->__('Please enter your name before uploading your address book..'));
				return $this->_redirect('*');
			} elseif (! $sender_email) {
				$core_session->addError($this->__('Please enter your email address before uploading your address book.'));
				return $this->_redirect('*');
			} else {
				$validator = new Zend_Validate_EmailAddress();
				if (! $validator->isValid($sender_email)) {
					$core_session->addError($this->__('Please enter a valid email address before uploading your address book.'));
					return $this->_redirect('*');
				}
				$session->setCurrentSenderData(array ('name' => $sender_name, 'email' => $sender_email, 'can_send' => true));
			}
			;
		} else {
			$session->setCurrentSenderData(array ('name' => $session->getCustomer()
				->getName(), 'email' => $session->getCustomer()
				->getEmail(), 'can_send' => true));
		}
		if (! Mage::helper('spreadtheword')->checkProvider($file_type, $this->getRequest()
			->getControllerName())) return $this->_redirect('*');
		
		$max_upload_size = Mage::helper('spreadtheword')->getMaxUpload();
		$max_upload_size_printable = $max_upload_size / 1024 / 1024;
		
		$zend_file_transfer = Mage::helper('spreadtheword')->isFileTransferImplemented();
		
		if ($zend_file_transfer) {
			$upload = new Zend_File_Transfer();
			$upload->setOptions(array ('useByteString' => false));
			$upload->addValidator('Size', false, $max_upload_size_printable.'Mb');
			$files = $upload->getFileInfo();
			
			
			if($upload->isUploaded()) {
				$files_error = false;
				foreach ($files as $file => $info) {
					if(!$upload->isValid($file)) $files_error = true;
				}
				
				if($files_error) {
					$msgs = $upload->getMessages();											
					$too_big = ($msgs && isset($msgs['fileSizeTooBig'])) ? true : false;
				}		
				
			}
			else {
				$core_session->addError($this->__('Sorry, the address book file is not found. Please try to upload a different file.'));
				return $this->_redirect('*');
			}
		
		} else {
			$file = $_FILES [$file_type];
			$file_name = $file ['name'];
			$file_size = $file ['size'];
			$file_mime = $file ['type'];
			
			if (! $file ['tmp_name']) {
				$core_session->addError($this->__('Sorry, the address book file is not found. Please try to upload a different file.'));
				return $this->_redirect('*');
			}
			$too_big = ($file_size > ($max_upload_size / 1024));
		}
		
		if ($too_big) {
			$core_session->addError($this->__('The uploaded file is too large. The maximum file size is: %s Mb.', $max_upload_size_printable));
			return $this->_redirect('*');
		}
		
		$service_info = Mage::helper('spreadtheword/services_' . $file_type)->getInfo();
		$extension = ($zend_file_transfer) ? pathinfo($upload->getFileName(), PATHINFO_EXTENSION) : pathinfo($file_name, PATHINFO_EXTENSION);
		
		
		if (in_array($extension, $service_info ['extension'])) {
			
	
			if ($zend_file_transfer) {
				if (! $this->checkMime($upload->getMimeType(), $service_info ['mime'])) return $this->_redirect('*');
			} else {
				if (! $this->checkMime($this->_detectMimeType($file), $service_info ['mime'])) return $this->_redirect('*');
			}
			
			if($zend_file_transfer) {
				$info = $upload->getFileInfo();
				$tmp_file = $info[$file_type]['tmp_name'];
			}
			$file_data = ($zend_file_transfer) ? file_get_contents($tmp_file) : file_get_contents($file['tmp_name']);
			
			if (! empty($file_data)) {
				$data = Mage::helper('spreadtheword/services_' . $file_type)->getContact($file_data);
			} 

			else {
				$message = $this->__('Sorry, the uploaded file is empty. Please try to upload a different file.');
				Mage::getSingleton('core/session')->addError($message);
				return $this->_redirect('*');
			}
		} 

		else {
			$core_session->addError($this->__('Sorry, this file extension doesn\'t correspond to the selected address book file type.'));
			return $this->_redirect('*');
		}
	}

	protected function _detectMimeType($value)
	{
		if (file_exists($value ['name'])) {
			$file = $value ['name'];
		} else 
			if (file_exists($value ['tmp_name'])) {
				$file = $value ['tmp_name'];
			} else {
				return null;
			}
		
		if (class_exists('finfo', false)) {
			$const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
			if (! empty($value ['options'] ['magicFile'])) {
				$mime = @finfo_open($const, $value ['options'] ['magicFile']);
			}
			
			if (empty($mime)) {
				$mime = @finfo_open($const);
			}
			
			if (! empty($mime)) {
				$result = finfo_file($mime, $file);
			}
			
			unset($mime);
		}
		
		if (empty($result) && (function_exists('mime_content_type') && ini_get('mime_magic.magicfile'))) {
			$result = mime_content_type($file);
		}
		
		if (empty($result)) {
			$result = 'application/octet-stream';
		}
		
		return $result;
	}

	private function checkMime($mime, $allowed_mime)
	{
		
		if (! in_array($mime, $allowed_mime)) {
			$message = $this->__('Sorry, the uploaded file is not a valid address book file. Please try to upload a different file.');
			Mage::getSingleton('core/session')->addError($message);
			return false;
		}
		return true;
	}
}