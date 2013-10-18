<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('affiliateplus_transaction')}
  ADD COLUMN `percent_plus` decimal(12,4) NOT NULL default '0',
  ADD COLUMN `commission_plus` decimal(12,4) NOT NULL default '0';

");
$installer->getConnection()->resetDdlCache($this->getTable('affiliateplus_transaction'));

$installer->endSetup();