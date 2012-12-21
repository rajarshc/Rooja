<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('login')};
CREATE TABLE IF NOT EXISTS {$this->getTable('login')} (
  `login_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(100) NOT NULL,
  `social_id` varchar(100) NOT NULL,
  `fb_email` varchar(250) NOT NULL,
  PRIMARY KEY (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

    ");
 
$installer->endSetup();