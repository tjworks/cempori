<?php
/**
 * Payleap Payment Model
 *
 * @category    Codeclouds
 * @package     Codeclouds_Payleap
 * @author      Somnath Sinha <som@codeclouds.com>
 */

$installer = $this;
/* @var $installer Codeclouds_Payleap_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

-- DROP TABLE if exists {$this->getTable('payleap_debug')};
CREATE TABLE {$this->getTable('payleap_debug')} (
  `debug_id` int(10) unsigned NOT NULL auto_increment,
  `request_body` text,
  `response_body` text,
  `request_serialized` text,
  `result_serialized` text,
  `request_dump` text,
  `result_dump` text,
  PRIMARY KEY  (`debug_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
