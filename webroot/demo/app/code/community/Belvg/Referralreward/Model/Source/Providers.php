<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Belvg_Referralreward_Model_Source_Providers {
    protected $_options;

    public function toOptionArray($isMultiselect=false) {

        if (!$this->_options) {
            $inviter        = Mage::getModel('referralreward/openinviter');
            $plugins        = $inviter->getOpenIniviterPlugins();
            $this->_options = array();
            foreach ($plugins['email'] as $provider=>$details) {
                $this->_options[] = array('value' => $provider, 'label' => $details["name"]);
            }
        }
        //print_r($this->_options); die;

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, array('value' => '', 'label' => Mage::helper('referralreward')->__('--Please Select--')));
        }

        return $options;
    }
}
