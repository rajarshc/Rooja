<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('affiliateplusprogram_transaction')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplusprogram_account')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplusprogram_product')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplusprogram_category')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplusprogram_value')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplusprogram')};

CREATE TABLE {$this->getTable('affiliateplusprogram')} (
  `program_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `created_date` date NULL,
  `status` tinyint(1) NOT NULL default '1',
  `expire_time` smallint(5) NOT NULL default'0',
  `num_account` int(11) NOT NULL default 0,
  `total_sales_amount` decimal(12,4) NOT NULL default 0,
  `commission_type` varchar(31) NOT NULL default '',
  `commission` decimal(12,4) NOT NULL default 0,
  `discount_type` varchar(31) NOT NULL default '',
  `discount` decimal(12,4) NOT NULL default 0,
  `autojoin` tinyint(1) NOT NULL default 0,
  `scope` tinyint(1) NOT NULL default 0,
  `customer_groups` text default '',
  `show_in_welcome` tinyint(1) NOT NULL default 0,
  PRIMARY KEY (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplusprogram_value')} (
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `program_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned  NOT NULL,
  `attribute_code` varchar(63) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE(`program_id`,`store_id`,`attribute_code`),
  INDEX (`program_id`),
  INDEX (`store_id`),
  FOREIGN KEY (`program_id`) REFERENCES {$this->getTable('affiliateplusprogram')} (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplusprogram_category')} (
	`id` int(10) unsigned NOT NULL auto_increment,
	`program_id` int(10) unsigned NOT NULL,
	`category_id` int(10) unsigned NOT NULL,
	`store_id` smallint(5) unsigned  NOT NULL,
	UNIQUE(`program_id`,`category_id`,`store_id`),
	INDEX (`program_id`),
	INDEX (`category_id`),
	INDEX (`store_id`),
	FOREIGN KEY (`program_id`) REFERENCES {$this->getTable('affiliateplusprogram')} (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`category_id`) REFERENCES {$this->getTable('catalog/category')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplusprogram_product')} (
	`id` int(10) unsigned NOT NULL auto_increment,
	`program_id` int(10) unsigned NOT NULL,
	`product_id` int(10) unsigned NOT NULL,
	`store_id` smallint(5) unsigned  NOT NULL,
	UNIQUE(`program_id`,`product_id`,`store_id`),
	INDEX (`program_id`),
	INDEX (`product_id`),
	INDEX (`store_id`),
	FOREIGN KEY (`program_id`) REFERENCES {$this->getTable('affiliateplusprogram')} (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplusprogram_account')} (
	`id` int(10) unsigned NOT NULL auto_increment,
	`program_id` int(10) unsigned NOT NULL,
	`account_id` int(10) unsigned NOT NULL,
	`joined` datetime NULL,
	UNIQUE(`program_id`,`account_id`),
	INDEX (`program_id`),
	INDEX (`account_id`),
	FOREIGN KEY (`program_id`) REFERENCES {$this->getTable('affiliateplusprogram')} (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplusprogram_transaction')} (
	`id` int(10) unsigned NOT NULL auto_increment,
	`transaction_id` int(10) unsigned NOT NULL,
	`program_id` int(10) unsigned NOT NULL,
	`account_id` int(10) unsigned NOT NULL,
	`program_name` varchar(255) NOT NULL,
	`account_name` varchar(255) NOT NULL default '',
	`order_id` int(10) unsigned  NOT NULL,
	`order_number` varchar(50) default '',
	`order_item_ids` text default '',
	`order_item_names` text default '',
	`total_amount` decimal(12,4) NOT NULL default '0',
	`commission` decimal(12,4) NOT NULL default '0',
	INDEX (`account_id`),
	INDEX (`transaction_id`),
	FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (`transaction_id`) REFERENCES {$this->getTable('affiliateplus_transaction')} (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('affiliateplus_banner')}
  ADD COLUMN `program_id` int(10) unsigned NOT NULL;

    ");

$installer->endSetup(); 