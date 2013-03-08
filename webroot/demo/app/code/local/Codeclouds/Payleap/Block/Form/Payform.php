<?php
/**
 * Payleap Payment From
 *
 * @category    Codeclouds
 * @package     Codeclouds_Payleap
 * @author      Somnath Sinha <som@codeclouds.com>
 */
class Codeclouds_Payleap_Block_Form_Payform extends Mage_Payment_Block_Form_Cc
{
    function _construct()  
	{
		parent::_construct();
		$this->setTemplate('payleap/form/payform.phtml');
	}
}
?>