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


class AddInMage_SpreadTheWord_Adminhtml_RulesController extends Mage_Adminhtml_Controller_Action
{

	protected function _initRule($paramName = 'id', $type = false)
	{
		$id = (int) $this->getRequest()
			->getParam($paramName);
		$rule = Mage::getModel('spreadtheword/rules');
		if ($id) {
			$rule->load($id);
			if ($rule->getId()) {
				$rule->loadConfiguration();
			}
		} else {
			$rule->setRuleMode($type);
		}
		
		Mage::register('current_rule', $rule);
		return $rule;
	}

	public function indexAction()
	{
		$this->_getSession()
			->unsExpNotice();
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('Rules'));
		$this->_initAction()
			->_addBreadcrumb($this->__('Spread The Word'), $this->__('Rules'));
		$this->renderLayout();
	
	}

	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('addinmage/spreadtheword/rules')
			->_addBreadcrumb($this->__('Spread The Word'), $this->__('Rules'));
		return $this;
	}

	public function newAction()
	{
		$this->_title($this->__('Spread The Word'))
			->_title($this->__('New Rule'));
		Mage::getSingleton('adminhtml/session')->setData('new_rule', true);
		$this->loadLayout();
		$this->_setActiveMenu('addinmage/spreadtheword');
		$this->renderLayout();
	}

	public function editAction()
	{
		$redirectBack = false;
		try {
			
			$id = (int) $this->getRequest()
				->getParam('id');
			$no_error = (bool) $this->getRequest()
				->getParam('no_error');
			$mode = $this->getRequest()
				->getParam('rule_mode');
			$rule = $this->_initRule('id', $mode);
			$this->_getSession()
				->unsExpNotice();
			if (! $rule->getId() && $id) {
				$this->_getSession()
					->addError($this->__('The rule does not exist.'));
				$this->_redirect('*/*/');
				return;
			}
			if ($rule->getId() || $rule->getRuleMode()) {
				$this->_title($rule->getRuleName() ? $rule->getRuleName() : $this->__('New Rule'));
				Mage::getSingleton('adminhtml/session')->setData('new_rule', false);
				
				if ($rule->getConflicts() && $rule->getErrors() && ! $no_error) {
					$errors = unserialize($rule->getErrors());
					$this->_getSession()
						->addNotice($this->__('This rule cannot be used until the errors above are fixed.'));
					foreach ($errors as $err) {
						$this->_getSession()
							->addError($err);
					}
				}
				if ($this->_checkDiscountRulesNotice() && $rule->getRuleMode() !== 'noaction') {
					$link = '<a href="' . $this->getUrl('adminhtml/promo_quote/') . '" target="_blank" title="' . $this->__('Please create a discount.') . '">' . $this->__('Please create a discount.') . '</a>';
					$this->_getSession()
						->addNotice($this->__('This rule cannot be configured properly because there are no discounts available. ' . $link));
				}
			
			} else
				$this->_redirect('*/*/new');
			
			$modes = Mage::helper('spreadtheword')->getRuleModes();
			
			if (array_key_exists($rule->getRuleMode(), $modes)) {
				$mode_title = $modes [$rule->getRuleMode()];
			}
			
			$mode_title = isset($mode_title) ? $mode_title : $rule->getRuleMode();
			$rule->setModeTitle($mode_title);
			$this->loadLayout();
			$this->_setActiveMenu('addinmage/spreadtheword');
			$this->renderLayout();
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()
				->addError($e->getMessage());
			$redirectBack = true;
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		} catch (Exception $e) {
			$this->_getSession()
				->addError($this->__('Unable to load application form.'));
			$redirectBack = true;
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		if ($redirectBack) {
			$this->_redirect('*/*/');
			return;
		}
	}

	protected function _clearSessionData()
	{
		Mage::getSingleton('adminhtml/session')->unsFormData();
		return $this;
	}

	protected function _checkDiscountRulesNotice()
	{
		$rules = Mage::helper('spreadtheword')->getDiscountRules($to_options = true);
		
		if (count($rules) == 1) return true;
		return false;
	}

	protected function _unsetAreas($ruleId)
	{
		try {
			$path = 'addinmage_spreadtheword/behaviour/rules';
			$config = Mage::getModel('core/config_data');
			$message = $this->__('This rule is no longer in use because it contains errors. The STW Extension switched to the default mode "Invitation Only".');
			$configData = $config->getCollection()
				->addFilter('path', $path)
				->addFilter('value', $ruleId);
			if ($configData->getSize() > 0) {
				foreach ($configData as $data) {
					if (in_array($data->getScope(), array ('websites', 'stores'))) $message = $this->__('This rule is no longer in use because it contains errors. All stores where this rule was used have inherited the rule of the default configuration scope.');
					$config->load($data->getConfigId())
						->delete();
				
				}
				$this->_getSession()
					->addNotice($this->__($message));
			}
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()
				->addException($e, $e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		} catch (Exception $e) {
			$this->_getSession()
				->addException($e, $this->__('Unable to use the rule.'));
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
	}

	public function saveAction()
	{
		$this->_getSession()
			->unsExpNotice();
		$data = $this->getRequest()
			->getPost();
		
		$rule = false;
		$isError = false;
		$mode = false;
		
		if ($data) {
			Mage::getSingleton('adminhtml/session')->setFormData($data);
			try {
				$id = $this->getRequest()
					->getParam('id');
				
				if (! $id && isset($data ['rule_mode'])) {
					$modes = Mage::helper('spreadtheword')->getRuleModes();
					$mode = (isset($modes [$data ['rule_mode']])) ? $data ['rule_mode'] : false;
					
					if ($mode === false) {
						$this->_getSession()
							->addError($this->__('Wrong rule mode.'));
						$isError = true;
					}
				}
				$rule = $this->_initRule('id', $mode);
				if (! $rule->getId() && $id) {
					$this->_getSession()
						->addError($this->__('The rule does not exist.'));
					$this->_redirect('*/*/');
					return;
				}
				
				$rule->addData($this->_preparePostData($data));
				
				$errors = $rule->validate();
				
				if ($errors !== true) {
					foreach ($errors as $err) {
						$this->_getSession()
							->addError($err);
					}
					$isError = true;
					$this->_unsetAreas($rule->getId());
					$rule->setConflicts(true);
					$rule->setErrors(serialize($errors));
					$this->_getSession()
						->addNotice($this->__('This rule cannot be used until the errors above are fixed.'));
				}
				if (! $isError) {
					$rule->setConflicts(false);
					$rule->setErrors(false);
					$this->_getSession()
						->addSuccess($this->__('The rule has been saved.'));
					$this->_clearSessionData();
				}
				
				$rule->save();
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()
					->addException($e, $e->getMessage());
				$isError = true;
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			} catch (Exception $e) {
				$this->_getSession()
					->addException($e, $this->__('Unable to save the rule.'));
				$isError = true;
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		
		if ($isError && is_object($rule) && $rule->getId()) {
			Mage::getSingleton('adminhtml/session')->setLoadSessionFlag(true);
			$this->_redirect('*/*/edit', array ('id' => $rule->getId(), 'no_error' => true));
		} else 
			if ($isError && is_object($rule) && ! $rule->getId() && $rule->getRuleMode()) {
				$this->_redirect('*/*/edit', array ('type' => $rule->getRuleMode(), 'no_error' => true));
			} else 
				if ($this->getRequest()
					->getParam('back')) {
					$this->_redirect('*/*/edit', array ('id' => $rule->getId(), 'no_error' => true));
				} else {
					$this->_redirect('*/*/');
				}
	
	}

	public function _preparePostData(array $arr)
	{
		
		$type_error = false;
		if (isset($arr ['conf'] ['options']) && is_array($arr ['conf'] ['options']) && ! is_null($arr ['conf'] ['options'])) {
			foreach ($arr ['conf'] ['options'] as $options_index => $options) {
				
				if (isset($options) && is_array($options) && ! is_null($options)) {
					foreach ($options as $option_index => $option) {
						if (isset($option ['discount_type']) && isset($option ['values']) && $option ['discount_type'] !== 'dynamic_levels') {
							unset($arr ['conf'] ['options'] [$options_index] [$option_index]);
							$type_error = true;
							continue;
						}
						
						if (isset($option ['from_date']) && ! empty($option ['from_date'])) {
							$from_parts = preg_split('[/]', trim($option ['from_date']));
							$from = checkdate($from_parts [1], $from_parts [0], $from_parts [2]);
							if ($from) {
								$from_date = mktime(0, 0, 0, $from_parts [1], $from_parts [0], $from_parts [2]);
								$arr ['conf'] ['options'] [$options_index] [$option_index] ['from_date'] = $from_date;
							} else
								$arr ['conf'] ['options'] [$options_index] [$option_index] ['from_date'] = null;
						}
						
						if (isset($option ['to_date']) && ! empty($option ['to_date'])) {
							$to_parts = preg_split('[/]', trim($option ['to_date']));
							$to = checkdate($to_parts [1], $to_parts [0], $to_parts [2]);
							if ($to) {
								$to_date = mktime(0, 0, 0, $to_parts [1], $to_parts [0], $to_parts [2]);
								$arr ['conf'] ['options'] [$options_index] [$option_index] ['to_date'] = $to_date;
							} else
								$arr ['conf'] ['options'] [$options_index] [$option_index] ['to_date'] = null;
						}
						
						if (isset($option ['values']) && is_array($option ['values']) && ! is_null($option ['values'])) {
							$percent_data = array ();
							foreach ($option ['values'] as $key_data => $row_data) {
								$percent_data [$key_data] = $row_data ['percent'];
							}
							array_multisort($percent_data, SORT_NUMERIC, $option ['values']);
							$arr ['conf'] ['options'] [$options_index] [$option_index] ['values'] = $option ['values'];
						
						}
					}
				}
			}
		}
		
		if (isset($arr ['conf'] ['rules']) && is_array($arr ['conf'] ['rules']) && ! is_null($arr ['conf'] ['rules'])) {
			foreach ($arr ['conf'] ['rules'] as $index => $rule) {
				
				if (isset($rule ['discount_type']) && isset($rule ['dynamic_levels_discount']) && $rule ['discount_type'] !== 'dynamic_levels') {
					unset($arr ['conf'] ['rules'] [$index]);
					$type_error = true;
					continue;
				}
				
				if (isset($rule ['dynamic_levels_discount']) && is_array($rule ['dynamic_levels_discount']) && ! is_null($rule ['dynamic_levels_discount'])) {
					$percent = array ();
					foreach ($rule ['dynamic_levels_discount'] as $key => $row) {
						$percent [$key] = $row ['percent'];
					}
					array_multisort($percent, SORT_NUMERIC, $rule ['dynamic_levels_discount']);
					$arr ['conf'] ['rules'] [$index] ['dynamic_levels_discount'] = $rule ['dynamic_levels_discount'];
					array_values(array_filter($arr ['conf'] ['rules'] [$index] ['dynamic_levels_discount']));
				}
			}
		}
		if ($type_error) $this->_getSession()
			->addNotice($this->__('Some service rule conditions were removed because of conflicting data.'));
		
		return $arr;
	}

	public function useDefaultAction()
	{
		$website = $this->getRequest()
			->getParam('website');
		$store = $this->getRequest()
			->getParam('store');
		$use_parent = $this->getRequest()
			->getParam('use_parent');
		$this->_getSession()
			->unsExpNotice();
		if ($use_parent) {
			
			try {
				$path = 'addinmage_spreadtheword/behaviour/rules';
				if ($store && $website) {
					$param = 'stores';
					$scopeId = Mage::app()->getStore($store)
						->getId();
				}
				if (! $store && $website) {
					$param = 'websites';
					$scopeId = Mage::app()->getWebsite($website)
						->getId();
				}
				
				$config = Mage::getModel('core/config_data');
				$configData = $config->getCollection()
					->addFilter('path', $path)
					->addFilter('scope', $param)
					->addFilter('scope_id', $scopeId)
					->getFirstItem()
					->getConfigId();
				
				if ($configData) {
					$config->load($configData)
						->delete();
					$this->_getSession()
						->addSuccess($this->__('The configuration has been saved.'));
				
				}
			
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()
					->addException($e, $e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			} catch (Exception $e) {
				$this->_getSession()
					->addException($e, $this->__('Unable to save configuration.'));
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		$this->_redirect('*/*/');
	}

	public function useItAction()
	{
		
		$website = $this->getRequest()
			->getParam('website');
		$store = $this->getRequest()
			->getParam('store');
		$this->_getSession()
			->unsExpNotice();
		try {
			$rule = $this->_initRule();
			$section = 'addinmage_spreadtheword';
			$data = array ('rules' => $rule->getId());
			$groups = array ();
			
			foreach ($data as $key => $value) {
				$groups ['behaviour'] ['fields'] [$key] ['value'] = $value;
			}
			
			Mage::getModel('adminhtml/config_data')->setSection($section)
				->setWebsite($website)
				->setStore($store)
				->setGroups($groups)
				->save();
			
			Mage::getConfig()->reinit();
			Mage::app()->reinitStores();
			
			$this->_getSession()
				->addSuccess($this->__('The selected rule is enabled.'));
		
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()
				->addException($e, $e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		} catch (Exception $e) {
			$this->_getSession()
				->addException($e, $this->__('Unable to use the rule.'));
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		$this->_redirect('*/*/', array ('website' => $website, 'store' => $store));
	}

	public function deleteAction()
	{
		try {
			$rule = $this->_initRule();
			if (! $this->_getInUse($rule->getId()) && $rule->getId()) {
				$rule->delete();
				$this->_getSession()
					->unsExpNotice();
				$this->_getSession()
					->addSuccess($this->__('The rule has been deleted.'));
			} elseif ($this->_getInUse($rule->getId()) && $rule->getId()) {
				$this->_getSession()
					->addNotice($this->__('You cannot delete a rule that is currently in use.'));
			} elseif (! $rule->getId()) {
				$this->_getSession()
					->addError($this->__('Unable to delete the rule. The rule is not found.'));
			}
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()
				->addException($e, $e->getMessage());
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		} catch (Exception $e) {
			$this->_getSession()
				->addException($e, $this->__('Unable to find the rule to be deleted.'));
			Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction()
	{
		
		$ids = $this->getRequest()
			->getParam('spreadtheword_massaction_ids');
		
		if (! is_array($ids)) {
			$this->_getSession()
				->addError($this->__('Please select rule(s)'));
		} else {
			try {
				foreach ($ids as $index => $id) {
					$rule = Mage::getModel('spreadtheword/rules')->load($id);
					if (! $this->_getInUse($rule->getId()) && $rule->getId()) {
						$rule->delete();
					} else 
						if ($this->_getInUse($rule->getId())) {
							$this->_getSession()
								->addNotice($this->__('Rule with id #%d can not be deleted because it is in use.', $rule->getId()));
							unset($ids [$index]);
						}
				}
				if (count($ids) !== 0) {
					$this->_getSession()
						->unsExpNotice();
					$this->_getSession()
						->addSuccess($this->__('Total of %d rule(s) were successfully deleted.', count($ids)));
				}
			} catch (Exception $e) {
				$this->_getSession()
					->addError($e->getMessage());
				Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
			}
		}
		$this->_redirect('*/*/index');
	}

	protected function _getInUse($id)
	{
		$path = 'addinmage_spreadtheword/behaviour/rules';
		$config = Mage::getModel('core/config_data');
		$configData = $config->getCollection()
			->addFilter('path', $path)
			->addFilter('value', $id)
			->getFirstItem()
			->getConfigId();
		if ($configData) return true;
		return false;
	}
}
