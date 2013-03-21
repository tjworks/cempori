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

$installer->run("

ALTER TABLE {$this->getTable('aw_raf_rule')} ADD COLUMN `total_greater` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0' AFTER `action_amount`;
ALTER TABLE {$this->getTable('aw_raf_rule')} ADD COLUMN `discount_greater` DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0' AFTER `total_greater`;
ALTER TABLE {$this->getTable('aw_raf_rule')} ADD COLUMN `allow_additional_discount` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `discount_greater`;

DROP TABLE IF EXISTS {$this->getTable('aw_raf_usedlink')};
CREATE TABLE {$this->getTable('aw_raf_usedlink')} (
  `used_id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_id` INTEGER(10) UNSIGNED NOT NULL,
  `referrer_id` INTEGER(10) UNSIGNED NOT NULL,
  `order_id` INTEGER(11) UNSIGNED NOT NULL,
  `used` TINYINT(1) default 1,
  PRIMARY KEY (`used_id`),
  KEY `FK_raf_rule_id` (`rule_id`),
  KEY `referrer_id` (`referrer_id`),
  CONSTRAINT `FK_raf_rule_id` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('aw_raf_rule')}` (`rule_id`) ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

DROP TABLE IF EXISTS {$this->getTable('aw_raf_history')};
CREATE TABLE {$this->getTable('aw_raf_history')} (
  `history_id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `referrer_id` INTEGER(10) UNSIGNED NOT NULL,
  `order_id` INTEGER(11) UNSIGNED NOT NULL,
  `amount` DECIMAL(12,4) UNSIGNED NOT NULL,
  `used_at` DATETIME NOT NULL,
  PRIMARY KEY (`history_id`),
  KEY `FK_raf_referrer_id` (`referrer_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `FK_raf_referrer_id` FOREIGN KEY (`referrer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';
");

$installer->endSetup();
