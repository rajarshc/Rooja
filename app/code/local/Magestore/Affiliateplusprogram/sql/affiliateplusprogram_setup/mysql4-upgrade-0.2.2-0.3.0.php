<?php

$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE {$this->getTable('affiliateplusprogram_transaction')}
  ADD COLUMN `type` TINYINT(2) NOT NULL default '3';
      
ALTER TABLE {$this->getTable('affiliateplusprogram')} CHANGE coupon_pattern coupon_pattern varchar(255) NULL

");


$installer->endSetup();