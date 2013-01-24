<?php

/* @var $this Aitoc_Aitsys_Model_Mysql4_Setup */
$this->startSetup();

$this->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('aitsys_performer')} (
    `id` INT(10) UNSIGNED NOT NULL auto_increment ,
    `product_id` INT(10) UNSIGNED NOT NULL ,
    `code` MEDIUMBLOB NOT NULL ,
    PRIMARY KEY ( `id` ) ,
    KEY `product_id` ( `product_id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

$this->endSetup();