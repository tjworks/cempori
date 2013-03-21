<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('aw_raf_rule')};
CREATE TABLE {$this->getTable('aw_raf_rule')} (
  `rule_id` INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `target_type` TINYINT(1) UNSIGNED NOT NULL,
  `target_amount` DECIMAL(12,4) UNSIGNED NOT NULL,
  `action_type` TINYINT(1) UNSIGNED NOT NULL,
  `action_amount` DECIMAL(12,4) UNSIGNED NOT NULL,
  `priority` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0',
  `applies` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`rule_id`)
)ENGINE=InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

DROP TABLE IF EXISTS {$this->getTable('aw_raf_invite')};
CREATE TABLE {$this->getTable('aw_raf_invite')} (
  `invite_id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `referrer_id` INTEGER(10) UNSIGNED NOT NULL,
  `referral_id` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0',
  `referral_name` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `referral_email` VARCHAR(255) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`invite_id`),
  KEY `referrer_id` (`referrer_id`),
  KEY `referral_id` (`referral_id`),
  KEY `count` (`referrer_id`, `referral_id`)
)ENGINE=InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

DROP TABLE IF EXISTS {$this->getTable('aw_raf_referral_turnover')};
CREATE TABLE {$this->getTable('aw_raf_referral_turnover')} (
  `order_id` INTEGER(11) UNSIGNED NOT NULL,
  `referral_id` INTEGER(10) UNSIGNED NOT NULL,
  `purchased_qty` SMALLINT(5) UNSIGNED NOT NULL,
  `purchase_amount` DECIMAL(12,4) UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `referral_id` (`referral_id`)
)ENGINE=InnoDB
CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';

DROP TABLE IF EXISTS {$this->getTable('aw_raf_referrer_discount')};
CREATE TABLE {$this->getTable('aw_raf_referrer_discount')} (
  `discount_id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `referrer_id` INTEGER(10) UNSIGNED NOT NULL,
  `rule_id` INTEGER(10) UNSIGNED NOT NULL,
  `type` TINYINT(1) UNSIGNED NOT NULL,
  `amount` DECIMAL(12,4) UNSIGNED NOT NULL,
  `priority` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`discount_id`),
  KEY `referrer_id` (`referrer_id`)
)ENGINE=InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';
");

$installer->endSetup();

