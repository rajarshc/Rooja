<?php

$installer = $this;

$installer->startSetup();

//@nelkaake this will attempt to fix a temporary bug that was caused by early ST 1.3.4 releases.


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

//@nelkaake -a 26/01/11: This was going to clean tables that were affected by a previous bug in the setup system scripts.
//Mage::helper('rewards/mysql4_install')->dropColumns($installer, $this->getTable('sales_flat_quote'), array (
//        "`points_action`",
//        "`points_currency_id`",
//        "`points_amount`",
//        "`points_amount_step`",
//        "`points_amount_step_currency_id`",
//        "`points_max_qty`",
//        "`points_catalogrule_simple_action`",
//        "`points_catalogrule_discount_amount`",
//        "`points_catalogrule_stop_rules_processing`",
//));


$installer->endSetup();

