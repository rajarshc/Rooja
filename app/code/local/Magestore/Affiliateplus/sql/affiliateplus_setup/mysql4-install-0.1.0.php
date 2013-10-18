<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('affiliateplus_referer')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplus_payment_paypal')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplus_payment')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplus_transaction')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplus_banner_value')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplus_banner')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplus_account_value')};
DROP TABLE IF EXISTS {$this->getTable('affiliateplus_account')};

CREATE TABLE {$this->getTable('affiliateplus_account')}(
  `account_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL,
  `address_id` int(10) unsigned NOT NULL default '0',
  `identify_code` varchar(63) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `balance` decimal(12,4) NOT NULL default '0',
  `total_commission_received` decimal(12,4) NOT NULL default '0',
  `total_paid` decimal(12,4) NOT NULL default '0',
  `total_clicks` int(11) NOT NULL default '0',
  `unique_clicks` int(11) NOT NULL default '0',
  `paypal_email` varchar(255) NOT NULL default '',
  `created_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL default '2',
  `approved` tinyint(1) NOT NULL default '2',
  UNIQUE(`customer_id`),
  UNIQUE(`identify_code`),
  INDEX(`customer_id`),
  FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer/entity')}(`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplus_account_value')}(
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned  NOT NULL,
  `attribute_code` varchar(63) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE(`account_id`,`store_id`,`attribute_code`),
  INDEX (`account_id`),
  INDEX (`store_id`),
  FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplus_banner')}(
  `banner_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `type_id` tinyint(1) NOT NULL default '1',
  `source_file` varchar(255) NOT NULL default '',
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplus_banner_value')}(
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `banner_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned  NOT NULL,
  `attribute_code` varchar(63) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE(`banner_id`,`store_id`,`attribute_code`),
  INDEX (`banner_id`),
  INDEX (`store_id`),
  FOREIGN KEY (`banner_id`) REFERENCES {$this->getTable('affiliateplus_banner')} (`banner_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplus_transaction')}(
  `transaction_id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `account_name` varchar(255) NOT NULL default '',
  `account_email` varchar(255)  NOT NULL,
  `customer_id` int(10) unsigned  NOT NULL,
  `customer_email` varchar(255)  NOT NULL,
  `order_id` int(10) unsigned  NOT NULL,
  `order_number` varchar(50) default '',
  `order_item_ids` text default '',
  `order_item_names` text default '',
  `total_amount` decimal(12,4) NOT NULL default '0',
  `commission` decimal(12,4) NOT NULL default '0',
  `discount` decimal(12,4) NOT NULL default '0',
  `created_time` datetime NULL,
  `status` tinyint(1) NOT NULL default '1',
  `store_id` smallint(5) unsigned  NOT NULL,
  INDEX(`account_id`),
  INDEX(`store_id`),
  FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplus_payment')}(
  `payment_id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned  NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_email` varchar(255) NOT NULL,
  `payment_method` varchar(63) NOT NULL default '',
  `amount` decimal(12,4) NOT NULL default '0',
  `fee` decimal(12,4) NOT NULL default '0',
  `request_time` datetime NULL,
  `status` tinyint(1) NOT NULL default '1',
  `description` text NOT NULL,
  `store_ids` text NOT NULL,
  `is_request` tinyint(1) NOT NULL default '1',
  `is_payer_fee` tinyint(1) NOT NULL default '1',
  INDEX(`account_id`),
  FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplus_payment_paypal')}(
  `payment_paypal_id` int(10) unsigned NOT NULL auto_increment,
  `payment_id` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL default '',
  `transaction_id` varchar(255) NOT NULL default '',
  `description` text NOT NULL default '',
  INDEX(`payment_id`),
  FOREIGN KEY (`payment_id`) REFERENCES {$this->getTable('affiliateplus_payment')} (`payment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`payment_paypal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('affiliateplus_referer')}(
  `referer_id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `referer` varchar(255) NOT NULL default '',
  `url_path` varchar(255) NOT NULL default '/',
  `total_clicks` int(11) NOT NULL default '0',
  `unique_clicks` int(11) NOT NULL default '0',
  `ip_list` longtext NOT NULL default '',
  `store_id` smallint(5) unsigned  NOT NULL,
  INDEX(`account_id`),
  INDEX(`store_id`),
  FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`referer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('sales/order')}
  ADD COLUMN `affiliateplus_discount` decimal(12,4) default NULL,
  ADD COLUMN `base_affiliateplus_discount` decimal(12,4) default NULL;		

");

$content = '
<p>Our program is free to join, it\'s easy to sign-up and requires no technical knowledge. Affiliate programs are common throughout the Internet and offers website owners an additional way to spread the word about their websites. Affiliates generate traffic and sales for commercial websites and in return receive commission payments.</p>
<h3>How Does It Work?</h3>
<p>When you join our affiliate program, you will be supplied with a range of banners and text links that you place wherever you like. When a user clicks on one of your links, they will be brought to our website and their activity will be tracked by our affiliate program. Once a purchase is completed from the traffic you send us, you earn commission!</p>
<h3>Real-Time Statistics and Reporting!</h3>
<p>Login 24 hours a day to check our sales, traffic, account balance and see how your banners are performing.</p>';

$cmsPage = array(
	'title' 		=> Mage::helper('affiliateplus')->__('Affiliate'),
	'identifier' 	=> 'affiliate-home',
	'content_heading' => Mage::helper('affiliateplus')->__('Welcome To Our Affiliate Program!'),
	'content' 		=> $content,
	'is_active' 	=> 1,
	'sort_order' 	=> 0,
	'stores' 		=> array(0),
	'root_template' => 'two_columns_left'
);
  
Mage::getModel('cms/page')->setData($cmsPage)->save();
				
$installer->endSetup(); 
