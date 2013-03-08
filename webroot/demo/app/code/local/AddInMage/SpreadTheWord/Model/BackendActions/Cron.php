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

class AddInMage_SpreadTheWord_Model_BackendActions_Cron extends Mage_Core_Model_Config_Data
{

	const CRON_STRING_PATH 	= 'crontab/jobs/addinmage_spreadtheword_send_emails/schedule/cron_expr';
	const CRON_MODEL_PATH 	= 'crontab/jobs/addinmage_spreadtheword_send_emails/run/model';

	protected function _afterSave()
	{
		$mode = $this->getData('groups/email_sending/fields/cron_mode/value');
		$time = $this->getData('groups/email_sending/fields/time/value');
		$period = $this->getData('groups/email_sending/fields/cron_period/value');
		
		if ($mode == 'cron_time') {
			$cronExprArray = array (intval($time [1]), intval($time [0]), '*', '*', '*');
			$cronExprString = join(' ', $cronExprArray);
		} elseif ($mode == 'cron_period') {
			$cronExprString = $period;
		} else {
			$cronExprString = '';
		}
		try {
			Mage::getModel('core/config_data')->load(self::CRON_STRING_PATH, 'path')
				->setValue($cronExprString)
				->setPath(self::CRON_STRING_PATH)
				->save();
			
			Mage::getModel('core/config_data')->load(self::CRON_MODEL_PATH, 'path')
				->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
				->setPath(self::CRON_MODEL_PATH)
				->save();
		} catch (Exception $e) {
			Mage::throwException(Mage::helper('adminhtml')->__('Unable to save cron expression.'));
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
	}
}
