<?php



$installer = $this;
/* @var $installer Mage_avenues_Model_Mysql4_Setup */

$installer->startSetup();

$installer->run("
CREATE TABLE `{$this->getTable('avenues_api_debug')}` (
  `debug_id` int(10) unsigned NOT NULL auto_increment,
  `debug_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `request_body` text,
  `response_body` text,
  PRIMARY KEY  (`debug_id`),
  KEY `debug_at` (`debug_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();