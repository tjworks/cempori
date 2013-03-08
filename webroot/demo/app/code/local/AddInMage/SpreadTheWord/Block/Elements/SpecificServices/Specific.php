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


class AddInMage_SpreadTheWord_Block_Elements_SpecificServices_Specific extends Mage_Core_Block_Template
{

	public function getMaxFileSize()
	{
		$max_upload = (int) (ini_get('upload_max_filesize'));
		$max_post = (int) (ini_get('post_max_size'));
		$memory_limit = (int) (ini_get('memory_limit'));
		$upload_mb = min($max_upload, $max_post, $memory_limit);
		return $upload_mb;
	}

	public function getOnclickAction($service)
	{
		$data = Mage::getModel('AddInMage_SpreadTheWord_Helper_Services_' . ucfirst($service))->getInfo();
		$action = "window.open(";
		$action .= "'" . Mage::getUrl('spreadtheword') . $data ['controller'] . "?service=" . $service . "',";
		$action .= "'" . $service . "_oauth',";
		$action .= "'width=" . $data ['popup_size'] ['width'] . ",height=" . $data ['popup_size'] ['height'] . ",toolbar=no,location=yes'";
		$action .= ")";
		$action .= ".focus();";
		
		return $action;
	
	}

}