<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping Cart Rule Validator
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_Rewards_Model_Salesrule_Discount_Action_Abstract extends Mage_Core_Model_Abstract {


    /**
     * Return discount item qty
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Mage_SalesRule_Model_Rule $rule
     * @return int
     */
    protected function _getItemQty($item, $rule)
    {
    	//@nelkaake: Magento 1.3 and lower doesnt have the item->getTotalQty attribute.
    	if(Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4')) {
        	$qty = $item->getTotalQty();
    	} else {
            $qty = $item->getQty();
            if ($item->getParentItem()) {
                $qty*= $item->getParentItem()->getQty();
            }
    	}
            
        return $rule->getDiscountQty() ? min($qty, $rule->getDiscountQty()) : $qty;
    }

    /**
     * Return item price
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     */
    protected function _getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $price : $item->getCalculationPrice();
    }

    /**
     * Return item base price
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     */
    protected function _getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }
    
	/**
	 * Fetches a cached rule model
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	protected function &getRule($rule_id) {
		return Mage::helper('rewards/rule')->getSalesrule($rule_id);
	}
	
	/**
	 * 
	 * @param array $cartRules
	 * @param unknown_type $address
	 * @param unknown_type $item
	 * @param unknown_type $rule
	 * @param int $qty FOr now, qty does nothing but later this will be used to calculate the maximum quantity a discount should be applied to.
	 */
	public abstract function applyDiscounts(&$cartRules, $address, $item, $rule, $qty);
	
	public abstract function calcItemDiscount($item, $address, $rule, $qty = null); 
	public abstract function calcCartDiscount($item, $address, $rule, &$cartRules, $qty = null);
		
	

	/**
	 * Figure out the catalog discount and the base catlaog discount due to catalog redemption rules.
	 * NOTE: THIS RETURNS A NEGATIVE NUMBER
	 * @param unknown_type $address
	 * @param unknown_type $item
	 */
	protected function _collectCatalogRewardsDiscounts($address, $item) {
        
        $item2 = clone $item;
        $item2->setQuote($item->getQuote());
        
        $currency_rate = Mage::helper('rewards/price')->getCurrencyRate($item2->getQuote());
        
        $catalog_discount = Mage::getSingleton('rewards/redeem')->getTotalCatalogDiscount($item2);
	
        // If tax exists in the item data and we are supposed to discount tax and we should apply tax before the discount.
        if(	$this->_discountTax($item)  ) {
        	if(!Mage::helper ( 'tax' )->priceIncludesTax($item->getQuote()->getStore())) {
        		$catalog_discount = $catalog_discount * (1 + $item->getTaxPercent()/100);
        	}
        }
        
    	$base_catalog_discount = Mage::helper('rewards/price')->getReversedCurrencyPrice($catalog_discount, $currency_rate );
    	
    	return array($catalog_discount, $base_catalog_discount);
	}
	
	protected function _discountTax($item) {
        $store = $item->getQuote()->getStore();
       	$has_tax = $item->getTaxAmount()  && $item->getTaxPercent();
       	$cfg_discount_tax = Mage::helper ( 'tax' )->discountTax ( $store );
       	$cfg_tax_not_after_discount =  !Mage::helper ( 'tax' )->applyTaxAfterDiscount ( $store );
       	
       	$result  = $has_tax && $cfg_discount_tax && $cfg_tax_not_after_discount ;
       	
		return $result;
	}
	
	
	/**
	 * Returns back the row total and BASE row total amounts that can be discounted by this rule.
	 * @param unknown_type $address
	 * @param unknown_type $item
	 * @param unknown_type $rule
	 */
	protected function _getDiscountableRowTotal($address, $item, $rule) {
		
	   	$itemPrice  = $this->_getItemPrice($item);
	    $baseItemPrice = $this->_getItemBasePrice($item);
        $qty = $this->_getItemQty($item, $rule);
        
		// Fetch the catalog rewards discounts
		list($catalog_discount, $base_catalog_discount) = $this->_collectCatalogRewardsDiscounts($address, $item);
        
    	// Calculate the row totals while subtracting the catalog discounts
		$item_row_total = $itemPrice * $qty + $catalog_discount;
		$item_base_row_total = $baseItemPrice * $qty  + $base_catalog_discount;
		
		// Subtract discounts from rules that are higher priority than this rule.
		$item_row_total -= $item->getDiscountAmount ();
		$item_base_row_total -= $item->getBaseDiscountAmount ();
		
		// If the rule says to apply the discount to shipping, add the shipping amount to the total amount we can discount.
		// TODO temporarily turned off 
/*		$add_shipping = $rule->getApplyToShipping () ? $address->getShippingAmount () : 0;
		$add_base_shipping = $rule->getApplyToShipping () ? $address->getBaseShippingAmount () : 0;
		$item_row_total += $add_shipping;
		$item_base_row_total += $add_base_shipping;*/
		
		
		//echo("($itemPrice * $qty + $catalog_discount) - {$item->getDiscountAmount ()} + {$add_shipping}  ={$item_row_total} |<BR /> \n");
		
		return array($item_row_total, $item_base_row_total);
	}
	
}