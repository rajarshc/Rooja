<?php

class TBT_Rewards_Model_Observer_Block_Total_Tax extends TBT_Rewards_Model_Observer_Block_Total_Abstract
{
    
    /**
     * Adds the rewards catalog spending rule discounts to the tax display amount
     * @param Varien_Event $observer 
     */
    public function addRewardsCatalogTaxDiscounts($observer) {
        $event = $observer->getEvent();
        $block = $event->getBlock();
        
        if($block instanceof Mage_Tax_Block_Checkout_Tax && $block->getTotal()) {
            $event->getBlock() ->setTemplate('rewards/checkout/total/tax.phtml');
            $event->getBlock() ->setTotalInclCatalogDiscounts(  $this->getTotalInclCatalogDiscounts( $event->getBlock() )  );
        }
        return $this;
    }
    
    
    /**
     * Fetches the total tax including any catalog redemption rule discounts
     * @param Mage_Tax_Block_Checkout_Tax $block
     */
	public function getTotalInclCatalogDiscounts($block) {
		$total = $block->getTotal ()->getValue ();
		
		// If tax is included in the produt price then we should subtract the change in tax price from the 
		// tax total visually only.
		$store = $block->getTotal ()->getAddress ()->getQuote ()->getStore ();
		if (Mage::helper ( 'tax' )->priceIncludesTax ( $store )) {
			$total -= $block->getTotal ()->getAddress ()->getQuote ()->getRewardsDiscountTaxAmount ();
		}
		return $total;
	}
	
	
}