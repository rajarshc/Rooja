<?php

class TBT_Rewards_Model_Checkout_Total_Observer extends Varien_Object
{
    
    /**
     * Checks 
     * @param type $observer 
     */
    public function checkTax($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if($block instanceof Mage_Checkout_Block_Total_Tax && $block->getTotal()) {
            $block->setTemplate('rewards/checkout/total/tax.phtml');
            $total = $block->getTotal()->getValue();
            // If tax is included in the produt price then we should subtract the change in tax price from the 
            // tax total visually only.
            $store = $block->getTotal ()->getAddress ()->getQuote ()->getStore ();
            if (Mage::helper ( 'tax' )->priceIncludesTax ( $store )) {
                $total -= $block->getTotal ()->getAddress ()->getQuote ()->getRewardsDiscountTaxAmount ();
            }
            $block->setTotalInclCatalogDiscounts($total);
        }
    }
	
    public function checkGrandTotal($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if($block instanceof Mage_Tax_Block_Checkout_Grandtotal && $block->getTotal()) {
            $excl = $block->getTotalExclTax();
            $store = $block->getTotal()->getAddress()->getQuote()->getStore();

            // If the product price includes tax, we need to 
            if(   Mage::helper('tax')->priceIncludesTax($store)   ) {
            $excl += $block->getTotal()->getAddress()->getQuote()->getRewardsDiscountTaxAmount();
            $excl = max($excl, 0);
            }
            $block->setTotalExclTax($excl);
        }
    }
    
}