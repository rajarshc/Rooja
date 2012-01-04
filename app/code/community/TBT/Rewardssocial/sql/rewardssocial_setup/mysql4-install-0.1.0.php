<?php

$installer = $this;
$installer->startSetup();

// Create facebook like table
$installer->attemptQuery($installer, "
    CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardssocial/facebook_like')}` (
        `facebook_like_id` smallint(11) unsigned NOT NULL auto_increment,
        `customer_id` int(10) unsigned NOT NULL,
        `url` varchar(255) NOT NULL default '',
        `facebook_account_id` varchar(32) NOT NULL default '',
        `type` smallint(5) unsigned default '0',
  		`created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (`facebook_like_id`),
        KEY `IDX_CUSTOMER` (`customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Facebook Likes';
");
    
$installer->getConnection()->addConstraint(
    "FK_FACEBOOK_LIKE_CUSTOMER",
    $this->getTable('rewardssocial/facebook_like'),
    'customer_id',
    $this->getTable('customer/entity'),
    'entity_id'
);
//
//$msg_title = "Sweet Tooth Social 0.1.0 Was Installed Sucessfully";
//$msg_desc = "Sweet Tooth Social Was Installed Successfully.";
//$msg_url = "http://www.sweettoothrewards.com/wiki/index.php/Social";
//
//$installer->createInstallNotice($msg_title, $msg_desc, $msg_url);

$installer->endSetup();

?>
