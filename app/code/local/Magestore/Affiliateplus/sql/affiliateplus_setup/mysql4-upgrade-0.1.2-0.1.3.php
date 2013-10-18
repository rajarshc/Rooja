<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('affiliateplus_account'), 'notification', 'tinyint(1) NOT NULL default 1');

$installer->endSetup();