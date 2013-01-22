<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('fileupload')};
CREATE TABLE `fileupload` (
 `fileupload_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(100) NOT NULL DEFAULT '',
  `branch_detail` varchar(100) NOT NULL DEFAULT '',
  `served_by` varchar(100) NOT NULL DEFAULT '',
  `pincode` varchar(6) NOT NULL DEFAULT '0',
  `area` varchar(100) NOT NULL DEFAULT '0',
  `file_type` varchar(100) NOT NULL DEFAULT '0',
  `state` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`fileupload_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12123 ;


    ");

$installer->endSetup();   





