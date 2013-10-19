<?php

$installer = $this;

$installer->startSetup();

/*
$installer->run("
DELETE FROM `{$this->getTable('core_config_data')}` WHERE `{$this->getTable('core_config_data')}`.`path` = 'affiliateplus/general/commission';
DELETE FROM `{$this->getTable('core_config_data')}` WHERE `{$this->getTable('core_config_data')}`.`path` = 'affiliateplus/general/discount';
");
*/

$installer->endSetup();
