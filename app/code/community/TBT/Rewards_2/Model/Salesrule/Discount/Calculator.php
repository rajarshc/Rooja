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
class TBT_Rewards_Model_Salesrule_Discount_Calculator extends Mage_SalesRule_Model_Validator {

	/**
	 * 
	 * @param Mage_Sales_Model_Quote $quote
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @param int $rule_id
	 */
	public function getNewDiscounts($quote, $address, $item, $rule_id) {
		$rule = $this->getRule ( $rule_id );
		$store = $item->getQuote ()->getStore (); //@nelkaake 17/03/2010 5:01:35 AM
	
		if(!$this->_getDiscountValidator()->isValidAppliedRedemption($rule, $item)) {
			return $this->_emptyDiscount();
		}
		
		

		//@nelkaake -a 16/11/10:  No rule ID? 
		if (! $rule->getId ()) {
			throw new Exception("No rule ID was detected in Salesrule_Discount_Calculator::getNewDiscounts, which means that there may be some unexpected behaviour that may cause problems.");
		}
		
		//@nelkaake -a 11/03/11: if this item is valid for the rule
		if (! $rule->getActions ()->validate ( $item )) {
			return $this->_emptyDiscount ();
		}
		
		$qty = $item->getQty ();
		//@nelkaake -a 11/03/11: If we have a bundle of 2, and two bundles it's 2x2=4 products total.
		if ($item->getParentItem ()) {
			$qty *= $item->getParentItem ()->getQty ();
		}
		
		
		//@nelkaake -a 11/03/11: Is there a maximum quantity to discount? If so, use that. TODO check if this is required.
		$qty = $rule->getDiscountQty () ? min ( $qty, $rule->getDiscountQty () ) : $qty;
		
		
		//@nelkaake -a 11/03/11: Make sure there discount percent is 100% max.
		$rulePercent = min ( 100, $rule->getDiscountAmount () );
		
		
		//@nelkaake -a 11/03/11: Initialize the discount amounts
		$discountAmount = 0;
		$baseDiscountAmount = 0;
		
		//@nelkaake 17/03/2010 5:09:27 AM :Get all items.
		$all_items = $item->getQuote ()->getAllItems ();
		
		//@nelkaake -a 11/03/11: How much was the total shipping amount so far?
		$shipping_amount = $address->getShippingAmount ();
		$base_shipping_amount = $address->getBaseShippingAmount ();
		
		//@nelkaake -a 11/03/11: Initialize the per-item price.
		$itemPrice = $item->getDiscountCalculationPrice ();
		if ($itemPrice !== null) {
			$baseItemPrice = $item->getBaseDiscountCalculationPrice ();
		} else {
			$itemPrice = $item->getCalculationPrice ();
			$baseItemPrice = $item->getBaseCalculationPrice ();
		}
		
		$cartRules = $address->getRewardsCartRules ();
		$cartRules = empty ( $cartRules ) ? array () : $cartRules;
		// WDCA CODE BEGIN
		
		
		//@nelkaake -a 11/03/11: Before we begin, refactor all the catalog redemptions so we have the latest row totals. 
		// TODO Is this still needed?
		
		switch ($rule->getSimpleAction ()) {
			case 'by_percent' :
				$by_percent_discounter = Mage::getSingleton('rewards/salesrule_discount_action_bypercent');
				list($discountAmount, $baseDiscountAmount) = $by_percent_discounter->applyDiscounts($cartRules, $address, $item, $rule, $qty);
				break;
			
			case 'cart_fixed' :
				$cart_fixed_discounter = Mage::getSingleton('rewards/salesrule_discount_action_cartfixed');
				list($discountAmount, $baseDiscountAmount) = $cart_fixed_discounter->applyDiscounts($cartRules, $address, $item, $rule, $qty);
				
				// WDCA CODE END
				break;
				
			default:
				throw new Exception("Reached unsupported discount action. This may be due to corrupt data.  Unexpected rule action was: ". $rule->getSimpleAction ());
				break;
		}
		
		//@nelkaake -a 11/03/11: Save cart rule entries.
		$address->setRewardsCartRules ($cartRules);
		
		
		$discountAmount = $quote->getStore ()->roundPrice ( $discountAmount );
		$baseDiscountAmount = $quote->getStore ()->roundPrice ( $baseDiscountAmount );
		
		
		return new Varien_Object(array(
        	'discount_amount'      => $discountAmount,
            'base_discount_amount' => $baseDiscountAmount,
        ));
		
	}
	

	/**
	 * @return TBT_Rewards_Model_Salesrule_Discount_Validator
	 */
	protected function _getDiscountValidator() {
		return Mage::getSingleton('rewards/salesrule_discount_validator');
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
	 * Fetches the redemption calculator model
	 *
	 * @return TBT_Rewards_Model_Redeem
	 */
	private function _getRedeemer() {
		return Mage::getSingleton ( 'rewards/redeem' );
	}
	
	protected function _emptyDiscount() {
		$result = new Varien_Object ( array ('discount_amount' => 0, 'base_discount_amount' => 0 ) );
		return $result;
	}
		
	
}
