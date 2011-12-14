<?php

$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'catalogrule' ), 
    array(
        "`points_uses_per_product` INT(11) DEFAULT '1'"
    ) );

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'rewards_currency' ), 
    array(
        "`text_offset_x` int(11)", 
        "`text_offset_y` int(11)"
    ) );

$installer->endSetup();

