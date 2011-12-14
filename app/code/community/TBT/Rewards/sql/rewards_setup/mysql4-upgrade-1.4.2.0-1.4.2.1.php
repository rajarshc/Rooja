<?php
$installer = $this;

$installer->startSetup();

if ( Mage::helper( 'rewards/version' )->isBaseMageVersionAtLeast( '1.4.1' ) ) {
    
    // We need to remove this column becuase it was named incorrectly, then we should add it back in.
    Mage::helper( 'rewards/mysql4_install' )->dropColumns( $installer, $this->getTable( 'sales_flat_order' ), 
        array(
            "`rewards_discount_base_tax_amount`"
        ) );
    
    Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'sales_flat_order' ), 
        array(
            "`rewards_base_discount_tax_amount` DECIMAL(12,4)"
        ) );
} else {
    // These are funcitons for stores that are older than Magento 1.4 (ie 1.3) that dont have the sales_flat_order table.
    

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
