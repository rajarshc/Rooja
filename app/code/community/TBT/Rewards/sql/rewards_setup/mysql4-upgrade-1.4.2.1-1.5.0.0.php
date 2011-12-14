<?php
$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, 
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('rewards_catalogrule_label')}` (
  `label_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label_id`),
  UNIQUE KEY `IDX_REWARDS_CATALOGRULE_STORE` (`rule_id`,`store_id`),
  KEY `FK_REWARDS_CATALOGRULE_LABEL_STORE` (`store_id`),
  KEY `FK_REWARDS_CATALOGRULE_LABEL_RULE` (`rule_id`),
  CONSTRAINT `FK_REWARDS_CATALOGRULE_LABEL_RULE` FOREIGN KEY (`rule_id`) 
  	REFERENCES `{$this->getTable('catalogrule')}` (`rule_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_REWARDS_CATALOGRULE_LABEL_STORE` FOREIGN KEY (`store_id`) 
  	REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
" );

// Either the index is being built for the first time, or the database is being recoverd for Sweet Tooth 
Mage::helper( 'rewards/customer_points_index' )->invalidate();

$installer->endSetup(); 
