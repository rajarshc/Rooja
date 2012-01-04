<?php

class TBT_Rewards_Model_Observer_Block_Total_Grandtotal extends TBT_Rewards_Model_Observer_Block_Total_Abstract
{
    
	/** 
     * Adds the rewards catalog spending rule discounts to the tax display amount
	 * @param Varien_Event $observer
	 */
    public function addRewardsCatalogTaxDiscounts($observer) {
        $event = $observer->getEvent();
        $block = $event->getBlock();
        
        if($block instanceof Mage_Tax_Block_Checkout_Grandtotal && $block->getTotal()) {
            $event->getBlock() ->setTemplate('rewards/checkout/total/tax/grandtotal.phtml');
            $event->getBlock()->setTotalExclTaxExclCatalogRedem( $this->getTotalExclTax($block) );
        }
        
        return $this;
    }

    /**
     * Get grandtotal exclude tax
     * @param Mage_Tax_Block_Checkout_Grandtotal
     *
     * @return float
     */
    public function getTotalExclTax($block)
    {
    	$excl = $block->getTotalExclTax();
    	
    	$store = $block->getTotal()->getAddress()->getQuote()->getStore();
    	
    	// If the product price includes tax, we need to 
    	if(   Mage::helper('tax')->priceIncludesTax($store)   ) {
	        $excl += $block->getTotal()->getAddress()->getQuote()->getRewardsDiscountTaxAmount();
	        $excl = max($excl, 0);
    	}
    	
        return $excl ;
    }
    
}