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

class AddInMage_SpreadTheWord_Block_Adminhtml_Problems_Problems extends Mage_Adminhtml_Block_Widget_Container
{

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('addinmage/spreadtheword/problems.phtml');
	}

	protected function _prepareLayout()
	{
		if ($this->testFailed()) {
			Mage::register('can_show_failed_control', true);
			$this->_addButton('retry', array ('label' => $this->__('Requeue all failed invitations'), 'title' => $this->__('Requeue all failed invitations'), 'onclick' => "setLocation('{$this->getUrl('*/*/retry')}')", 'class' => 'adm-stw-retry'));
		}
		if ($this->canReassignDiscounts()) $this->setChild('reassingn_discounts', $this->getLayout()
			->createBlock('spreadtheword/adminhtml_problems_problems_grid_renderer_reassigndiscounts', 'reassingn.discounts'));
		
		$this->setChild('grid', $this->getLayout()
			->createBlock('spreadtheword/adminhtml_problems_problems_grid', 'problems.grid'));
		return parent::_prepareLayout();
	}

	public function getGridHtml()
	{
		return $this->getChildHtml('grid');
	}

	public function getReassignDiscountsHtml()
	{
		return $this->getChildHtml('reassingn_discounts');
	}

	protected function testFailed()
	{
		return (Mage::getModel('spreadtheword/queue')->getCollection()
			->addFilter('status', AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED)
			->count()) ? true : false;
	}

	protected function canReassignDiscounts()
	{
		$problems = Mage::getModel('spreadtheword/problems');
		$queue = Mage::getModel('spreadtheword/queue');
		$problems_queue_ids = $problems->getCollection()
			->addFieldToFilter('error_code', array ('neq' => AddInMage_SpreadTheWord_Model_Problems::ERROR_EX_LOG));
		if ($problems_queue_ids->count()) {
			$non_requeued = $queue->getCollection()
				->addFieldToFilter('status', array ('eq' => AddInMage_SpreadTheWord_Model_Queue::STATUS_FAILED))
				->addFieldToFilter('id', array ('in' => $problems_queue_ids->getColumnValues('queue_id')));
			return ($non_requeued->count()) ? true : false;
		}
		
		return false;
	}
}
