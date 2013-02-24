<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Referafriend
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('referafriend/rule'), 'updated', 'DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'');
$installer->getConnection()->addColumn($this->getTable('referafriend/history'), 'discount_id', 'INT(3) NOT NULL AFTER `order_id`');
$installer->getConnection()->addColumn($this->getTable('referafriend/rule'), 'status', 'INT(3) NOT NULL DEFAULT 1');
$installer->getConnection()->addColumn($this->getTable('referafriend/rule'), 'visibility', 'INT(3) NOT NULL DEFAULT 1');
$installer->getConnection()->addColumn($this->getTable('referafriend/rule'), 'last_rule', 'INT(3) NOT NULL DEFAULT 0');
$installer->getConnection()->addColumn($this->getTable('referafriend/rule'), 'store_id', 'VARCHAR(200) NOT NULL DEFAULT 0 AFTER `rule_id`');
$installer->getConnection()->addColumn($this->getTable('referafriend/turnover'), 'store_id', 'VARCHAR(200) NOT NULL DEFAULT 0 AFTER `order_id`');
$installer->getConnection()->addColumn($this->getTable('referafriend/discount'), 'earned', 'INT(3) NOT NULL DEFAULT 0 AFTER `rule_id`');
$installer->getConnection()->addColumn($this->getTable('referafriend/invite'), 'referral_status', 'INT(3) NOT NULL AFTER `referral_email`');
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('referafriend/usedorders')};
CREATE TABLE {$this->getTable('referafriend/usedorders')} (
  `id` int(10) NOT NULL auto_increment,
  `order_id` int(10) NOT NULL,
  `rule_id` smallint(4) NOT NULL,
  `used_qty` smallint(5) NOT NULL,
  `used_amount` decimal(12,4) NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('referafriend/usedsignups')};
CREATE TABLE {$this->getTable('referafriend/usedsignups')} (
  `id` int(10) NOT NULL auto_increment,
  `referrer_id` int(10) NOT NULL,
  `rule_id` smallint(4) NOT NULL,
  `used_signups` smallint(5) NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('referafriend/discounthistory')};
CREATE TABLE {$this->getTable('referafriend/discounthistory')} (
  `id` int(10) NOT NULL auto_increment,
  `order_id` int(10) NOT NULL,
  `discount_id` int(10) NOT NULL,
  `rule_id` int(10) NOT NULL,
  `referrer_id` int(10) NOT NULL,
  `discount_type` tinyint(1) NOT NULL,
  `discount_amount` decimal(12,4) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");
$installer->endSetup();