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

class AddInMage_SpreadTheWord_Block_Adminhtml_Problems_Problems_Grid_Renderer_ReassignDiscounts extends Mage_Adminhtml_Block_Template
{

	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('addinmage/spreadtheword/reassign_discounts.phtml');
	}

	public function getFailedDiscountsSelectHtml()
	{
		$select = $this->getLayout()
			->createBlock('adminhtml/html_select')
			->setData(array ('id' => 'reassign_failed', 'class' => 'select validate-select'))
			->setName('reassign_failed')
			->setOptions(Mage::getModel('spreadtheword/problems')->getFailedDiscounts());
		return $select->getHtml();
	}

	public function getNewDiscountsSelectHtml()
	{
		$select = $this->getLayout()
			->createBlock('adminhtml/html_select')
			->setData(array ('id' => 'reassign_new', 'class' => 'select validate-select'))
			->setName('reassign_new')
			->setOptions(Mage::getModel('spreadtheword/problems')->getNewDiscounts());
		return $select->getHtml();
	}

	public function haveAccess()
	{
		return (Mage::getSingleton('admin/session')->isAllowed('promo/quote')) ? true : false;
	}

	public function getShoppingCartPriceRulesUrl()
	{
		return $this->getUrl('adminhtml/promo_quote');
	}

	public function getReassignButtonHtml()
	{
		$button = $this->getLayout()
			->createBlock('adminhtml/widget_button')
			->setData(array ('label' => $this->__('Update discounts & Requeue'), 'type' => 'submit', 'class' => 'reassign-discounts'));
		$this->setChild('add_button', $button);
		
		return $this->getChildHtml('add_button');
	}

	public function getActionUrl()
	{
		return $this->getUrl('*/*/massReassignDiscounts');
	}
}