<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('adjcartalert')} ADD `status` ENUM( 'pending', 'invalid' ) DEFAULT 'pending' NOT NULL AFTER `is_preprocessed` ;
UPDATE {$this->getTable('adjcartalert')} SET `status` = 'pending';

ALTER TABLE {$this->getTable('adjcartalert')} ADD `abandoned_at` DATETIME NOT NULL AFTER `status` ;

CREATE TABLE {$this->getTable('adjcartalert_history')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `sent_at` datetime NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `txt` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup(); 