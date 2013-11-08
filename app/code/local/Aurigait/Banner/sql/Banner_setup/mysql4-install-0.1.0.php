<?php
 
$installer = $this;
 
$installer->startSetup();
 
$sql=<<<SQLTEXT

CREATE TABLE IF NOT EXISTS `banner_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `block_title` varchar(255) NOT NULL,
  `view_more_text` varchar(255) NOT NULL,
  `view_more_url` varchar(255) NOT NULL,
  `block_position` varchar(255) NOT NULL,
  `gender` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `banner` (
  `banner_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `block_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `bannerimage` varchar(255) DEFAULT NULL,
  `filethumbgrid` text,
  `image_text` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `target` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `status` smallint(6) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `banner_type` int(11) DEFAULT NULL,
  `content_heading` varchar(100) DEFAULT NULL,
  `content_text` varchar(150) DEFAULT NULL,
  `buttontext` varchar(20) DEFAULT NULL,
  `contentposition` varchar(20) DEFAULT NULL,
  `fontcolor` varchar(10) DEFAULT NULL,
  `position` varchar(20) DEFAULT NULL,
  `gender` int(11) NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
SQLTEXT;



$installer->run($sql);
 
$installer->endSetup();

