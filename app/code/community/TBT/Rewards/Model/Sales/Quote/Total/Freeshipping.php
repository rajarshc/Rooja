<?php
/**
 * Totals model for enabling Free shipping if the option is enabled in the ST config and 
 * the customer is redeeming points 
 * 
 * @author Snowdog & nelkaake
 *
 */
class TBT_Rewards_Model_Sales_Quote_Total_Freeshipping extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    
    /**
     * This function handles enabling/disabling free shipping
     * (overrides parent method)
     * @param Mage_Sales_Model_Quote_Address $address
     */
    public function collect (Mage_Sales_Model_Quote_Address $address) {
        $quote = $this->_getQuoteFromAddress($address);
    
        if (! $this->isFreeShippingEnabled( $quote->getStore()->getId() ) ) {
            return $this;
        }
        
        $catalogRules = $quote->hasAnyAppliedCatalogRedemptions();
        $cartRules = $quote->_hasAppliedCartRedemptions();
        
        if ($catalogRules == true || $cartRules == true) {
            $address->setFreeShipping(true);
        }
        
        return $this;
    }
    
    /**
     * @param store id
     * @return free shipping is enabled
     */
    protected function isFreeShippingEnabled ($store_id=null)
    {
        return Mage::getStoreConfigFlag('rewards/general/freeshipping_when_spending_points', $store_id);
    }
    /**
     * Fetches a Rewards quote from an address model.
     * @param Mage_Sales_Model_Quote_Address $address
     * @return TBT_Rewards_Model_Sales_Quote 
     */
    protected function _getQuoteFromAddress ( Mage_Sales_Model_Quote_Address $address )  {
        $quote = $address->getQuote();
        // Quote is not of TBT_Rewards variety
        if (! ($quote instanceof TBT_Rewards_Model_Sales_Quote)) {
            $quote = Mage::getModel('rewards/sales_quote');
            $quote->setData($quote->getData());
        }
        return $quote;
    }
}