<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('affiliatepluslevel_tier')};
DROP TABLE IF EXISTS {$this->getTable('affiliatepluslevel_transaction')};

CREATE TABLE {$this->getTable('affiliatepluslevel_tier')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tier_id` int(10) unsigned NOT NULL default '0',
  `toptier_id` int(10) unsigned NOT NULL default '0',
  `level` tinyint(3) unsigned NOT NULL default '0',
  UNIQUE(`tier_id`, `toptier_id`),
  FOREIGN KEY (`tier_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`toptier_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE {$this->getTable('affiliatepluslevel_transaction')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tier_id` int(10) unsigned NOT NULL default '0',
  `transaction_id` int(10) unsigned NOT NULL default '0',
  `level` tinyint(3) unsigned NOT NULL default '0',
  `commission` decimal(12,4) NOT NULL default '0.0000',
  UNIQUE(`tier_id`, `transaction_id`),
  FOREIGN KEY (`tier_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`transaction_id`) REFERENCES {$this->getTable('affiliateplus_transaction')} (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup(); 