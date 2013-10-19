<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('sales/order')}
  ADD COLUMN `affiliateplus_coupon` varchar(100) default '';

");

$installer->getConnection()->resetDdlCache($this->getTable('sales/order'));

$installer->endSetup();
