<?php
/**
 * Payleap Payment Model
 *
 * @category    Codeclouds
 * @package     Codeclouds_Payleap
 * @author      Somnath Sinha <som@codeclouds.com>
 */
class Codeclouds_Payleap_Model_Mysql4_Payleap_Debug_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract 
{
	protected function _construct()
	{
		$this->_init('payleap/payleap_debug');
	}
}