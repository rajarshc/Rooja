<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('affiliatepluslevel_transaction')}
  ADD COLUMN `commission_plus` decimal(12,4) NOT NULL default '0';

");
$installer->getConnection()->resetDdlCache($this->getTable('affiliatepluslevel_transaction'));

$installer->endSetup();