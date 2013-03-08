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

class AddInMage_SpreadTheWord_Model_Cron
{

	public function deleteNonActivated()
	{
		$genarate_account = Mage::getStoreConfig('addinmage_spreadtheword/behaviour/account', Mage::app()->getStore());
		$delete = Mage::getStoreConfig('addinmage_spreadtheword/behaviour/delete', Mage::app()->getStore());
		if ($genarate_account && $delete == 'delete_cron') {
			$period = Mage::getStoreConfig('addinmage_spreadtheword/behaviour/delete_cron', Mage::app()->getStore());
			$special_date = date('Y-m-d', strtotime('-' . $period));
			$customers = Mage::getModel('customer/customer')->getCollection();
			$non_activated = false;
			$i = 0;
			foreach ($customers as $customer) {
				$customer_id = $customer->getId();
				$created_date = date('Y-m-d', strtotime($customer->load($customer_id)
					->getCreatedAt()));
				if ($created_date < $special_date) {
					$log_model = Mage::getModel('log/customer');
					$log_model->load($customer_id);
					if (! $log_model->getLoginAt()) {
						$i ++;
						$customer->reset()
							->load($customer_id)
							->delete();
						$non_activated = true;
					}
				}
			}
			if ($non_activated) Mage::log(Mage::helper('spreadtheword')->__('%d non-activated account(s) were successfully deleted.', $i), null, 'addinmage/stw.log');
			else Mage::log(Mage::helper('spreadtheword')->__('There are no non-activated accounts found.'), null, 'addinmage/stw.log');
		}
	}
}