<?php

$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'catalogrule' ), 
    array(
        "`points_max_redeem_percentage_price` INT(11)"
    ) );

$installer->endSetup();
