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

class AddInMage_SpreadTheWord_Model_Data extends Mage_Core_Model_Abstract
{

	const STATUS_DISCOUNT = 'discount';
	const STATUS_NOACTION = 'noaction';

	public function _construct()
	{
		parent::_construct();
		$this->_init('spreadtheword/data');
	}

	public function checkQueuedData()
	{
		try {
			$data = $this->getCollection();
			foreach ($data as $data_id) {
				$data_in_use = Mage::getModel('spreadtheword/queue')->getCollection()
					->addFilter('data_id', $data_id->getId())
					->getFirstItem();
				if (! $data_in_use->hasId()) $data_id->delete();
			}
		} catch (Exception $e) {
			$this->_getSession()
				->addError($e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
	}
}