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
class TBT_Rewards_Model_Salesrule_Discount_Action_Bypercent extends TBT_Rewards_Model_Salesrule_Discount_Action_Abstract {

	
	public function applyDiscounts(&$cartRules, $address, $item, $rule, $qty) {
		
		list ( $discountAmount, $baseDiscountAmount ) = $this->calcItemDiscount ( $item, $address, $rule, $cartRules, $qty );
	
		//@nelkaake -a 11/03/11: Save our discount due to spending points
		if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
			$new_total_rsd = (float)$address->getTotalBaseRewardsSpendingDiscount();
			$new_total_rsd = $new_total_rsd + $baseDiscountAmount;
			$address->setTotalBaseRewardsSpendingDiscount($new_total_rsd);
		}
		
		return array ( $discountAmount, $baseDiscountAmount );
	}
	
	
	public function calcItemDiscount($item, $address, $rule, $qty = null){
		return $this->_getTotalPercentDiscountOnitem($item, $address, $rule, $qty);
	}
	public function calcCartDiscount($item, $address, $rule, &$cartRules, $qty = null) {
		return $this->_getTotalPercentDiscount($item, $address, $rule, $cartRules, $qty);
	}
		
	/**
	 * Returns a total discount on the cart from the provided items
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @param TBT_Rewards_Model_Sales_Rule $rule
	 * @param int $qty	max discount qty or unlimited if null
	 * @return float
	 */
	protected function _getTotalPercentDiscount($item, $address, $rule, $qty = null) {
		$all_items = $address->getAllItems ();
		$store = $item->getQuote ()->getStore ();
		
        $totalDiscountOnCart = 0;
        
        $discountPercent = $this->_getRulePercent($rule);
        
        // TODO move this to a method
		if ($rule->getPointsAction () != TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
			foreach ( $all_items as $cartItem ) {
				if (! $rule->getActions ()->validate ( $cartItem )) {
					continue;
				}
				
				list($item_row_total, $item_base_row_total) = $this->_getDiscountableRowTotal($address, $item, $rule);	
				$discountOnItem = $item_row_total * $discountPercent;
				$totalDiscountOnCart += $discountOnItem;
			}
			return $totalDiscountOnCart;
		} 

        $totalAmountToDiscount = 0;
		foreach ( $all_items as $cartItem ) {
            if (! $rule->getActions ()->validate ( $cartItem )) {
                    continue;
            }
            list($item_row_total, $item_base_row_total) = $this->_getDiscountableRowTotal($address, $item, $rule);	
            $totalAmountToDiscount += $item_row_total; // $cartItem->getTaxAmount();

            /*// Fetch the catalog rewards discounts.  Add this NEGATIVE amount to the total.
            list($catalog_discount, $base_catalog_discount) = $this->_collectCatalogRewardsDiscounts($address, $cartItem);
            $totalAmountToDiscount += $base_catalog_discount;*/
        }

        // @nelkaake -a 16/11/10: 
        if ($rule->getApplyToShipping ()) {
                $totalAmountToDiscount += $address->getBaseShippingAmountForDiscount();
        }

        $totalDiscountOnCart = $totalAmountToDiscount * $discountPercent;
		
		return $totalDiscountOnCart;
	}

    /**
     * Get the discount percentage from the rule with consideration for the session points spending amount
     * @param TBT_Rewards_Model_Salesrule $rule
     */
    protected function _getRulePercent($rule) {
        
        $discountPercent = 0;
        
        if ( $rule->getPointsAction() == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT ) {
            $points_spent = Mage::getSingleton('rewards/session')->getPointsSpending();
            $discountPercent = (($rule->getDiscountAmount() * floor(($points_spent / $rule->getPointsAmount()))) / 100);
        } else {
            $discountPercent = (float) $rule->getDiscountAmount() / 100;
        }
        
        $discountPercent = min($discountPercent, 1);
        
        return $discountPercent;
    }
	
	protected function _getTotalSpendingPercent($rule) {
	
	    if ( $rule->getPointsAction() == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT ) {
	        $points_spent = Mage::getSingleton('rewards/session')->getPointsSpending();
	        $multiplier = floor(($points_spent / $rule->getPointsAmount()));
	    } else { 
	    	$multiplier = 1; 
	    }
		$discountPercent = (($rule->getDiscountAmount () * $multiplier ) / 100);
	    
		$discountPercent = min ( $discountPercent, 1 );
		
		return $discountPercent;
	}
	

	protected function reverseShippingDiscountAmount($address, $rule) {
		$discountPercent = $this->_getTotalSpendingPercent($rule);
		
		return $discountPercent;
	}
	
	/**
	 * Returns a total discount on the cart from the provided items
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @param TBT_Rewards_Model_Sales_Rule $rule
	 * @param array() &$cartRules
	 * @param int $qty	max discount qty or unlimited if null
	 * @return array($discountAmount, $baseDiscountAmount)
	 */
	protected function _getTotalPercentDiscountOnitem($item, $address, $rule, &$cartRules, $qty = null) {
		$quote = $item->getQuote ();
		$store = $item->getQuote ()->getStore ();
		
        $rulePercent = $this->_getRulePercent($rule);

		list($item_row_total, $item_base_row_total) = $this->_getDiscountableRowTotal($address, $item, $rule);
		
		$discountAmount = ($item_row_total ) * $rulePercent;
		$baseDiscountAmount = ($item_base_row_total) * $rulePercent;
		
		return array ($discountAmount, $baseDiscountAmount );
	}
	

}
