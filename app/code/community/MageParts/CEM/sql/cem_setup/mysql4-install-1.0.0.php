<?php
/**
 * MageParts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   MageParts
 * @package    MageParts_Adminhtml
 * @copyright  Copyright (c) 2009 MageParts (http://www.mageparts.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Create tables
$installer->run("

	/*==============================	SERVICES	==============================*/

	CREATE TABLE IF NOT EXISTS `{$this->getTable('cem_services')}` (
	  `service_id` int(11) unsigned NOT NULL auto_increment,
	  `url` varchar(255) NOT NULL,
	  PRIMARY KEY  (`service_id`),
	  UNIQUE KEY `UK_url` (`url`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	
	
	
	/*==============================	LICENSES	==============================*/

	CREATE TABLE IF NOT EXISTS `{$this->getTable('cem_licenses')}` (
	  `license_id` int(11) unsigned NOT NULL auto_increment,
	  `license_key` varchar(255) NOT NULL,
	  PRIMARY KEY  (`license_id`),
	  UNIQUE KEY `FK_license_key` (`license_key`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	
	
	
	/*==============================	PACKAGES	==============================*/

	CREATE TABLE IF NOT EXISTS `{$this->getTable('cem_packages')}` (
	  `package_id` int(11) unsigned NOT NULL auto_increment,
	  `service_id` int(11) unsigned NOT NULL,
	  `license_id` int(11) unsigned NOT NULL,
	  `module_id` int(11) NOT NULL,
	  `identifier` varchar(100) NOT NULL,
	  `title` varchar(100) NOT NULL,
	  `version` decimal(12,4) NOT NULL,
	  `identifier_rollback` varchar(100),
	  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  `update_available` tinyint(1) DEFAULT '0',
	  `auto_update` tinyint(1) NOT NULL DEFAULT '0',
	  PRIMARY KEY  (`package_id`),
	  UNIQUE KEY `UK_identifier` (`identifier`),
	  UNIQUE KEY `FK_license_id` (`license_id`),
	  KEY `FK_service_id` (`service_id`),
  	  CONSTRAINT `FK_service_id` FOREIGN KEY (`service_id`) REFERENCES {$this->getTable('cem_services')} (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  	  CONSTRAINT `FK_license_id` FOREIGN KEY (`license_id`) REFERENCES {$this->getTable('cem_licenses')} (`license_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	
	
	
	/*==============================	CEM KEYS	==============================*/

	CREATE TABLE IF NOT EXISTS `{$this->getTable('cem_service_keys')}` (
	  `key_id` int(11) unsigned NOT NULL auto_increment,
	  `service_id` int(11) unsigned NOT NULL,
	  `key` varchar(255) NOT NULL,
	  PRIMARY KEY  (`key_id`),
	  UNIQUE KEY `FK_service_id` (`service_id`),
	  UNIQUE KEY `UK_key` (`key`),
	  CONSTRAINT `FK_service_id2` FOREIGN KEY (`service_id`) REFERENCES {$this->getTable('cem_services')} (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
");


$installer->endSetup();