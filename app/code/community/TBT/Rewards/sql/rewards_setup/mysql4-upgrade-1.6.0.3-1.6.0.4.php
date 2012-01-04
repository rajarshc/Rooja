<?php
$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'rewards/special' ), 
    array(
        "`onhold_duration` INT(11) NOT NULL DEFAULT '0'"
    ) );

$installer->endSetup(); 
