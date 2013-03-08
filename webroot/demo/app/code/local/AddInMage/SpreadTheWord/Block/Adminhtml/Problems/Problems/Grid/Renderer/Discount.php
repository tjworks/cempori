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

class AddInMage_SpreadTheWord_Block_Adminhtml_Problems_Problems_Grid_Renderer_Discount extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		$reassigned = '<span class="reassigned">' . $this->__('The Discount has been updated.') . '</span>';
		if ($row->getErrorCode() == AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG) {
			return $this->__('Please see exception.log file.');
		} elseif ($row->getErrorCode() == AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_DSC && $row->getStatus() == AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE) {
			return $this->__('The given discount has expired.') . ' ' . $reassigned;
		} elseif ($row->getErrorCode() == AddInMage_SpreadTheWord_Model_Problems::ERROR_DSC_DLT && $row->getStatus() == AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE) {
			
			return $this->__('The given discount no longer exists.') . ' ' . $reassigned;
		} elseif ($row->getErrorCode() == AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_DSC) {
			return $this->__('The given discount has expired. The user was promised %s.', Mage::helper('spreadtheword')->formatDiscount($row->getPromisedAmount(), $row->getSimpleAction()));
		} elseif ($row->getErrorCode() == AddInMage_SpreadTheWord_Model_Problems::ERROR_DSC_DLT) {return $this->__('The given discount no longer exists. The user was promised %s.', Mage::helper('spreadtheword')->formatDiscount($row->getPromisedAmount(), $row->getSimpleAction()));}
	}
}
