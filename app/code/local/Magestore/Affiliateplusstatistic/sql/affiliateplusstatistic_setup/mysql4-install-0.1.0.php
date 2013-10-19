<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

if (!$installer->tableExists($installer->getTable('affiliateplusstatistic'))) {
    $installer->run("

CREATE TABLE {$this->getTable('affiliateplusstatistic')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `referer_id` int(10) unsigned NOT NULL,
  `referer` varchar(255) NOT NULL default '',
  `url_path` varchar(255) NOT NULL default '/',
  `ip_address` varchar(63) NOT NULL default '',
  `visit_at` datetime NULL,
  `store_id` smallint(5) NOT NULL default 0,
  INDEX (`referer_id`),
  FOREIGN KEY (`referer_id`) REFERENCES {$this->getTable('affiliateplus_referer')} (`referer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
}

$installer->endSetup(); 
