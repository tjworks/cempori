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

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('spreadtheword_friends')};
CREATE TABLE IF NOT EXISTS {$this->getTable('spreadtheword_friends')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_type` enum('customer','visitor') NOT NULL DEFAULT 'visitor',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `source_type` enum('file','social','mail','manual') NOT NULL DEFAULT 'mail',
  `source` varchar(20) NOT NULL DEFAULT '',
  `stw_rule` int(10) unsigned NOT NULL,
  `discount_rule` int(11) unsigned NOT NULL,
  `friend_id` varchar(255) NOT NULL DEFAULT '',
  `friend_name` varchar(255) NOT NULL DEFAULT '',
  `friend_url` varchar(255) DEFAULT NULL,
  `friend_picture` varchar(255) DEFAULT NULL,
  `friend_invite_link` varchar(7) NOT NULL DEFAULT '',
  `invited` tinyint(1) NOT NULL DEFAULT '0',
  `invited_time` datetime DEFAULT NULL,
  `time_reinvited` int(10) unsigned NOT NULL DEFAULT '0',
  `invite_link_used` tinyint(1) NOT NULL DEFAULT '0',
  `invite_link_used_time` datetime DEFAULT NULL,
  `already_customer` tinyint(1) NOT NULL DEFAULT '0',
  `time` datetime NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('spreadtheword_rules')};
CREATE TABLE IF NOT EXISTS {$this->getTable('spreadtheword_rules')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(255) NOT NULL DEFAULT '',
  `rule_mode` enum('noaction','action_d_sen','action_d_frd','action_d_all') NOT NULL DEFAULT 'noaction',
  `sender_targeting` tinyint(1) NOT NULL DEFAULT '0',
  `configuration` blob NOT NULL,
  `friends_targeting` tinyint(1) NOT NULL DEFAULT '0',
  `conflicts` tinyint(1) NOT NULL DEFAULT '0',
  `errors` blob NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('spreadtheword_sales')};
CREATE TABLE IF NOT EXISTS {$this->getTable('spreadtheword_sales')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stw_id` int(11) unsigned NOT NULL,
  `stw_rule` int(10) unsigned NOT NULL,
  `source` varchar(20) NOT NULL DEFAULT '',
  `short_discount` varchar(6) NOT NULL DEFAULT '',
  `real_discount` varchar(255) NOT NULL DEFAULT '',
  `user_type` enum('sender','friend') NOT NULL DEFAULT 'friend',
  PRIMARY KEY (`id`),
  KEY `FK_STW_ID` (`stw_id`),
  CONSTRAINT `FK_STW_ID` FOREIGN KEY (`stw_id`) REFERENCES {$this->getTable('spreadtheword_friends')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('spreadtheword_queue_data')};
CREATE TABLE IF NOT EXISTS {$this->getTable('spreadtheword_queue_data')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(100) NOT NULL DEFAULT '',
  `type` enum('noaction','discount') NOT NULL DEFAULT 'noaction',
  `discount_rule` int(11) DEFAULT NULL,
  `simple_action` varchar(32) NOT NULL,
  `promised_amount` decimal(12,4) DEFAULT NULL,
  `source` varchar(20) NOT NULL DEFAULT '',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sender_name` varchar(255) NOT NULL DEFAULT '',
  `sender_email` varchar(255) NOT NULL DEFAULT '',
  `personal_message` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('spreadtheword_queue')};
CREATE TABLE IF NOT EXISTS {$this->getTable('spreadtheword_queue')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `friend_id` int(11) unsigned NOT NULL,
  `data_id` int(11) unsigned NOT NULL,
  `status` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_STW_QUEUE_DATA_ID` (`data_id`),
  KEY `FK_STW_FRIEND_ID` (`friend_id`),
  CONSTRAINT `FK_STW_QUEUE_DATA_ID` FOREIGN KEY (`data_id`) REFERENCES {$this->getTable('spreadtheword_queue_data')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_STW_FRIEND_ID` FOREIGN KEY (`friend_id`) REFERENCES {$this->getTable('spreadtheword_friends')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('spreadtheword_queue_problems')};
CREATE TABLE IF NOT EXISTS {$this->getTable('spreadtheword_queue_problems')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) unsigned NOT NULL,
  `failed_at` datetime DEFAULT NULL,
  `error_code` int(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_STW_PROBLEM_ID` (`queue_id`),
  CONSTRAINT `FK_STW_PROBLEM_ID` FOREIGN KEY (`queue_id`) REFERENCES {$this->getTable('spreadtheword_queue')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('spreadtheword_services')};
CREATE TABLE IF NOT EXISTS {$this->getTable('spreadtheword_services')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `type` enum('social','mail','file') NOT NULL DEFAULT 'mail',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('spreadtheword_services')} (`id`, `name`, `type`) VALUES
(1, 'facebook', 'social'),
(2, 'linkedin', 'social'),
(3, 'mailru', 'social'),
(4, 'twitter', 'social'),
(5, 'yammer', 'social'),
(6, 'gmail', 'mail'),
(7, 'outlook', 'mail'),
(8, 'yahoo', 'mail'),
(9, 'ldif', 'file'),
(10, 'vcard', 'file');
");

if (version_compare(Mage::getVersion(), '1.4.1.0') >= 0) {
	try {
		$installer->run("ALTER TABLE {$this->getTable('sales/order')} ADD COLUMN `stw_sales_id` int(11) DEFAULT NULL");
		
		$customer_params = array ('type' => 'int', 'label' => 'STW Customer Id', 'required' => false, 'global' => 1, 'visible' => 0, 'default' => 0);
		$installer->addAttribute('customer', 'stw_customer_id', $customer_params);
	
	} catch (Exception $e) {
		Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
	}
} else {
	try {
		$stw_params = array ('type' => 'int', 'label' => 'STW Sales Id', 'required' => 0, 'global' => 1, 'visible' => 0, 'default' => 0);
		$installer->addAttribute('order', 'stw_sales_id', $stw_params);
		
		$customer_params = array ('type' => 'int', 'label' => 'STW Customer Id', 'required' => false, 'global' => 1, 'visible' => 0, 'default' => 0);
		$installer->addAttribute('customer', 'stw_customer_id', $customer_params);
	
	} catch (Exception $e) {
		Mage::helper('spreadtheword')->stwLog(__METHOD__, $e);
	}
}

$installer->endSetup();

Mage::helper('spreadtheword/setup')->notifyAdminUser();
