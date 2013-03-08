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

class AddInMage_SpreadTheWord_Model_Rules extends Mage_Core_Model_Abstract
{
	protected $conf;

	public function _construct()
	{
		parent::_construct();
		$this->_init('spreadtheword/rules');
	}

	protected function _beforeSave()
	{
		$conf = serialize($this->prepareConfiguration());
		$this->setConfiguration($conf);
		
		$this->setUpdatedAt(date('Y-m-d H:i:s', time()));
		return $this;
	}

	public function prepareConfiguration()
	{
		return $this->getData('conf');
	}

	public function validate()
	{
		$errors = array ();
		$validateConf = $this->_validateConf();
		if ($validateConf !== true) {
			$errors = $validateConf;
		}
		if (! Zend_Validate::is($this->getRuleName(), 'NotEmpty')) {
			$errors [] = Mage::helper('spreadtheword')->__('Please enter "Rule Name".');
		}
		
		if (empty($errors)) {return true;}
		return $errors;
	}

	protected function _validateConf()
	{
		$errors = array ();
		$conf = $this->getConf();
		
		$rules_config = isset($conf ['rules']) && is_array($conf ['rules']) && ! is_null($conf ['rules']) ? $conf ['rules'] : false;
		$options_config = isset($conf ['options']) && is_array($conf ['options']) && ! is_null($conf ['options']) ? $conf ['options'] : false;
		$manual_discount = (Mage::getStoreConfig('addinmage_spreadtheword/services/manual') && $this->getRuleMode() !== 'noaction') ? true : false;
		
		$helper = Mage::helper('spreadtheword');
		$discount_types = $helper->getDiscountOptions();
		$discount_rules = $helper->getDiscountRules($to_options = false, $validation = true);
		
		if ($manual_discount) {
			if (! isset($conf ['manual_discount']) || ! in_array($conf ['manual_discount'], $discount_rules)) $errors [] = $helper->__('Please select a valid discount rule on the "Manual Mode Discount" tab.');
		}
		
		if ($rules_config) {
			$valid_range = new Zend_Validate_Between(array ('min' => 0, 'max' => 100));
			$valid_int = new Zend_Validate_Int();
			foreach ($rules_config as $pr_index => $participant_rule) {
				
				if (is_array($participant_rule) && ! is_null($participant_rule)) {
					if (! isset($participant_rule ['discount_type']) || (isset($participant_rule ['discount_type']) && ! array_key_exists($participant_rule ['discount_type'], $discount_types))) $errors [] = ($pr_index == 'sender') ? $helper->__('Please select the discount type on the "Discount for Senders" tab.') : $helper->__('Please select the discount type on the "Discount for Friends" tab.');
					
					if (isset($participant_rule ['discount_type']) && $participant_rule ['discount_type'] == 'fixed' && (! isset($participant_rule ['fixed_discount']) || empty($participant_rule ['fixed_discount']))) $errors [] = ($pr_index == 'sender') ? $helper->__('Please select a fixed discount rule on the "Discount for Senders" tab.') : $helper->__('Please select a fixed discount rule on the "Discount for Friends" tab.');
					
					if (isset($participant_rule ['discount_type']) && $participant_rule ['discount_type'] == 'dynamic' && (! isset($participant_rule ['dynamic_discount']) || empty($participant_rule ['dynamic_discount']))) $errors [] = ($pr_index == 'sender') ? $helper->__('Please select the maximum discount rule on the "Discount for Senders" tab.') : $helper->__('Please select the maximum discount rule on the "Discount for Friends" tab.');
					
					if ((isset($participant_rule ['fixed_discount']) && ! in_array($participant_rule ["fixed_discount"], $discount_rules)) || (isset($participant_rule ['dynamic_discount']) && ! in_array($participant_rule ["dynamic_discount"], $discount_rules))) $errors [] = ($pr_index == 'sender') ? $helper->__('Please select a valid discount rule on the "Discount for Senders" tab.') : $helper->__('Please select a valid discount rule on the "Discount for Friends" tab.');
					
					if (isset($participant_rule ['discount_type']) && $participant_rule ['discount_type'] == 'dynamic_levels') {
						if (! isset($participant_rule ['dynamic_levels_discount']) || empty($participant_rule ['dynamic_levels_discount'])) $errors [] = ($pr_index == 'sender') ? $helper->__('Please add dynamic discount levels on the "Discount for Senders" tab.') : $helper->__('Please add dynamic discount levels on the "Discount for Friends" tab.');
						else {
							
							$pr_unique_rule = array ();
							$pr_unique_percent = array ();
							$discount_levels_rule_error = false;
							$discount_levels_percent_error = false;
							
							$discount_model = Mage::getModel('salesrule/rule');
							$start = 0;
							$lvl_amount_error = false;
							foreach ($participant_rule ['dynamic_levels_discount'] as $level_index => $level) {
								
								if ($discount_model->load($level ['rule'])
									->getDiscountAmount() < $start) $lvl_amount_error = true;
								
								$start = $discount_model->load($level ['rule'])
									->getDiscountAmount();
								if (! isset($level ['rule']) || (isset($level ['rule']) && (! in_array($level ['rule'], $discount_rules) || empty($level ['rule'])))) $discount_levels_rule_error = true;
								
								$pr_unique_rule [] = $level ['rule'];
								
								if (! isset($level ['percent']) || (isset($level ['percent']) && (! $valid_range->isValid(trim($level ['percent'])) || ! $valid_int->isValid(trim($level ['percent']))))) $discount_levels_percent_error = true;
								
								$pr_unique_percent [] = $level ['percent'];
							}
							if ($lvl_amount_error) $errors [] = ($pr_index == 'sender') ? $helper->__('Discount for Senders tab: Please check discount amounts of the discount levels. The greater the percentage of selected friends is, the greater the discount amount should be.') : $helper->__('Discount for Friends tab: Please check discount amounts of the discount levels. The greater the percentage of selected friends is, the greater the discount amount should be.');
							
							if (array_unique($pr_unique_rule) != $pr_unique_rule) $errors [] = ($pr_index == 'sender') ? $helper->__('Discount for Senders tab: Some discount levels contain the same discount amount.') : $helper->__('Discount for Friends tab: Some discount levels contain the same discount amount.');
							
							if (array_unique($pr_unique_percent) != $pr_unique_percent) $errors [] = ($pr_index == 'sender') ? $helper->__('Discount for Senders tab: Some discount levels contain duplicate percent values.') : $helper->__('Discount for Friends tab: Some discount levels contain duplicate percent values.');
							
							if ($discount_levels_rule_error) $errors [] = ($pr_index == 'sender') ? $helper->__('Discount for Senders tab: Please select discount rules for all dynamic discount levels.') : $helper->__('Discount for Friends tab:  Please select discount rules for all dynamic discount levels.');
							
							if ($discount_levels_percent_error) $errors [] = ($pr_index == 'sender') ? $helper->__('Discount for Senders tab: Please enter valid percent values (0 to 100) for all dynamic discount levels.') : $helper->__('Discount for Friends tab: Please enter valid percent values (0 to 100) for all dynamic discount levels.');
						}
					}
				}
			}
			
			$set_sender_targeting = (isset($options_config ['sender'])) ? $this->setSenderTargeting(true) : $this->setSenderTargeting(false);
			$set_friends_targeting = (isset($options_config ['friends'])) ? $this->setFriendsTargeting(true) : $this->setFriendsTargeting(false);
			if ($options_config) {
				foreach ($options_config as $po_index => $participant_options) {
					if (is_array($participant_options) && ! is_null($participant_options)) {
						$check_services = array ();
						foreach ($participant_options as $options_index => $options) {
							if (is_array($options) && ! is_null($options)) {
								
								if (! isset($options ['service']) || (isset($options ['service']) && (is_null($options ['service']) || empty($options ['service'])))) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please select a service for rule exception #%s.', $options_index) : $helper->__('Service Targeting for Friends: Please select a service for rule exception #%s.', $options_index);
								
								else {
									$check_services [$options ['service']] [$options_index] = array ();
									
									if (! isset($options ['discount_type']) || (isset($options ['discount_type']) && ! array_key_exists($options ['discount_type'], $discount_types))) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please select a discount type for rule exception #%s.', $options_index) : $helper->__('Service Targeting for Friends: Please select a discount type for rule exception #%s.', $options_index);
									
									if (isset($options ['discount_type']) && ($options ['discount_type'] == 'fixed' || $options ['discount_type'] == 'dynamic') && (! isset($options ['discount_rule']) || ! in_array($options ['discount_rule'], $discount_rules))) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please select a discount rule for rule exception #%s.', $options_index) : $helper->__('Service Targeting for Friends: Please select a discount rule for rule exception #%s.', $options_index);
									
									if (isset($options ['min_friends']) && trim(($options ['min_friends'])) !== '') {
										if (! $valid_int->isValid(trim($options ['min_friends'])) || trim($options ['min_friends']) <= 0) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please enter a valid value in the "Min Number of Friends" field for rule exception #%s.', $options_index) : $helper->__('Service Targeting for Friends: Please enter a valid value in the "Min Number of Friends" field for rule exception #%s.', $options_index);
										else $check_services [$options ['service']] [$options_index] ['min_friends'] = $options ['min_friends'];
									}
									
									if ((isset($options ['from_date']) && ! empty($options ['from_date']) && (! isset($options ['to_date']) || empty($options ['to_date']))) || (isset($options ['to_date']) && ! empty($options ['to_date']) && (! isset($options ['from_date']) || empty($options ['from_date'])))) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please enter a valid date range in rule exception #%s. Both "From Date" and "To Date" are necessary.', $options_index) : $helper->__('Service Targeting for Friends: Please enter a valid date range in rule exception #%s. Both "From Date" and "To Date" are necessary.', $options_index);
									if ((isset($options ['from_date']) && ! empty($options ['from_date'])) && (isset($options ['to_date']) && ! empty($options ['to_date']))) {
										if ($options ['from_date'] >= $options ['to_date']) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please enter a valid date range in rule exception #%s. "From Date" should be less than and not equal to the "To Date".', $options_index) : $helper->__('Service Targeting for Friends: Please enter a valid date range in rule exception #%s. "From Date" should be less than and not equal to the "To Date".', $options_index);
										else {
											$check_services [$options ['service']] [$options_index] ['from_date'] = $options ['from_date'];
											$check_services [$options ['service']] [$options_index] ['to_date'] = $options ['to_date'];
										}
									}
								}
								
								if (isset($options ['values']) && is_array($options ['values']) && ! empty($options ['values'])) {
									
									$po_unique_rule = array ();
									$po_unique_percent = array ();
									$discount_levels_rule_targeting_error = false;
									$discount_levels_percent_targeting_error = false;
									
									$discount_model = Mage::getModel('salesrule/rule');
									$start_custom = 0;
									$lvl_amount_custom_error = false;
									foreach ($options ['values'] as $option) {
										if ($discount_model->load($option ['discount_rule'])
											->getDiscountAmount() < $start_custom) $lvl_amount_custom_error = true;
										
										$start_custom = $discount_model->load($option ['discount_rule'])
											->getDiscountAmount();
										
										if (! isset($option ['discount_rule']) || (isset($option ['discount_rule']) && ! in_array($option ['discount_rule'], $discount_rules))) $discount_levels_rule_targeting_error = true;
										$po_unique_rule [] = $option ['discount_rule'];
										
										if (! isset($option ['percent']) || (isset($option ['percent']) && (! $valid_range->isValid(trim($option ['percent'])) || ! $valid_int->isValid(trim($option ['percent']))))) $discount_levels_percent_targeting_error = true;
										$po_unique_percent [] = $option ['percent'];
									
									}
									if ($lvl_amount_custom_error) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please check the amounts of the discount levels in rule exception #%s. The greater the percentage of selected friends is, the greater the discount amount should be.', $options_index) : $helper->__('Service Targeting for Friends: Please check the amounts of the discount levels in rule exception #%s. The greater the percentage of selected friends is, the greater the discount amount should be.', $options_index);
									if (array_unique($po_unique_rule) != $po_unique_rule) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Some discount levels in rule exception #%s contain dublicate discount rule values.', $options_index) : $helper->__('Service Targeting for Friends: Some discount levels in rule exception #%s contain dublicate discount rule values.', $options_index);
									
									if (array_unique($po_unique_percent) != $po_unique_percent) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Some discount levels in rule exception #%s contain duplicate percent values.', $options_index) : $helper->__('Service Targeting for Friends: Some discount levels in rule exception #%s contain duplicate percent values.', $options_index);
									
									if ($discount_levels_rule_targeting_error) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please select discount rules for all dynamic discount levels in rule exception #%s.', $options_index) : $helper->__('Service Targeting for Friends: Please select discount rules for all dynamic discount levels in rule exception #%s.', $options_index);
									
									if ($discount_levels_percent_targeting_error) $errors [] = ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: Please enter valid percent values (0 to 100) for all dynamic discount levels in rule exception #%s.', $options_index) : $helper->__('Service Targeting for Friends: Please enter valid percent values (0 to 100) for all dynamic discount levels in rule exception #%s.', $options_index);
								}
							}
						}
						if (! empty($check_services)) {
							foreach ($check_services as $service_name => $service_rules) {
								if (count($service_rules) > 1) {
									
									$service_error = array ();
									
									foreach ($service_rules as $serviceindex => $range) {
										foreach ($service_rules as $index_date => $date) {
											
											if (! isset($range ['from_date']) && ! isset($range ['to_date']) && ! isset($date ['from_date']) && ! isset($date ['to_date']) && $index_date !== $serviceindex) {
												
												$error_range_nodata_message = '';
												if (! isset($range ['min_friends']) && ! isset($date ['min_friends'])) $error_range_nodata_message .= ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: There are duplicate service rules that are in conflict with each other. Please add date ranges or "minimum number of friends" condition to resolve this problem.') : $helper->__('Service Targeting for Friends: There are duplicate service rules that are in conflict with each other. Please add date ranges or "minimum number of friends" condition to resolve this problem.');
												
												if (isset($date ['min_friends']) && isset($range ['min_friends']) && ($date ['min_friends'] == $range ['min_friends'])) $error_range_nodata_message .= ($po_index == 'sender') ? $helper->__('Service Targeting for Senders: There are duplicate service rules that are in conflict with each other. Please add date ranges or enter a different "minimum number of friends" condition to resolve this problem.') : $helper->__('Service Targeting for Friends: There are duplicate service rules that are in conflict with each other. Please add date ranges or enter a different "minimum number of friends" condition to resolve this problem.');
												
												continue;
											}
											
											if (isset($range ['from_date']) && isset($range ['to_date']) && isset($date ['from_date']) && isset($date ['to_date'])) {
												if ((($range ['from_date'] >= $date ['from_date'] && $range ['from_date'] <= $date ['to_date']) || ($range ['to_date'] <= $date ['to_date'] && $range ['to_date'] >= $date ['from_date'])) && $index_date !== $serviceindex && ((! isset($range ['min_friends']) && ! isset($date ['min_friends'])) || (isset($range ['min_friends']) && isset($date ['min_friends']) && $range ['min_friends'] == $date ['min_friends']))) {
													
													$service_error [$serviceindex] [] = $index_date;
													$error_min_fr_message = '';
													if (! isset($range ['min_friends']) || ! isset($date ['min_friends'])) $error_min_fr_message .= ($po_index == 'sender') ? $helper->__(' Service Targeting for Senders: Please change overlapping date ranges or add "minimum number of friends" condition.') : $helper->__(' Service Targeting for Friends: Please change overlapping date ranges or add "minimum number of friends" condition.');
													else {
														if ($date ['min_friends'] == $range ['min_friends']) $error_min_fr_message .= ($po_index == 'sender') ? $helper->__(' Service Targeting for Senders: Please change overlapping date ranges or enter a different "minimum number of friends" condition.') : $helper->__(' Service Targeting for Friends: Please change overlapping date ranges or enter a different "minimum number of friends" condition.');
													}
												}
											}
										}
									}
									
									if (isset($error_range_nodata_message) && ! empty($error_range_nodata_message)) $errors [] = $error_range_nodata_message;
									
									if (! empty($service_error)) {
										foreach ($service_error as $error_index => $error) {
											foreach ($error as $e_index => $e) {
												if (@in_array($error_index, $service_error [$e])) unset($service_error [$error_index] [$e_index]);
											}
										}
										foreach ($service_error as $error_mi => $error_m) {
											if (! empty($error_m)) {
												$error_message = $helper->__('Date ranges of the following rule exceptions overlap: rule %s', $error_mi);
												foreach ($error_m as $r => $sm) {
													if (! next($error_m)) $error_message .= $helper->__(' and rule %s.', $sm);
													else $error_message .= $helper->__(', rule %s', $sm);
												}
												$errors [] = $error_message . $error_min_fr_message;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		if (empty($errors)) {return true;}
		return $errors;
	}

	public function loadConfiguration()
	{
		static $isConfigurationLoaded = null;
		if (is_null($isConfigurationLoaded)) {
			$configuration = $this->getConfiguration();
			if (! empty($configuration)) {
				$configuration = unserialize($configuration);
				$this->setData('conf', $configuration);
				$isConfigurationLoaded = true;
			}
		}
		return $this;
	}

	public function getFormData()
	{
		$data = $this->getData();
		return $this->_flatArray($data);
	}

	protected function _flatArray($subtree, $prefix = null)
	{
		$result = array ();
		foreach ($subtree as $key => $value) {
			if (is_null($prefix)) {
				$name = $key;
			} else {
				$name = $prefix . '[' . $key . ']';
			}
			if (is_array($value)) {
				$result = array_merge($result, $this->_flatArray($value, $name));
			} else {
				$result [$name] = $value;
			}
		}
		return $result;
	}
}