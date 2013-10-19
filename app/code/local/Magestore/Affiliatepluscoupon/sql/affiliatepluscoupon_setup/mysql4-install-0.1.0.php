<?php

$installer = $this;
$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('affiliateplus_account')}
  ADD COLUMN `coupon_code` varchar(255) default '' AFTER `identify_code`;

");

$installer->getConnection()->resetDdlCache($this->getTable('affiliateplus_account'));
$installer->endSetup(); 