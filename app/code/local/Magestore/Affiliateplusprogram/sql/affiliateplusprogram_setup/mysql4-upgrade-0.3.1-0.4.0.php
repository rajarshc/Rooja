<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'sec_commission', 'tinyint(1) NOT NULL default 0');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'sec_commission_type', 'varchar(31) NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'secondary_commission', 'decimal(12,4) NOT NULL default 0');


$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'sec_discount', 'tinyint(1) NOT NULL default 0');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'sec_discount_type', 'varchar(31) NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'secondary_discount', 'decimal(12,4) NOT NULL default 0');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'customer_group_ids', "text default ''");

// Update field to work with tier-commission v4.0
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'use_sec_tier', 'tinyint(1) NOT NULL default 0');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'sec_tier_commission', 'mediumtext NULL');

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('affiliateplusprogram_joined')};
CREATE TABLE {$this->getTable('affiliateplusprogram_joined')} (
	`id` int(10) unsigned NOT NULL auto_increment,
	`program_id` int(10) unsigned NOT NULL,
	`account_id` int(10) unsigned NOT NULL,
	UNIQUE(`program_id`,`account_id`),
	INDEX (`program_id`),
	INDEX (`account_id`),
	FOREIGN KEY (`program_id`) REFERENCES {$this->getTable('affiliateplusprogram')} (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

// Update current joined
$selectSQL = $installer->getConnection()->select()->reset()
    ->from(array('a' => $installer->getTable('affiliateplusprogram_account')), array())
    ->columns(array('program_id', 'account_id'));
$insertSQL = $selectSQL->insertFromSelect($installer->getTable('affiliateplusprogram_joined'),
    array('program_id', 'account_id'),
    true
);
$installer->getConnection()->query($insertSQL);

$installer->endSetup();
