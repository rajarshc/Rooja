<?php

$installer = $this;

$installer->startSetup();

$table = $this->getTable('adjcartalert/quotestat');
$installer->run("
CREATE TABLE `$table` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`quote_id` INT NOT NULL ,
`cart_items` TEXT NOT NULL ,
`cart_price` FLOAT NOT NULL ,
`cart_abandon_date` DATETIME,
`alert_number` SMALLINT( 1 ) NOT NULL ,
`alert_date` DATETIME,
`recovery_date` DATETIME,
`order_items` TEXT NOT NULL ,
`order_price` FLOAT NOT NULL ,
`order_date` DATETIME,
`alert_coupon_generated` varchar(255) NOT NULL,
`order_coupon_used` varchar(255) NOT NULL,
INDEX (`cart_abandon_date`) ,
UNIQUE (
`quote_id`
)
) ENGINE = MYISAM ;
");


$table = $this->getTable('adjcartalert/dailystat');
$installer->run("
CREATE TABLE `$table` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`date` DATE,
`abandoned_carts_num` INT NOT NULL ,
`abandoned_carts_price` FLOAT NOT NULL ,
`abandoned_items_num` INT NOT NULL ,
`recovered_carts_num` int(11) NOT NULL,
`ordered_carts_num` INT NOT NULL ,
`ordered_carts_price` FLOAT NOT NULL ,
`ordered_items_num` INT NOT NULL ,
`av_back_time` time NOT NULL,
`target_letter_step` FLOAT NOT NULL,
`coupons_used` int(11) NOT NULL,
UNIQUE (
`date`
)
) ENGINE = MYISAM ;
");


$table = $this->getTable('adjcartalert/history');
$installer->run("
ALTER TABLE `$table` ADD `coupon_code` VARCHAR( 255 ) NOT NULL 
");


$table = $this->getTable('core/config_data');

$config = serialize(array(
                'task_startdate'    => date('Y-m-d',time()-86400*30),
                'task_enddate'      => date('Y-m-d'),
                'task_isset'        => 1,
                'task_class'        => 'AdjustWare_Cartalert_Model_Quotestat',
                'task_method'       => 'collectDay',
            ));

$installer->run("
INSERT INTO `$table` VALUES(NULL, 'default', 0, 'catalog/adjcartalert/statconfig', '$config');
");

$installer->endSetup(); 
