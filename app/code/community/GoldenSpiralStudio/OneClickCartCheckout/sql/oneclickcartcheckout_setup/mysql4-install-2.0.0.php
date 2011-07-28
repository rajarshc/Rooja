<?php


$installer = $this;

$installer->startSetup();
/*/
 * array(8) {
  ["template_id"]=>
  string(1) "1"
  ["template_code"]=>
  string(16) "Reminder Default"
  ["template_type"]=>
  string(1) "2"
  ["template_subject"]=>
  string(16) "Default Reminder"
  ["template_sender_name"]=>
  string(15) "CustomerSupport"
  ["template_sender_email"]=>
  string(19) "support@example.com"
  ["added_at"]=>
  string(19) "2011-07-02 10:07:39"
  ["modified_at"]=>
  string(19) "2011-07-02 10:37:20"
}
 * */

//$installer->run("
//
//-- DROP TABLE IF EXISTS {$this->getTable('oneclickcartcheckout')};
//CREATE TABLE {$this->getTable('oneclickcartcheckout')} (
//  `oneclickcartcheckout_id` int(11) unsigned NOT NULL auto_increment,
//  `title` varchar(255) NOT NULL default '',
//  `filename` varchar(255) NOT NULL default '',
//  `content` text NOT NULL default '',
//  `status` smallint(6) NOT NULL default '0',
//  `created_time` datetime NULL,
//  `update_time` datetime NULL,
//  PRIMARY KEY (`oneclickcartcheckout_id`)
//) ENGINE=InnoDB DEFAULT CHARSET=utf8;
//
//    ");

$installer->endSetup(); 