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

class AddInMage_SpreadTheWord_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_action
{

	public function deleteNonActiveAction()
	{
		$customersIds = $this->getRequest()
			->getParam('customer');
		$special_date = date('Y-m-d', strtotime('-' . $this->getRequest()
			->getParam('period')));
		if (! is_array($customersIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customers.'));
		} else {
			try {
				$customer = Mage::getModel('customer/customer');
				$i = 0;
				foreach ($customersIds as $customerId) {
					$created_date = date('Y-m-d', strtotime($customer->load($customerId)
						->getCreatedAt()));
					if ($created_date < $special_date) {
						$log_model = Mage::getModel('log/customer');
						$log_model->load($customerId);
						if (! $log_model->getLoginAt()) {
							$i ++;
							$customer->reset()
								->load($customerId)
								->delete();
						}
					}
				}
				
				if ($i) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('spreadtheword')->__('%d non-activated accounts were successfully deleted.', $i));
				else Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('spreadtheword')->__('There are no non-activated accounts found for this period.'));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		$this->_redirectUrl($this->getUrl('adminhtml/customer'));
	}
}