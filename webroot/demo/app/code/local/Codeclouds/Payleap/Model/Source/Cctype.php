<?php
/**
 * Payleap Payment CC Types Source Model
 *
 * @category    Codeclouds
 * @package     Codeclouds_Payleap
 * @author      Somnath Sinha <som@codeclouds.com>
 */
class Codeclouds_Payleap_Model_Source_Cctype extends Mage_Payment_Model_Source_Cctype
{
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DI');
    }
}