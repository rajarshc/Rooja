<?php
$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'sales_flat_quote' ), 
    array(
        "`rewards_discount_amount` DECIMAL(12,4)", 
        "`rewards_base_discount_amount` DECIMAL(12,4)", 
        "`rewards_discount_tax_amount` DECIMAL(12,4)", 
        "`rewards_discount_base_tax_amount` DECIMAL(12,4)"
    ) );

if ( Mage::helper( 'rewards/version' )->isBaseMageVersionAtLeast( '1.4.1' ) ) {
    Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'sales_flat_order' ), 
        array(
            "`rewards_discount_amount` DECIMAL(12,4)", 
            "`rewards_base_discount_amount` DECIMAL(12,4)", 
            "`rewards_discount_tax_amount` DECIMAL(12,4)", 
            "`rewards_base_discount_tax_amount` DECIMAL(12,4)"
        ) );
    
    Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'sales_flat_order_item' ), 
        array(
            "`earned_points_hash` TEXT", 
            "`redeemed_points_hash` TEXT", 
            "`row_total_before_redemptions` DECIMAL(12,4) NOT NULL DEFAULT '0'"
        ) );
} else {
    
    Mage::helper( 'rewards/mysql4_install' )->addAttribute( 'order', 'rewards_discount_amount', 
        array(
            'position' => 1, 
            'type' => 'decimal', 
            'label' => Mage::helper( 'rewards' )->__( "Rewards Discount Amount" ), 
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
            'is_configurable' => 0, 
            'default' => 0.00
        ) );
    Mage::helper( 'rewards/mysql4_install' )->addAttribute( 'order', 'rewards_base_discount_amount', 
        array(
            'position' => 1, 
            'type' => 'decimal', 
            'label' => Mage::helper( 'rewards' )->__( "Rewards Base Discount Amount" ), 
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
            'is_configurable' => 0, 
            'default' => 0.00
        ) );
    
    Mage::helper( 'rewards/mysql4_install' )->addAttribute( 'order', 'rewards_discount_tax_amount', 
        array(
            'position' => 1, 
            'type' => 'decimal', 
            'label' => Mage::helper( 'rewards' )->__( "Rewards Tax Discount Amount" ), 
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
            'is_configurable' => 0, 
            'default' => 0.00
        ) );
    Mage::helper( 'rewards/mysql4_install' )->addAttribute( 'order', 'rewards_base_discount_tax_amount', 
        array(
            'position' => 1, 
            'type' => 'decimal', 
            'label' => Mage::helper( 'rewards' )->__( "Rewards Base Tax Discount Amount" ), 
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
            'is_configurable' => 0, 
            'default' => 0.00
        ) );
    
    Mage::helper( 'rewards/mysql4_install' )->addAttribute( 'order_item', 'row_total_before_redemptions', 
        array(
            'position' => 1, 
            'type' => 'decimal', 
            'label' => Mage::helper( 'rewards' )->__( "Row Total Before Redemptions" ), 
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
    
    Mage::helper( 'rewards/mysql4_install' )->addAttribute( 'order_item', 'earned_points_hash', 
        array(
            'position' => 1, 
            'type' => 'text', 
            'label' => Mage::helper( 'rewards' )->__( "Earned Points Hash" ), 
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
    Mage::helper( 'rewards/mysql4_install' )->addAttribute( 'order_item', 'redeemed_points_hash', 
        array(
            'position' => 1, 
            'type' => 'text', 
            'label' => Mage::helper( 'rewards' )->__( "Redeemed Points Hash" ), 
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