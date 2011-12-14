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
class TBT_Rewards_Model_Salesrule_Discount_Action_Cartfixed extends TBT_Rewards_Model_Salesrule_Discount_Action_Abstract {

	
	public function applyDiscounts(&$cartRules, $address, $item, $rule, $qty) {
	
		// WDCA CODE BEGIN
		
		if (! isset ( $cartRules [$rule->getId ()] )) {
			$cartRules [$rule->getId ()] = $this->_getTotalFixedDiscountOnCart ( $item, $address, $rule );
		}
		//@nelkaake Wednesday May 5, 2010 RM: 
		if ($cartRules [$rule->getId ()] <= 0) {
			return array(0,0);
		}
		
		list ( $discountAmount, $baseDiscountAmount ) = $this->_getTotalFixedDiscountOnitem ( $item, $address, $rule, $cartRules );
		
		if($cartRules [$rule->getId ()] - $baseDiscountAmount >= 0) {
			$cartRules [$rule->getId ()] -= $baseDiscountAmount;
		} else {
			$baseDiscountAmount = $cartRules [$rule->getId ()];
			$discountAmount = $item->getQuote()->getStore()->convertPrice($baseDiscountAmount);
			$cartRules [$rule->getId ()] = 0;
		}
	
		//@nelkaake -a 11/03/11: Save our discount due to spending points
		if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
			$new_total_rsd = (float)$address->getTotalBaseRewardsSpendingDiscount();
			$new_total_rsd = $new_total_rsd + $baseDiscountAmount;
			$address->setTotalBaseRewardsSpendingDiscount($new_total_rsd);
		}
		
		
				
		return array ( $discountAmount, $baseDiscountAmount );
	}
	
	public function calcItemDiscount($item, $address, $rule, $qty = null){
		return $this->_getTotalFixedDiscountOnCart ( $item, $address, $rule );
	}
	public function calcCartDiscount($item, $address, $rule, &$cartRules, $qty = null) {
		return $this->_getTotalFixedDiscountOnitem ( $item, $address, $rule, $cartRules );
	}
		
    /**
     * Returns a total discount on the cart from the provided items
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param Mage_Sales_Model_Quote_Address $address
     * @param TBT_Rewards_Model_Sales_Rule $rule
     * @return float
     */
    protected function _getTotalFixedDiscountOnCart($item, $address, $rule) {
        if ( $rule->getPointsAction() == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT ) {
			$points_spent = Mage::getSingleton('rewards/session')->getPointsSpending();
			$totalDiscountOnCart = $rule->getDiscountAmount() * floor(($points_spent / $rule->getPointsAmount()));
        } else {
            $totalDiscountOnCart = $rule->getDiscountAmount();
        }
        
		$all_items = $address->getAllItems ();
		
		return $totalDiscountOnCart;
    }
    /**
     * Returns a total discount on the cart from the provided items
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param Mage_Sales_Model_Quote_Address $address
     * @param TBT_Rewards_Model_Sales_Rule $rule
     * @param array() &$cartRules
     * @return array($discountAmount, $baseDiscountAmount)
     */
    protected function _getTotalFixedDiscountOnitem($item, $address, $rule, &$cartRules) {
    	$quote = $item->getQuote();
	    $store = $item->getQuote()->getStore();
		
	    if ( $rule->getPointsAction() == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT ) {
	        $points_spent = Mage::getSingleton('rewards/session')->getPointsSpending();
	        $multiplier = floor(($points_spent / $rule->getPointsAmount()));
	    } else { 
	    	$multiplier = 1; 
	    }
	    
	    $quoteAmount = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
	    
		list($item_row_total, $item_base_row_total) = $this->_getDiscountableRowTotal($address, $item, $rule);		
        
		$discountAmount = min($item_row_total , $quoteAmount);
		$baseDiscountAmount = min($item_base_row_total , $cartRules[$rule->getId()]);
		
	    return array($discountAmount, $baseDiscountAmount);
    }
	

}
