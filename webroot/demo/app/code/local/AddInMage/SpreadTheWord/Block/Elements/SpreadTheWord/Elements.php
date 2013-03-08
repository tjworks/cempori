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


class AddInMage_SpreadTheWord_Block_Elements_SpreadTheWord_Elements extends Mage_Core_Block_Template
{
	protected $_mode;

	protected function _prepareLayout()
	{
		$config = Mage::getSingleton('core/session')->getStwConfig();
		if (! is_object($config)) $this->_mode = 'noaction';
		else $this->_mode = $config->getRuleMode();
		
		return parent::_prepareLayout();
	}

	public function getThirdStepText()
	{
		
		switch ($this->_mode) {
			case 'noaction':
			$third_text = $this->__('Thanks for inviting your friends!');
			break;
			case 'action_d_sen':
			$third_text = $this->__('Get your discount code and enjoy shopping!');
			break;
			case 'action_d_frd':
			$third_text = $this->__('The discount codes are in your friends\' inboxes!');
			break;
			case 'action_d_all':
			$third_text = $this->__('Get your discount code and enjoy shopping with your friends!');
			break;
		}
		
		return $third_text;
	}
	

	public function getDescriptionText()
	{
		switch ($this->_mode) {
			case 'noaction':
			$decription_text = array(
				$this->__('It is easy to tell your friends about our store! Choose the service or tool and select the friends whom you want to invite.'),
				$this->__('Don\'t forget to come here again, sometimes you and your friends may get discounts!'),
			);
			break;
			case 'action_d_sen':
			$decription_text = array(
				$this->__('It is simple! Choose the service or tool and select the friends whom you want to invite. Send the invitations in a click and get a discount at our store!'),
				$this->__('Don\'t forget to come here again, the discount terms may change every day!'),
			);
			break;
			case 'action_d_frd':
			$decription_text = array(
				$this->__('It is simple! Choose the service or tool and select people you want to invite. Your friends will find a pleasant surprise in the invitation - a discount they can use at our store!'),
				$this->__('Don\'t forget to come here again, the discount terms may change every day!'),
			);
			break;
			case 'action_d_all':
			$decription_text = array(
				$this->__('It is simple! Choose the service or tool, select the friends and send them an invitation with a discount they can use at our store. You will get a discount for inviting your friends right after sending!'),
				$this->__('Don\'t forget to come here again, the discount amounts may change every day!'),
			);
			break;
		}
		
		return $decription_text;
	}
	

	public function canShowManualInvitation()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/services/manual', Mage::app()->getStore());
	}

	public function canShowInformation()
	{
		return Mage::getStoreConfig('addinmage_spreadtheword/general/information', Mage::app()->getStore());
	}

	public function getActionSubTitle()
	{
		switch ($this->_mode) {
			case 'noaction':
			$sub_title = $this->__('and shop together!');
			break;
			case 'action_d_sen':
			$sub_title = $this->__('and get a discount!');
			break;
			case 'action_d_frd':
			$sub_title = $this->__('and give them discounts!');
			break;
			case 'action_d_all':
			$sub_title = $this->__('get a discount by giving coupon codes to your friends!');
			break;
		}
		
		return $sub_title;
	}
}