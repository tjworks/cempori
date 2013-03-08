<?php
/**
 * Payleap Payment Payment Action Source Model
 *
 * @category    Codeclouds
 * @package     Codeclouds_Payleap
 * @author      Somnath Sinha <som@codeclouds.com>
 */
class Codeclouds_Payleap_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
                'label' => Mage::helper('payleap')->__('Authorize Only')
            ),
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('payleap')->__('Authorize and Capture')
            ),
        );
    }
}