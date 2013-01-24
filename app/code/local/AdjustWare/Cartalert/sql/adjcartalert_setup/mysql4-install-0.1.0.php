<?php

$installer = $this;

$installer->startSetup();

$date = date('Y-m-d H:i:s', time()-3600*24*14); // two previous weeks

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('adjcartalert')};
CREATE TABLE {$this->getTable('adjcartalert')} (
  `cartalert_id` int(11) unsigned NOT NULL auto_increment,
  `store_id` smallint(5) unsigned NOT NULL,
  `is_preprocessed` tinyint(1) unsigned NOT NULL default '0',
  `customer_email` varchar(255) NOT NULL,
  `customer_fname` varchar(255) NOT NULL,
  `customer_lname` varchar(255) NOT NULL,
  `products` text NOT NULL,
  PRIMARY KEY  (`cartalert_id`)
) ENGINE=InnoDB CHARSET=utf8;

INSERT INTO {$this->getTable('core/config_data')} (`scope` , `scope_id` , `path` , `value` )
    VALUES ('default', '0', 'catalog/adjcartalert/from_date', '$date');
");

$installer->endSetup(); 