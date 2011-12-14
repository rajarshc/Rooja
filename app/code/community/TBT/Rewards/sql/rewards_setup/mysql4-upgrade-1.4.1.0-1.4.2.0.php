<?php
$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'sales_flat_quote_item' ), 
    array(
        "`row_total_before_redemptions_incl_tax` DECIMAL(12,4) NOT NULL DEFAULT '0'"
    ) );

if ( Mage::helper( 'rewards/version' )->isBaseMageVersionAtLeast( '1.4.1' ) ) {
    Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'sales_flat_order_item' ), 
        array(
            "`row_total_before_redemptions_incl_tax` DECIMAL(12,4) NOT NULL DEFAULT '0'"
        ) );
} else {
    Mage::helper( 'rewards/mysql4_install' )->addAttribute( 'order_item', 'row_total_before_redemptions_incl_tax', 
        array(
            'position' => 1, 
            'type' => 'decimal', 
            'label' => Mage::helper( 'rewards' )->__( "Row Total Before Redemptions Including Tax" ), 
            'global' => 1, 
            'visible' => 0, 
            'required' => 0, 
            'user_defined' => 0, 
            'searchable' => 0, 
            'filterable' => 0, 
            'comparable' => 0, 
            'visible_on_front' => 0, 
            'visible_in_advanced_search' => 0, 
            'unique' => 0, 
            'is_configurable' => 0
        ) );
}

$installer->endSetup(); 
