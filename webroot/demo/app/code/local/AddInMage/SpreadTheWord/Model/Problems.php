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

class AddInMage_SpreadTheWord_Model_Problems extends Mage_Core_Model_Abstract
{

	const ERROR_EX_LOG 		= 0;
	const ERROR_EX_DSC 		= 1;
	const ERROR_DSC_DLT 	= 2;
	const SORT_ORDER_ASC 	= 'asc';

	public function _construct()
	{
		parent::_construct();
		$this->_init('spreadtheword/problems');
	}

	public function addRecipientData($id)
	{
		$this->setQueueId($id);
		return $this;
	}

	public function addTimeOfFailure()
	{
		$this->setFailedAt(now());
		return $this;
	}

	public function addErrorData($code)
	{
		$this->setErrorCode($code);
		return $this;
	}

	public function arrayUnique($array, $preserveKeys = false)
	{
		$arrayRewrite = array ();
		$arrayHashes = array ();
		foreach ($array as $key => $item) {
			$hash = md5(serialize($item));
			if (! isset($arrayHashes [$hash])) {
				$arrayHashes [$hash] = $hash;
				if ($preserveKeys) {
					$arrayRewrite [$key] = $item;
				} else {
					$arrayRewrite [] = $item;
				}
			}
		}
		return $arrayRewrite;
	}

	public function getFailedDiscounts()
	{
		$collection = $this->getCollection()
			->addFieldToFilter('error_code', array ('neq' => self::ERROR_EX_LOG));
		$queue_ids = $collection->getColumnValues('queue_id');
		$queue_model = Mage::getModel('spreadtheword/queue');
		$queue_data_model = Mage::getModel('spreadtheword/data');
		$data_ids = $queue_model->getCollection()
			->addFieldToFilter('id', array ('in' => $queue_ids))
			->getColumnValues('data_id');
		$data_ids = array_unique($data_ids);
		$amounts = $queue_data_model->getCollection()
			->addFieldToFilter('id', array ('in' => $data_ids))
			->toArray(array ('simple_action', 'promised_amount'));
		$discounts = $amounts ['items'];
		$formated_discounts = array ();
		foreach ($discounts as $discount) {
			$formated_discounts [] = array ('value' => (int) $discount ['promised_amount'] . '-' . $this->_getHelper()
				->getDiscountAmountType($discount ['simple_action']), 'label' => $this->_getHelper()
				->formatDiscount($discount ['promised_amount'], $discount ['simple_action']));
		}
		$amounts = $this->arrayUnique($formated_discounts);
		foreach ($amounts as $key => $row) {
			$label [$key] = $row ['label'];
			$value [$key] = $row ['value'];
		}
		array_multisort($label, SORT_ASC, $value, SORT_DESC, $amounts);
		array_unshift($amounts, array ('value' => '', 'label' => $this->_getHelper()
			->__('Select Amount')));
		return $amounts;
	}

	public function getNewDiscounts()
	{
		$options = $this->_getHelper()
			->getDiscountRules($to_options = true, $validation = false, $format = true);
		unset($options [0]);
		array_unshift($options, array ('value' => '', 'label' => $this->_getHelper()
			->__('Select Discount Rule')));
		return $options;
	}

	public function reassignDiscount($failed, $new)
	{
		$rule = Mage::getModel('salesrule/rule')->load($new);
		$new_amount = (int) $rule->getDiscountAmount();
		$new_simple_action = $rule->getSimpleAction();
		$queue_data_model = Mage::getModel('spreadtheword/data');
		
		$failed_inf = explode('-', $failed);
		$failed_amount = $failed_inf [0];
		$failed_types = $this->_getHelper()
			->getSimpleActionsByType($failed_inf [1]);
		
		$failed_data = $queue_data_model->getCollection()
			->addFieldToFilter('promised_amount', array ('eq' => $failed_amount))
			->addFieldToFilter('simple_action', array ('in' => $failed_types));
		
		$failed_rules = $failed_data->getColumnValues('discount_rule');
		$failed_rules = array_unique($failed_rules);
		$failed_codes = Mage::getModel('salesrule/rule')->getCollection()
			->addFieldToFilter('rule_id', array ('in' => $failed_rules))
			->getColumnValues('code');
		
		$failed_data_ids = $failed_data->getAllIds();
		
		$failed_data->setDataToAll('promised_amount', $new_amount)
			->setDataToAll('discount_rule', $new)
			->setDataToAll('simple_action', $new_simple_action)
			->save();
		
		Mage::getModel('spreadtheword/sales')->getResource()
			->fixShortDiscount($rule->getCouponCode(), $failed_codes);
		
		$queue_model = Mage::getModel('spreadtheword/queue');
		
		$queued_ids = $queue_model->getCollection()
			->addFieldToFilter('data_id', array ('in' => $failed_data_ids))
			->addFieldToFilter('status', array ('eq' => AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED))
			->getAllIds();
		Mage::getModel('spreadtheword/queue')->getResource()
			->changeStatus($queued_ids, AddInMage_SpreadTheWord_Model_Queue::STATUS_IN_QUEUE);
		
		$problems = Mage::getModel('spreadtheword/problems');
		$count = $problems->getCollection()
			->addFieldToFilter('error_code', array ('neq' => AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG))
			->count();
		
		return ($count) ? $count : false;
	}

	protected function _getHelper()
	{
		return Mage::helper('spreadtheword');
	}
}