<?php

$installer = $this;

$installer->startSetup();

$quote = $this->getTable('sales/quote');

$installer->run("

ALTER TABLE `$quote` ADD `allow_alerts` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' ;

");

$installer->endSetup(); 
