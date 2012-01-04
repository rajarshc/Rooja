<?php

$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, 
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('rewards_currency')}` (
    `rewards_currency_id` INT(11) NOT NULL AUTO_INCREMENT,
    `caption` VARCHAR(100) NOT NULL,
    `value` DECIMAL(11,8) NOT NULL DEFAULT '1',
    `active` TINYINT(1) NOT NULL DEFAULT '1',
    `image` VARCHAR(200),
    `image_width` SMALLINT(6),
    `image_height` SMALLINT(6),
    `image_write_quantity` TINYINT(2),
    `font` VARCHAR(200),
    `font_size` SMALLINT(6),
    `font_color` INT(11),
    PRIMARY KEY (`rewards_currency_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

" );

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, 
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('rewards_customer')}` (
    `rewards_customer_id` INT(11) NOT NULL AUTO_INCREMENT,
    `rewards_currency_id` INT(11) NOT NULL,
    `customer_entity_id` INT(10) unsigned NOT NULL,
    PRIMARY KEY (`rewards_customer_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
" );

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, 
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('rewards_special')}` (
    `rewards_special_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NOT NULL,
    `from_date` DATE,
    `to_date` DATE,
    `customer_group_ids` VARCHAR(255) NOT NULL,
    `is_active` TINYTEXT NOT NULL,
    `conditions_serialized` MEDIUMTEXT NOT NULL,
    `points_action` VARCHAR(25),
    `points_currency_id` INT(11),
    `points_amount` INT(11),
    `website_ids` TEXT,
    `is_rss` TINYINT(4) NOT NULL DEFAULT '0',
    `sort_order` INT(10) NOT NULL DEFAULT '0',
    PRIMARY KEY (`rewards_special_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
" );

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, 
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('rewards_store_currency')}` (
    `rewards_store_currency_id` INT(11) NOT NULL AUTO_INCREMENT,
    `currency_id` INT(11) NOT NULL,
    `store_id` INT(11) NOT NULL,
    PRIMARY KEY (`rewards_store_currency_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
" );

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, 
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('rewards_transfer')}` (
    `rewards_transfer_id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(10) unsigned NOT NULL,
    `quantity` INT(11) NOT NULL DEFAULT '1',
    `comments` VARCHAR(200) DEFAULT '',
    `effective_start` TIMESTAMP,
    `expire_date` TIMESTAMP,
    `status` INT(11) NOT NULL DEFAULT '0',
    `currency_id` INT(11) NOT NULL,
    `creation_ts` TIMESTAMP,
    `reason_id` INT(11) NOT NULL,
    `last_update_ts` TIMESTAMP,
    `issued_by` VARCHAR(60) NOT NULL,
    `last_update_by` VARCHAR(60) NOT NULL,
    PRIMARY KEY (`rewards_transfer_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
" );

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, 
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('rewards_transfer_reference')}` (
    `rewards_transfer_reference_id` INT(11) NOT NULL AUTO_INCREMENT,
    `reference_type` INT(11) NOT NULL,
    `reference_id` INT(11) NOT NULL,
    `rewards_transfer_id` INT(11) NOT NULL,
    `rule_id` INT(11),
    PRIMARY KEY (`rewards_transfer_reference_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
" );

// Add foreign key constraint to points transfers table
Mage::helper( 'rewards/mysql4_install' )->addFKey( $installer, 'rewards_transfer_reference_fk', 
    $this->getTable( 'rewards_transfer_reference' ), 'rewards_transfer_id', $this->getTable( 'rewards_transfer' ), 'rewards_transfer_id', 
    'CASCADE', 'NO ACTION' );

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, 
    "
INSERT INTO `{$this->getTable('rewards_currency')}` (`caption`,`value`,`active`,`image`,`image_width`,`image_height`,`image_write_quantity`,`font`,`font_size`,`font_color`)
    SELECT '','1','1','','','','','','',''
        FROM dual
    WHERE NOT EXISTS (
        SELECT * FROM `{$this->getTable('rewards_currency')}`
    );
" );

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'catalogrule' ), 
    array(
        "`points_action` VARCHAR(25)", 
        "`points_currency_id` INT(11)", 
        "`points_amount` INT(11)", 
        "`points_amount_step` FLOAT(9,2) DEFAULT '1'", 
        "`points_amount_step_currency_id` INT(11)", 
        "`points_max_qty` INT(11)", 
        "`points_catalogrule_simple_action` VARCHAR(32)", 
        "`points_catalogrule_discount_amount` DECIMAL(12,4)", 
        "`points_catalogrule_stop_rules_processing` TINYINT(1) DEFAULT '1'"
    ) );

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'catalogrule_product_price' ), 
    array(
        "`rules_hash` TEXT"
    ) );

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'sales_flat_quote' ), 
    array(
        "`cart_redemptions` TEXT", 
        "`applied_redemptions` TEXT"
    ) );

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'sales_flat_quote_item' ), 
    array(
        "`earned_points_hash` TEXT", 
        "`redeemed_points_hash` TEXT", 
        "`row_total_before_redemptions` DECIMAL(12,4) NOT NULL DEFAULT '0'"
    ) );

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'salesrule' ), 
    array(
        "`points_action` VARCHAR(25)", 
        "`points_currency_id` INT(11)", 
        "`points_amount` INT(11)", 
        "`points_amount_step` FLOAT(9,2) DEFAULT '1'", 
        "`points_amount_step_currency_id` INT(11)", 
        "`points_qty_step` INT(11) DEFAULT '1'", 
        "`points_max_qty` INT(11)"
    ) );

$msg_title = "Sweet Tooth was successfully installed!";
$msg_desc = "Sweet Tooth was successfully installed on your store.  Remember to go to the Sweet Tooth configuration and enter in your license key under Registration Information section.";
Mage::helper( 'rewards/mysql4_install' )->createInstallNotice( $msg_title, $msg_desc );

$installer->endSetup(); 

