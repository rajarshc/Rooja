<?php
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('ig_cashondelivery_local')};
CREATE TABLE {$this->getTable('ig_cashondelivery_local')} (
  `ig_cashondelivery_local_id` int(11) unsigned NOT NULL auto_increment,
  `amount_from` float NOT NULL,
  `apply_fee` float NOT NULL,
  `fee_mode` enum('percent','absolute') NOT NULL default 'absolute',
  PRIMARY KEY  (`ig_cashondelivery_local_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('ig_cashondelivery_foreign')};
CREATE TABLE {$this->getTable('ig_cashondelivery_foreign')} (
  `ig_cashondelivery_foreign_id` int(11) unsigned NOT NULL auto_increment,
  `amount_from` float NOT NULL,
  `apply_fee` float NOT NULL,
  `fee_mode` enum('percent','absolute') NOT NULL default 'absolute',
  PRIMARY KEY  (`ig_cashondelivery_foreign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
");
$installer->endSetup();
