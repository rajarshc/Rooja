<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'use_tier_config', 'tinyint(1) NOT NULL default 1');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'max_level', 'int(10) NOT NULL default 0');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'tier_commission', 'text NULL');

$installer->endSetup();
