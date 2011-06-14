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
 * @deprecated As of Sweet Tooth 1.4.2 this is no longer used.  Instead we use a nobserver for the Validator::process event. See TBT_Rewards_Model_Salesrule_Observer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Salesrule_Validator extends Mage_SalesRule_Model_Validator {
	public static $cartRulePass = 0;
	private $num_items_validated = 0;
	
	private $total_discount_reversed = 0;
	private $total_base_discount_reversed = 0;
	private $cart_fixed_rules = array ();
	//@nelkaake Added on Wednesday May 5, 2010: If contains an entry it means that it is is processed.
	private $cfr_processed = array ();
	protected static $cart_points_spend_redem_rule_discounts = array ();
	protected $_rrules = array ();
	
	protected $item_rid_map = array ();
	
	public function reset(Mage_Sales_Model_Quote_Address $address) {
		self::$cartRulePass = 0;
		$this->num_items_validated = 0;
		
		$this->total_discount_reversed = 0;
		$this->total_base_discount_reversed = 0;
		$this->cart_fixed_rules = array ();
		
		$this->cfr_processed = array ();
		self::$cart_points_spend_redem_rule_discounts = array ();
		
		$this->_rrules = array ();
		$this->item_rid_map = array ();
		
		$address->setCartFixedRules ( null );
		$address->setCartFixedRules2 ( null );
		Mage::getSingleton ( 'rewards/salesrule_discountmanager' )->reset ();
		
		$this->_isFirstTimeProcessRun = false;
		
		return $this;
	}
	
	/**
	 * Fetches a cached rule model
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	protected function &getRule($rule_id) {
		if (! isset ( $this->_rrules [$rule_id] )) {
			$this->_rrules [$rule_id] = Mage::getModel ( 'rewards/salesrule_rule' )->load ( $rule_id );
		}
		return $this->_rrules [$rule_id];
	}
	
	/**
	 * Extracts an address model from an item
	 *
	 * @param unknown_type $item
	 * @return Mage_Sales_Model_Quote_Address
	 */
	protected function _extractAddress($item) {
		$quote = $item->getQuote ();
		if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
			$address = $item->getAddress ();
		} elseif ($quote->isVirtual ()) {
			$address = $quote->getBillingAddress ();
		} else {
			$address = $quote->getShippingAddress ();
		}
		
		return $address;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_SalesRule_Model_Validator::process($item)
	 */
	public function process(Mage_Sales_Model_Quote_Item_Abstract $item) {
		
		return parent::process ( $item );
		
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.2' )) {
			$v = Mage::registry ( 'assex' );
			$v = ! is_array ( $v ) ? array () : $v;
			if (isset ( $v [$item->getId ()] )) {
				return $this;
			} else {
				$v [$item->getId ()] = 1;
				Mage::unregister ( 'assex' );
				Mage::register ( 'assex', $v );
			}
		}
		
		Varien_Profiler::start ( "TBT_REWARDS: Salesrule Validator" );
		
		if ($this->num_items_validated == 0) {
			$item->getQuote ()->setAppliedRuleIds ( array () );
		}
		$this->num_items_validated ++;
		
		//@nelkaake -a 28/11/10: Run magento's orgiinal process method.
		$this->originalProcess ( $item );
		
		$quote = $item->getQuote ();
		$address = $this->_extractAddress ( $item );
		
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.2' )) {
			$itemPrice = $this->_getItemPrice ( $item );
			$baseItemPrice = $this->_getItemBasePrice ( $item );
			if ($itemPrice <= 0) {
				return $this;
			}
		}
		
		//Clearing applied rule ids for quote and address
		if ($this->_isFirstTimeProcessRun !== true) {
			$this->_isFirstTimeProcessRun = true;
			$quote->setAppliedRuleIds ( '' );
			$address->setAppliedRuleIds ( '' );
		}
		
		$applicable_redemptions = explode ( ',', $quote->getCartRedemptions () );
		$applied_redemptions = explode ( ',', $quote->getAppliedRedemptions () );
		//@nelkaake -a 28/11/10: Get all the rule ids that were validated by Magento
		$all_rule_ids = $this->_getQuoteRewardsRuleIds ( $quote );
		$validated_applicable_rule_ids = array ();
		$validated_applied_rule_ids = array ();
		
		$item->setDiscountAmount ( 0 );
		$item->setBaseDiscountAmount ( 0 );
		$item->setDiscountPercent ( 0 );
		
		//@nelkaake -a 28/11/10: Revalidate the ones according to Sweet Tooth redemption validation and populate lists.
		$this->_validateAppliedRules ( $all_rule_ids, $address, $applicable_redemptions, $applied_redemptions, $validated_applicable_rule_ids, $validated_applied_rule_ids );
		//@nelkaake -a 28/11/10: 
		$this->_cleanApplicableAndApplied ( $applicable_redemptions, $applied_redemptions, $validated_applicable_rule_ids, $validated_applied_rule_ids );
		//@nelkaake -a 28/11/10: 
		$this->_validateStopProcessingCondition ( $applicable_redemptions, $applied_redemptions, $validated_applied_rule_ids );
		//@nelkaake -a 28/11/10: 
		$this->_validateBySpentRules ( $applicable_redemptions, $applied_redemptions, $validated_applied_rule_ids );
		//@nelkaake -a 3/03/11: uniqify just in case the above functions weren't meticulous enough.
		$validated_applied_rule_ids = array_unique ( $validated_applied_rule_ids );
		$applied_redemptions = array_unique ( $applied_redemptions );
		$applicable_redemptions = array_unique ( $applicable_redemptions );
		
		foreach ( $validated_applied_rule_ids as $rule_id ) {
			$this->recalculateDiscounts ( $quote, $address, $item, $rule_id );
		}
		
		// No support for multi-shipping
		if (Mage::helper ( 'rewards' )->isMultishipMode ( $quote )) {
			$applicable_redemptions = array ();
			$applied_redemptions = array ();
			$validated_applied_rule_ids = array ();
		}
		
		$this->recalculateShippingDiscounts ( $address, $item, $applied_redemptions );
		
		//@nelkaake -a 28/11/10: Save redemption info into item address and quote
		$this->_saveRedemptions ( $applicable_redemptions, $applied_redemptions, $validated_applied_rule_ids, $item, $address, $quote );
		$this->total_discount_reversed = 0;
		$this->total_base_discount_reversed = 0;
		Varien_Profiler::stop ( "TBT_REWARDS: Salesrule Validator" );
		return $this;
	}
	
	/**
	 * 
	 * @param Mage_Sales_Model_Quote $quote
	 */
	protected function _getQuoteRewardsRuleIds($quote) {
		$all_rule_ids = explode ( ',', $quote->getAppliedRuleIds () );
		$all_rr_ids = array ();
		foreach ( $all_rule_ids as $rule_id ) {
			$salesrule = $this->getRule ( $rule_id );
			if ($salesrule->isPointsRule ()) {
				$all_rr_ids [] = $rule_id;
			}
		}
		
		return $all_rr_ids;
	}
	
	/**
	 * 
	 * Validates the rules based on their redemption rule status.
	 * Takes all the rules that Magento validated and 
	 * 
	 * @param array $all_rule_ids
	 * @param unknown_type $address
	 * @param array $applicable_redemptions
	 * @param array $applied_redemptions
	 * @param array $validated_applicable_rule_ids
	 * @param array $validated_applied_rule_ids
	 */
	protected function _validateAppliedRules($all_rule_ids, $address, &$applicable_redemptions, &$applied_redemptions, &$validated_applicable_rule_ids, &$validated_applied_rule_ids) {
		
		foreach ( $all_rule_ids as $rule_id ) {
			$salesrule = $this->getRule ( $rule_id );
			$rule = &$salesrule; // @alias
			

			if (! $rule->validate ( $address )) {
				if (array_search ( $rule_id, $validated_applied_rule_ids ) !== false) {
					unset ( $validated_applied_rule_ids [$rule_id] );
				}
				continue;
			}
			
			// Here we're basically checking to see if the cart rule is enabled
			// for this particular item or quotation if the rule is a redemption points rule.
			if ($rule->isRedemptionRule ()) {
				if (array_search ( $rule_id, $applied_redemptions ) === false) { // the rule is not already applied
					if (array_search ( $rule_id, $applicable_redemptions ) === false) { // the rule is not already applicable
						//@nelkaake -a 3/03/11: Other places in the code will check by map, so we should be doing it here too.
						$applicable_redemptions [$rule_id] = $rule_id;
					}
					
					$validated_applicable_rule_ids [] = $rule_id;
					if (array_search ( $rule_id, $validated_applied_rule_ids ) !== false) {
						unset ( $validated_applied_rule_ids [$rule_id] );
					}
					continue;
				}
			}
			$validated_applied_rule_ids [$rule_id] = $rule_id;
		}
		return $this;
	}
	
	/**
	 * 1. If a rule id is in validated applicable rule list, it is removed from applicable
	 * 2. If a rule is in validated applied list, it is removed from applied list.
	 *
	 * @param array $applicable_redemptions
	 * @param array $applied_redemptions
	 * @param array $validated_applied_rule_ids
	 * @return $this
	 */
	protected function _cleanApplicableAndApplied(&$applicable_redemptions, &$applied_redemptions, &$validated_applicable_rule_ids, &$validated_applied_rule_ids) {
		foreach ( $applicable_redemptions as $key => $rid ) {
			if (array_search ( $rid, $validated_applicable_rule_ids ) === false) {
				unset ( $applicable_redemptions [$key] );
			}
		}
		
		// Check for if the rule was acutally validated (I think this also done earlier)
		foreach ( $applied_redemptions as $key => $rid ) {
			$rr = $this->getRule ( $rid );
			if (array_search ( $rid, $validated_applied_rule_ids ) === false) {
				//@nelkaake -d 17/02/11: Removing this because if the product does not apply to THIS item, it may apply to other ones
			// so it is not correct to assume that it was not intended to be applied in the first place.
			//unset($applied_redemptions[$key]);
			}
		}
		
		return $this;
	}
	
	/**
	 * Go through all the validated applied rules in sequential order.
	 * If we hit one that has a stop processing flag, then break and all
	 * other rules to invalidated.
	 * TODO should we be cancelling all other rules or only subsequent rules after we hit the flag?
	 *
	 * @param array $applicable_redemptions
	 * @param array $applied_redemptions
	 * @param array $validated_applied_rule_ids
	 */
	protected function _validateStopProcessingCondition(&$applicable_redemptions, &$applied_redemptions, &$validated_applied_rule_ids) {
		foreach ( $applied_redemptions as $key => $rid ) {
			$rr = $this->getRule ( $rid );
			if (array_search ( $rid, $validated_applied_rule_ids ) !== false) {
				if ($rr->getStopRulesProcessing ()) {
					$applied_redemptions = array ($rid );
					$applicable_redemptions = array ();
					$validated_applied_rule_ids = array ($rid );
					break;
				}
			}
		}
		
		return $this;
	}
	/**
	 * Alters applied, applicable and validated applied rules when looking
	 * at the number of points spent total.
	 *
	 * @param array &$applicable_redemptions
	 * @param array &$applied_redemptions
	 * @param array &$validated_applied_rule_ids
	 * @return $this
	 */
	protected function _validateBySpentRules(&$applicable_redemptions, &$applied_redemptions, &$validated_applied_rule_ids) {
		// The next few commented lines are for the discount by points spent action
		$vraw = array ();
		foreach ( $applicable_redemptions as $key => $rid ) {
			$vraw [$rid] = $rid;
		}
		foreach ( $validated_applied_rule_ids as $key => $rid ) {
			$vraw [$rid] = $rid;
		}
		foreach ( $vraw as $rid => $rid ) {
			$rule = $this->getRule ( $rid );
			if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
				$applied_redemptions [] = $rid;
				$validated_applied_rule_ids [] = $rid;
				unset ( $applicable_redemptions [$rid] );
			}
		}
		return $this;
	}
	
	/**
	 * Saves redemption id information into item, address and quote models
	 */
	protected function _saveRedemptions($applicable_redemptions, $applied_redemptions, $validated_applied_rule_ids, $item, $address, $quote) {
		$applicable_redemptions = array_unique ( $applicable_redemptions );
		$applicable_redemptions_str = implode ( ',', $applicable_redemptions );
		
		$applied_redemptions = array_unique ( $applied_redemptions );
		$applied_redemptions_str = implode ( ',', $applied_redemptions );
		
		$validated_applied_rule_ids = array_unique ( $validated_applied_rule_ids );
		$validated_applied_rule_ids_str = implode ( ',', $validated_applied_rule_ids );
		
		$item->setAppliedRuleIds ( $validated_applied_rule_ids_str );
		$address->setAppliedRuleIds ( $validated_applied_rule_ids_str );
		$quote->setCartRedemptions ( $applicable_redemptions_str )->setAppliedRedemptions ( $applied_redemptions_str )->setAppliedRuleIds ( $validated_applied_rule_ids_str );
		
		return $this;
	}
	
	public function itemHasAppliedRid($item_id, $cart_rule_id) {
		//Mage::log("Reading RID map for item #". $item_id);
		if (! isset ( $this->item_rid_map [$item_id] ))
			return false;
		
		//Mage::log("Item applied rule ids: #". print_r($this->item_rid_map[$item_id], true));
		return (array_search ( $cart_rule_id, $this->item_rid_map [$item_id] ) !== false);
	}
	
	public function setItemAppliedRuleIds($item, $rule_ids) {
		$this->item_rid_map [$item->getId ()] = $rule_ids;
		//Mage::log("Wrote item RID map for item #". $item->getId() . ": ". print_r($rule_ids, true));
		return $this;
	}
	public function addItemAppliedRuleId($item, $rule_id) {
		$item_id = $item->getId ();
		if (! isset ( $this->item_rid_map [$item_id] ))
			$this->item_rid_map [$item_id] = array ();
		$this->item_rid_map [$item_id] [] = $rule_id;
		$this->item_rid_map [$item_id] = array_unique ( $this->item_rid_map [$item_id] );
		//Mage::log("Added item RID map for item #{$item->getId()} name={$item->getId()} rule_id=". $rule_id);
		return $this;
	}
	/**
	 * Recalulates the shipping discount taking into account whether or not
	 * a redemption rule is applied by the user.
	 *
	 * @author Jay El <nelkaake@wdca.ca>
	 * 
	 * @param Mage_Sales_Model_Quote_Address &$address
	 * @param Mage_Sales_Model_Quote_Item_Abstract &$item
	 * @param array $applied_redemptions
	 */
	protected function recalculateShippingDiscounts(Mage_Sales_Model_Quote_Address &$address, &$item, array $applied_redemptions) {
		//@nelkaake 04/03/2010 4:56:24 PM : as long as we're not in Mage 1.4
		

		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' )) {
		} else {
			// Enabled/disable free shipping
			$rules = $this->_getRules2 ();
			foreach ( $rules as &$salesrule ) {
				$rule_id = $salesrule->getId ();
				// Get the points salesrule versus $salesrule which is any type of salesrule
				$pointsrule = $this->getRule ( $rule_id );
				if (! $pointsrule->isPointsRule ())
					continue; // we're only looking at points rules here
				// if it's a rdemption rule and it's not applied, continue on...
				if ($pointsrule->isRedemptionRule () && (array_search ( $rule_id, $applied_redemptions ) === false))
					continue;
				switch ($pointsrule->getSimpleFreeShipping ()) {
					case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM :
						$item->setFreeShipping ( $pointsrule->getDiscountQty () ? $pointsrule->getDiscountQty () : true );
						break;
					
					case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS :
						$address->setFreeShipping ( true );
						break;
				}
			
			}
		}
	
		//Mage::log("Free Shipping Report: item={$item->getFreeShipping()}, address={$address->getFreeShipping()}" );
	}
	
	protected function _getRules2() {
		$rules = $this->_rules;
		$first_rule = current ( $rules );
		if ($first_rule !== false) {
			if ($first_rule instanceof Mage_SalesRule_Model_Mysql4_Rule_Collection) {
				return parent::_getRules ();
			}
		}
		return $rules;
	}
	
	/**
	 * Fetches the redemption calculator model
	 *
	 * @return TBT_Rewards_Model_Redeem
	 */
	private function _getRedeemer() {
		return Mage::getSingleton ( 'rewards/redeem' );
	}
	
	/**
	 * @author Jared Ty <jtyler@wdca.ca>
	 * 
	 * @param unknown_type $quote
	 * @param unknown_type $address
	 * @param unknown_type $item
	 * @param unknown_type $rule_id
	 */
	protected function recalculateDiscounts($quote, $address, $item, $rule_id) {
		$rule = $this->getRule ( $rule_id );
		$store = $item->getQuote ()->getStore (); //@nelkaake 17/03/2010 5:01:35 AM
		

		//@nelkaake -a 16/11/10: 
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.1.0' )) {
			$addrFixedCartRules = $address->getCartRules ();
		}
		
		if (! $rule->getId ()) {
			return $this;
		}
		if (! $rule->getActions ()->validate ( $item )) {
			return $this;
		}
		
		Mage::getSingleton ( 'rewards/salesrule_validator' )->addItemAppliedRuleId ( $item, $rule_id );
		
		$qty = $item->getQty ();
		if ($item->getParentItem ()) {
			$qty *= $item->getParentItem ()->getQty ();
		}
		$qty = $rule->getDiscountQty () ? min ( $qty, $rule->getDiscountQty () ) : $qty;
		$rulePercent = min ( 100, $rule->getDiscountAmount () );
		$discountAmount = 0;
		$baseDiscountAmount = 0;
		
		//@nelkaake 17/03/2010 5:09:27 AM : is this the last item?
		$all_items = $item->getQuote ()->getAllItems ();
		
		$shipping_amount = $address->getShippingAmount ();
		$base_shipping_amount = $address->getBaseShippingAmount ();
		
		$itemPrice = $item->getDiscountCalculationPrice ();
		if ($itemPrice !== null) {
			$baseItemPrice = $item->getBaseDiscountCalculationPrice ();
		} else {
			$itemPrice = $item->getCalculationPrice ();
			$baseItemPrice = $item->getBaseCalculationPrice ();
		}
		
		Mage::getSingleton ( 'rewards/redeem' )->refactorRedemptions ( $all_items, false );
		switch ($rule->getSimpleAction ()) {
			case 'to_percent' :
			//@nelkaake -a 16/11/10: THIS TYPE OF DISCOUNT WAS ABANDONED BY MAGENTO
			//$rulePercent = max(0, 100-$rule->getDiscountAmount());
			//no break;
			

			case 'by_percent' :
				
				//@mhadianfard -c 16/11/10: 
				$cartRules = $this->cart_fixed_rules;
				// WDCA CODE BEGIN
				

				//@nelkaake -a 28/11/10: First calculate the total discount on the cart
				if (! isset ( $cartRules [$rule->getId ()] )) {
					$totalDiscountOnCart = $this->_getTotalPercentDiscount ( $item, $address, $rule, $qty );
					$cartRules [$rule->getId ()] = $totalDiscountOnCart;
					
					//@nelkaake -a 28/11/10: if this was a by points spent 
					if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
						$this->_registerPointsSpentDiscount ( $rule, $cartRules [$rule->getId ()] );
					}
					$addrCartRules = is_array ( $address->getCartFixedRules () ) ? $address->getCartFixedRules () : array ();
					$addrCartRules [$rule->getId ()] = $cartRules [$rule->getId ()];
					$address->setCartFixedRules ( $addrCartRules );
				}
				
				//@nelkaake -a 28/11/10: If we've already calculated the total discount on the cart, start trying to discount per item.
				if ($cartRules [$rule->getId ()] > 0) {
					list ( $discountAmount, $baseDiscountAmount ) = $this->_getTotalPercentDiscountOnitem ( $item, $address, $rule, $cartRules, $qty );
					if (! $this->isCfrProcessed ( $item, $rule_id )) {
						$cartRules [$rule->getId ()] -= $baseDiscountAmount;
						$this->setIsCfrProcessed ( $item, $rule_id );
					}
				}
				
				// WDCA CODE END
				$this->cart_fixed_rules = $cartRules;
				$address->setCartFixedRules ( $cartRules );
				break;
			
			case 'to_fixed' :
				$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
				$discountAmount = $qty * ($item->getCalculationPrice () - $quoteAmount);
				$baseDiscountAmount = $qty * ($item->getBaseCalculationPrice () - $rule->getDiscountAmount ());
				break;
			
			case 'by_fixed' :
				if ($step = $rule->getDiscountStep ()) {
					$qty = floor ( $qty / $step ) * $step;
				}
				$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
				$discountAmount = $qty * $quoteAmount;
				$baseDiscountAmount = $qty * $rule->getDiscountAmount ();
				break;
			
			case 'cart_fixed' :
				$cartRules = $this->cart_fixed_rules;
				// WDCA CODE BEGIN
				if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.2' )) {
					if (empty ( $this->_rulesItemTotals [$rule->getId ()] )) {
						Mage::throwException ( Mage::helper ( 'salesrule' )->__ ( 'Item totals are not set for rule.' ) );
					}
				}
				
				if (! isset ( $cartRules [$rule->getId ()] )) {
					$cartRules [$rule->getId ()] = $this->_getTotalFixedDiscountOnCart ( $item, $address, $rule );
					
					if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
						$this->_registerPointsSpentDiscount ( $rule, $cartRules [$rule->getId ()] );
					}
					
					$addrCartRules = is_array ( $address->getCartFixedRules () ) ? $address->getCartFixedRules () : array ();
					$addrCartRules [$rule->getId ()] = $cartRules [$rule->getId ()];
					$address->setCartFixedRules ( $addrCartRules );
				
				}
				
				//@nelkaake Wednesday May 5, 2010 RM: 
				if ($cartRules [$rule->getId ()] > 0) {
					list ( $discountAmount, $baseDiscountAmount ) = $this->_getTotalFixedDiscountOnitem ( $item, $address, $rule, $cartRules );
					//@nelkaake Changed on Monday August 23, 2010: 
					if (! $this->isCFRProcessed ( $item, $rule->getId () )) {
						if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.2' )) {
							$cartRules2 = $cartRules;
							$cartRules2 [$rule->getId ()] -= $baseDiscountAmount;
							$address->setCartFixedRules2 ( $cartRules2 );
							$cartRules [$rule->getId ()] -= $baseDiscountAmount;
						} else {
							$cartRules [$rule->getId ()] -= $baseDiscountAmount;
						}
						$this->setIsCFRProcessed ( $item, $rule->getId () );
					}
				}
				
				if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.2' )) {
					$address->setCartFixedRules ( $cartRules );
				}
				
				// WDCA CODE END
				$this->cart_fixed_rules = $cartRules;
				$address->setCartFixedRules ( $cartRules );
				break;
			
			case 'buy_x_get_y' :
				$x = $rule->getDiscountStep ();
				$y = $rule->getDiscountAmount ();
				if (! $x || $y >= $x) {
					break;
				}
				$buy = 0;
				$free = 0;
				while ( $buy + $free < $qty ) {
					$buy += $x;
					if ($buy + $free >= $qty) {
						break;
					}
					$free += min ( $y, $qty - $buy - $free );
					if ($buy + $free >= $qty) {
						break;
					}
				}
				$discountAmount = $free * $item->getCalculationPrice ();
				$baseDiscountAmount = $free * $item->getBaseCalculationPrice ();
				break;
		
		}
		
		$this->total_discount_reversed += $discountAmount;
		$this->total_base_discount_reversed += $baseDiscountAmount;
		
		//WDCA BEGIN
		$discountAmount = $quote->getStore ()->roundPrice ( $discountAmount );
		$baseDiscountAmount = $quote->getStore ()->roundPrice ( $baseDiscountAmount );
		
		//@nelkaake This is the discount applied twice, the first time raw second time rounded
		$dada = $item->getDiscountAmount () + $discountAmount;
		$base_dada = $item->getBaseDiscountAmount () + $baseDiscountAmount;
		
		$row_total = $item->getRowTotal ();
		$base_row_total = $item->getBaseRowTotal ();
		if (Mage::helper ( 'tax' )->discountTax ( $store ) && ! Mage::helper ( 'tax' )->applyTaxAfterDiscount ( $store )) {
			$row_total += (($item->getTaxAmount () / $item->getQty ()) * $qty);
			$base_row_total += (($item->getBaseTaxAmount () / $item->getQty ()) * $qty);
		}
		
		$discountAmount = min ( $dada, $row_total + $shipping_amount );
		$baseDiscountAmount = min ( $base_dada, $base_row_total + $base_shipping_amount );
		
		//@nelkaake Added on Wednesday May 5, 2010: Check to make sure that the new disocunt does not increase max 
		// discounts more than row total.
		$discount_diff = $dada - ($row_total + $shipping_amount);
		if ($discount_diff > 0) {
			$base_discount_diff = $base_dada - ($base_row_total + $base_shipping_amount);
			$fullDiscountAmount = $discountAmount + $discount_diff;
			$fullBaseDiscountAmount = $baseDiscountAmount + $base_discount_diff;
		} else {
			$fullDiscountAmount = $dada;
			$fullBaseDiscountAmount = $base_dada;
		}
		//WDCA END
		

		//@nelkaake Added on Wednesday May 5, 2010: 
		Mage::getSingleton ( 'rewards/salesrule_discountmanager' )->setDiscount ( $rule, $fullDiscountAmount - $item->getDiscountAmount (), $fullBaseDiscountAmount - $item->getBaseDiscountAmount () );
		$item->setDiscountAmount ( $discountAmount );
		$item->setBaseDiscountAmount ( $baseDiscountAmount );
		
		return $this;
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
		$all_items = $item->getQuote ()->getAllItems ();
		$store = $item->getQuote ()->getStore ();
		$qty = empty ( $qty ) ? $item->getQty () : ( int ) $qty;
		
		//@nelkaake -a 1/12/10: Confusing way how this returns in mid-method
		if ($rule->getPointsAction () != TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
			$discountPercent = ($rule->getDiscountAmount () / 100);
			$totalDiscountOnCart = 0;
			foreach ( $all_items as $cartItem ) {
				if (! $rule->getActions ()->validate ( $cartItem )) {
					continue;
				}
				$discountOnItem = (($cartItem->getRowTotal () / $item->getQty ()) * $qty) * $discountPercent;
				$totalDiscountOnCart += $discountOnItem;
			}
			return $totalDiscountOnCart;
		} // else, this is a by points spent rule:  
		

		$points_spent = Mage::getSingleton ( 'rewards/session' )->getPointsSpending ();
		$discountPercent = (($rule->getDiscountAmount () * floor ( ($points_spent / $rule->getPointsAmount ()) )) / 100);
		$discountPercent = min ( $discountPercent, 1 );
		
		$totalDiscountOnCart = 0;
		$totalAmountToDiscount = 0;
		foreach ( $all_items as $cartItem ) {
			if (! $rule->getActions ()->validate ( $cartItem )) {
				continue;
			}
			if (Mage::helper ( 'tax' )->discountTax ( $store ) && ! Mage::helper ( 'tax' )->applyTaxAfterDiscount ( $store )) {
				$totalAmountToDiscount += $cartItem->getRowTotalInclTax (); // $cartItem->getTaxAmount();
			} else {
				$totalAmountToDiscount += $cartItem->getRowTotal (); // $cartItem->getTaxAmount();
			}
		
		//$cartItem->printPre();
		}
		
		// @nelkaake -a 16/11/10: 
		if ($rule->getApplyToShipping ()) {
			$totalAmountToDiscount += $address->getShippingAmount ();
		}
		
		$totalDiscountOnCart = $totalAmountToDiscount * $discountPercent;
		return $totalDiscountOnCart;
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
		$qty = empty ( $qty ) ? $item->getQty () : ( int ) $qty;
		
		$quoteAmount = $quote->getStore ()->convertPrice ( $cartRules [$rule->getId ()] );
		$quoteAmountBase = $cartRules [$rule->getId ()];
		
		if (Mage::helper ( 'tax' )->discountTax ( $store ) && ! Mage::helper ( 'tax' )->applyTaxAfterDiscount ( $store )) {
			$tax_amount = (($item->getTaxAmount () / $item->getQty ()) * $qty);
			$base_tax_amount = (($item->getBaseTaxAmount () / $item->getQty ()) * $qty);
			$quoteAmount += $tax_amount; // $cartItem->getTaxAmount();
			$quoteAmountBase += $base_tax_amount; // $cartItem->getTaxAmount();
		} else {
			$tax_amount = $base_tax_amount = 0;
		}
		
		$shipping_amount = $address->getShippingAmount ();
		$base_shipping_amount = $address->getBaseShippingAmount ();
		$add_shipping = $rule->getApplyToShipping () ? $shipping_amount : 0;
		$add_base_shipping = $rule->getApplyToShipping () ? $base_shipping_amount : 0;
		
		$discountAmount = min ( (($item->getRowTotal () / $item->getQty ()) * $qty) - $item->getDiscountAmount () + $add_shipping + $tax_amount, $quoteAmount );
		$baseDiscountAmount = min ( (($item->getBaseRowTotal () / $item->getQty ()) * $qty) - $item->getBaseDiscountAmount () + $add_base_shipping + $base_tax_amount, $quoteAmountBase );
		return array ($discountAmount, $baseDiscountAmount );
	}
	
	/**
	 * Returns a total discount on the cart from the provided items
	 * @deprecated @see TBT_Rewards_Model_Salesrule_Discount_Action_Cartfixed
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @param TBT_Rewards_Model_Sales_Rule $rule
	 * @return float
	 */
	protected function _getTotalFixedDiscountOnCart($item, $address, $rule) {
		if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
			$points_spent = Mage::getSingleton ( 'rewards/session' )->getPointsSpending ();
			$totalDiscountOnCart = $rule->getDiscountAmount () * floor ( ($points_spent / $rule->getPointsAmount ()) );
		} else {
			$totalDiscountOnCart = $rule->getDiscountAmount ();
		}
		return $totalDiscountOnCart;
	}
	/**
	 * Returns a total discount on the cart from the provided items
	 * @deprecated @see TBT_Rewards_Model_Salesrule_Discount_Action_Cartfixed
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @param TBT_Rewards_Model_Sales_Rule $rule
	 * @param array() &$cartRules
	 * @return array($discountAmount, $baseDiscountAmount)
	 */
	protected function _getTotalFixedDiscountOnitem($item, $address, $rule, &$cartRules) {
		$quote = $item->getQuote ();
		$store = $item->getQuote ()->getStore ();
		$quoteAmount = $quote->getStore ()->convertPrice ( $cartRules [$rule->getId ()] );
		
		if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
			$points_spent = Mage::getSingleton ( 'rewards/session' )->getPointsSpending ();
			$multiplier = floor ( ($points_spent / $rule->getPointsAmount ()) );
		} else {
			$multiplier = 1;
		}
		$quoteAmount = $quote->getStore ()->convertPrice ( $cartRules [$rule->getId ()] );
		
		if (Mage::helper ( 'tax' )->discountTax ( $store ) && ! Mage::helper ( 'tax' )->applyTaxAfterDiscount ( $store )) {
			$tax_amount = $item->getTaxAmount ();
			$base_tax_amount = $item->getBaseTaxAmount ();
		} else {
			$tax_amount = 0;
			$base_tax_amount = 0;
		}
		
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.2' )) {
			$itemPrice = $this->_getItemPrice ( $item );
			$baseItemPrice = $this->_getItemBasePrice ( $item );
			$qty = $this->_getItemQty ( $item, $rule );
			
			if (1 >= $this->_rulesItemTotals [$rule->getId ()] ['items_count']) {
				$quoteAmount = $quote->getStore ()->convertPrice ( $cartRules [$rule->getId ()] );
				
				$discountAmount = min ( $itemPrice * $qty, $quoteAmount );
				$baseDiscountAmount = min ( $baseItemPrice * $qty, $cartRules [$rule->getId ()] );
			} else {
				$discountRate = $baseItemPrice * $qty / $this->_rulesItemTotals [$rule->getId ()] ['base_items_price'];
				$maximumItemDiscount = $rule->getDiscountAmount () * $discountRate;
				$quoteAmount = $quote->getStore ()->convertPrice ( $maximumItemDiscount );
				
				$discountAmount = min ( $itemPrice * $qty, $quoteAmount );
				$baseDiscountAmount = min ( $baseItemPrice * $qty, $maximumItemDiscount );
				$this->_rulesItemTotals [$rule->getId ()] ['items_count'] --;
			}
		} else {
			$discountAmount = min ( $item->getRowTotal () - $item->getDiscountAmount () + $tax_amount, $quoteAmount );
			$baseDiscountAmount = min ( $item->getBaseRowTotal () - $item->getBaseDiscountAmount () + $base_tax_amount, $cartRules [$rule->getId ()] );
		}
		
		return array ($discountAmount, $baseDiscountAmount );
	}
	
	/**
	 * Apply discounts to shipping amount
	 *
	 * @param   Mage_Sales_Model_Quote_Address $address
	 * @return  Mage_SalesRule_Model_Validator
	 */
	public function processShippingAmount(Mage_Sales_Model_Quote_Address $address) {
		$shippingAmount = $address->getShippingAmountForDiscount ();
		if ($shippingAmount !== null) {
			$baseShippingAmount = $address->getBaseShippingAmountForDiscount ();
		} else {
			$shippingAmount = $address->getShippingAmount ();
			$baseShippingAmount = $address->getBaseShippingAmount ();
		}
		$quote = $address->getQuote ();
		$appliedRuleIds = array ();
		foreach ( $this->_getRules () as $rule ) {
			/* @var $rule Mage_SalesRule_Model_Rule */
			if (! $rule->getApplyToShipping () || ! $this->_canProcessRule ( $rule, $address )) {
				continue;
			}
			
			// WDCA BEGIN 
			//@nelkaake -a 16/11/10: 
			$points_rule = $this->getRule ( $rule->getId () );
			if ($points_rule->isPointsRule ()) {
				if ($points_rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
					$rule->setDiscountAmount ( 0 );
				}
			}
			// WDCA END 
			

			$discountAmount = 0;
			$baseDiscountAmount = 0;
			$rulePercent = min ( 100, $rule->getDiscountAmount () );
			switch ($rule->getSimpleAction ()) {
				case 'to_percent' :
					$rulePercent = max ( 0, 100 - $rule->getDiscountAmount () );
				case 'by_percent' :
					$discountAmount = ($shippingAmount - $address->getShippingDiscountAmount ()) * $rulePercent / 100;
					$baseDiscountAmount = ($baseShippingAmount - $address->getBaseShippingDiscountAmount ()) * $rulePercent / 100;
					$discountPercent = min ( 100, $address->getShippingDiscountPercent () + $rulePercent );
					$address->setShippingDiscountPercent ( $discountPercent );
					break;
				case 'to_fixed' :
					$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
					$discountAmount = $shippingAmount - $quoteAmount;
					$baseDiscountAmount = $baseShippingAmount - $rule->getDiscountAmount ();
					break;
				case 'by_fixed' :
					$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
					$discountAmount = $quoteAmount;
					$baseDiscountAmount = $rule->getDiscountAmount ();
					break;
				
				case 'cart_fixed' :
					//@nelkaake -a 26/01/11: To resolve an issue with Magento 1.4.2 and the shipping discounts, we have to keep 
					// track of the cart fixed discounts separately from the old way that no longer works with Magento 1.4.2 
					// as well as the new way with Magento 1.4.2.  getCartFixedRules2 is the old way as stored by 
					// the recalculateDiscount function above.  That's what you see on the next 5 lines.
					if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.2' )) {
						$cartRules = $address->getCartFixedRules2 ();
					} else {
						$cartRules = $address->getCartFixedRules ();
					}
					
					if (! isset ( $cartRules [$rule->getId ()] )) {
						$cartRules [$rule->getId ()] = $rule->getDiscountAmount ();
					}
					if ($cartRules [$rule->getId ()] > 0) {
						$quoteAmount = $quote->getStore ()->convertPrice ( $cartRules [$rule->getId ()] );
						$discountAmount = min ( $shippingAmount - $address->getShippingDiscountAmount (), $quoteAmount );
						$baseDiscountAmount = min ( $baseShippingAmount - $address->getBaseShippingDiscountAmount (), $cartRules [$rule->getId ()] );
						$cartRules [$rule->getId ()] -= $baseDiscountAmount;
					}
					$address->setCartFixedRules ( $cartRules );
					break;
			}
			
			$discountAmount = min ( $address->getShippingDiscountAmount () + $discountAmount, $shippingAmount );
			$baseDiscountAmount = min ( $address->getBaseShippingDiscountAmount () + $baseDiscountAmount, $baseShippingAmount );
			$address->setShippingDiscountAmount ( $discountAmount );
			$address->setBaseShippingDiscountAmount ( $baseDiscountAmount );
			$appliedRuleIds [$rule->getRuleId ()] = $rule->getRuleId ();
			
			$this->_maintainAddressCouponCode ( $address, $rule );
			$this->_addDiscountDescription ( $address, $rule );
			if ($rule->getStopRulesProcessing ()) {
				break;
			}
		}
		$address->setAppliedRuleIds ( $this->mergeIds ( $address->getAppliedRuleIds (), $appliedRuleIds ) );
		$quote->setAppliedRuleIds ( $this->mergeIds ( $quote->getAppliedRuleIds (), $appliedRuleIds ) );
		return $this;
	}
	
	/**
	 * Have we processed looking at this cart fixed rule?
	 * @nelkaake Added on Wednesday May 5, 2010: 
	 * @nelkaake Changed on Monday August 23, 2010: 
	 *
	 * @param unknown_type $item
	 * @param unknown_type $rule_id
	 * @return unknown
	 */
	protected function isCFRProcessed($item, $rule_id) {
		$key = $item->getId () . "_" . $rule_id;
		
		$v = Mage::registry ( 'assex' );
		$v = ! is_array ( $v ) ? array () : $v;
		return isset ( $v [$key] );
	}
	/**
	 * Remember that this catalog fixed rule was processed for this item.
	 * @nelkaake Added on Wednesday May 5, 2010: 
	 * @nelkaake Changed on Monday August 23, 2010: 
	 * @param unknown_type $item
	 * @param unknown_type $rule_id
	 * @return unknown
	 */
	protected function setIsCFRProcessed($item, $rule_id) {
		$key = $item->getId () . "_" . $rule_id;
		
		$v = Mage::registry ( 'assex' );
		$v = ! is_array ( $v ) ? array () : $v;
		
		$v [$key] = true;
		Mage::unregister ( 'assex' );
		Mage::register ( 'assex', $v );
		return $this;
	}
	
	/**
	 * Quote item free shipping ability check
	 * This process not affect information about applied rules, coupon code etc.
	 * This information will be added during discount amounts processing
	 *
	 * @param   Mage_Sales_Model_Quote_Item_Abstract $item
	 * @return  Mage_SalesRule_Model_Validator
	 */
	public function processFreeShipping(Mage_Sales_Model_Quote_Item_Abstract $item) {
		$address = $this->_getAddress ( $item );
		$item->setFreeShipping ( false );
		$applied_redemptions = explode ( ",", $item->getAppliedRuleIds () ); //@nelkaake 04/03/2010 4:53:44 PM : WDCA
		foreach ( $this->_getRules () as $rule ) {
			/* @var $rule Mage_SalesRule_Model_Rule */
			if (! $this->_canProcessRule ( $rule, $address )) {
				continue;
			}
			
			if (! $rule->getActions ()->validate ( $item )) {
				continue;
			}
			//@nelkaake 04/03/2010 4:58:03 PM : WDCA CODE BEGIN
			$rewards_rule = $this->getRule ( $rule->getId () );
			// if it's a rdemption rule and it's not applied, continue on...
			if ($rewards_rule->isRedemptionRule ()) {
				if (array_search ( $rewards_rule->getId (), $applied_redemptions ) === false) {
					continue;
				}
			}
			// WDCA CODE END
			

			switch ($rule->getSimpleFreeShipping ()) {
				case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM :
					$item->setFreeShipping ( $rule->getDiscountQty () ? $rule->getDiscountQty () : true );
					break;
				
				case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS :
					$address->setFreeShipping ( true );
					break;
			}
			if ($rule->getStopRulesProcessing ()) {
				break;
			}
		}
		return $this;
	}
	
	public function originalProcess(Mage_Sales_Model_Quote_Item_Abstract $item) {
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.2' )) {
			return $this->originalProcessAfter142 ( $item );
		} elseif (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.1.0' )) {
			return $this->originalProcessAfter141 ( $item );
		} else {
			return $this->originalProcessBefore141 ( $item );
		}
	}
	
	public function originalProcessBefore141(Mage_Sales_Model_Quote_Item_Abstract $item) {
		$item->setFreeShipping ( false );
		$item->setDiscountAmount ( 0 );
		$item->setBaseDiscountAmount ( 0 );
		$item->setDiscountPercent ( 0 );
		
		$quote = $item->getQuote ();
		
		//@nelkaake I know it's wierd that these are still here despite the function not being called unless Mage < 141, but it is possible that this is needed for Magent 1.4.0 only.
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.1.0' )) {
			$address = $this->_getAddress ( $item );
			//Clearing applied rule ids for quote and address
			if ($this->_isFirstTimeProcessRun !== true) {
				$this->_isFirstTimeProcessRun = true;
				$quote->setAppliedRuleIds ( '' );
				$address->setAppliedRuleIds ( '' );
			}
		} else {
			if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
				$address = $item->getAddress ();
			} elseif ($quote->isVirtual ()) {
				$address = $quote->getBillingAddress ();
			} else {
				$address = $quote->getShippingAddress ();
			}
		}
		
		$customerId = $quote->getCustomerId ();
		$ruleCustomer = Mage::getModel ( 'salesrule/rule_customer' );
		$appliedRuleIds = array ();
		
		foreach ( $this->_getRules2 () as $rule ) {
			/* @var $rule Mage_SalesRule_Model_Rule */
			/**
			 * already tried to validate and failed
			 */
			if ($rule->getIsValid () === false) {
				continue;
			}
			
			// WDCA CODE BEGIN
			$rewards_rule = $this->getRule ( $rule->getId () );
			// WDCA CODE END
			

			if ($rule->getIsValid () !== true) {
				
				/**
				 * too many times used in general
				 */
				if ($rule->getUsesPerCoupon () && ($rule->getTimesUsed () >= $rule->getUsesPerCoupon ())) {
					$rule->setIsValid ( false );
					continue;
				}
				/**
				 * too many times used for this customer
				 */
				$ruleId = $rule->getId ();
				if ($ruleId && $rule->getUsesPerCustomer ()) {
					$ruleCustomer->loadByCustomerRule ( $customerId, $ruleId );
					if ($ruleCustomer->getId ()) {
						if ($ruleCustomer->getTimesUsed () >= $rule->getUsesPerCustomer ()) {
							continue;
						}
					}
				}
				$rule->afterLoad ();
				/**
				 * quote does not meet rule's conditions
				 */
				if (! $rule->validate ( $address )) {
					$rule->setIsValid ( false );
					continue;
				}
				/**
				 * passed all validations, remember to be valid
				 */
				$rule->setIsValid ( true );
			}
			
			/**
			 * although the rule is valid, this item is not marked for action
			 */
			if (! $rule->getActions ()->validate ( $item )) {
				continue;
			}
			
			//@nelkaake Added on Thursday August 19, 2010: 
			if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.1' )) {
				$qty = $item->getTotalQty ();
			} else {
				$qty = $item->getQty ();
				if ($item->getParentItem ()) {
					$qty *= $item->getParentItem ()->getQty ();
				}
			}
			
			$qty = $rule->getDiscountQty () ? min ( $qty, $rule->getDiscountQty () ) : $qty;
			$rulePercent = min ( 100, $rule->getDiscountAmount () );
			$discountAmount = 0;
			$baseDiscountAmount = 0;
			switch ($rule->getSimpleAction ()) {
				case 'to_percent' :
					$rulePercent = max ( 0, 100 - $rule->getDiscountAmount () );
				//no break;
				

				case 'by_percent' :
					if ($step = $rule->getDiscountStep ()) {
						$qty = floor ( $qty / $step ) * $step;
					}
					$discountAmount = ($qty * $item->getCalculationPrice () - $item->getDiscountAmount ()) * $rulePercent / 100;
					$baseDiscountAmount = ($qty * $item->getBaseCalculationPrice () - $item->getBaseDiscountAmount ()) * $rulePercent / 100;
					
					if (! $rule->getDiscountQty () || $rule->getDiscountQty () > $qty) {
						$discountPercent = min ( 100, $item->getDiscountPercent () + $rulePercent );
						$item->setDiscountPercent ( $discountPercent );
					}
					break;
				
				case 'to_fixed' :
					$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
					$discountAmount = $qty * ($item->getCalculationPrice () - $quoteAmount);
					$baseDiscountAmount = $qty * ($item->getBaseCalculationPrice () - $rule->getDiscountAmount ());
					break;
				
				case 'by_fixed' :
					if ($step = $rule->getDiscountStep ()) {
						$qty = floor ( $qty / $step ) * $step;
					}
					$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
					$discountAmount = $qty * $quoteAmount;
					$baseDiscountAmount = $qty * $rule->getDiscountAmount ();
					break;
				
				case 'cart_fixed' :
					$cartRules = $address->getCartFixedRules ();
					if (! isset ( $cartRules [$rule->getId ()] )) {
						$cartRules [$rule->getId ()] = $rule->getDiscountAmount ();
					}
					if ($cartRules [$rule->getId ()] > 0) {
						$quoteAmount = $quote->getStore ()->convertPrice ( $cartRules [$rule->getId ()] );
						$discountAmount = min ( $item->getRowTotal (), $quoteAmount );
						$baseDiscountAmount = min ( $item->getBaseRowTotal (), $cartRules [$rule->getId ()] );
						
						$cartRules [$rule->getId ()] -= $baseDiscountAmount;
					}
					$address->setCartFixedRules ( $cartRules );
					break;
				
				case 'buy_x_get_y' :
					$x = $rule->getDiscountStep ();
					$y = $rule->getDiscountAmount ();
					if (! $x || $y >= $x) {
						break;
					}
					$buy = 0;
					$free = 0;
					while ( $buy + $free < $qty ) {
						$buy += $x;
						if ($buy + $free >= $qty) {
							break;
						}
						$free += min ( $y, $qty - $buy - $free );
						if ($buy + $free >= $qty) {
							break;
						}
					}
					$discountAmount = $free * $item->getCalculationPrice ();
					$baseDiscountAmount = $free * $item->getBaseCalculationPrice ();
					break;
			}
			
			$result = new Varien_Object ( array ('discount_amount' => $discountAmount, 'base_discount_amount' => $baseDiscountAmount ) );
			Mage::dispatchEvent ( 'salesrule_validator_process', array ('rule' => $rule, 'item' => $item, 'address' => $address, 'quote' => $quote, 'qty' => $qty, 'result' => $result ) );
			
			$discountAmount = $result->getDiscountAmount ();
			$baseDiscountAmount = $result->getBaseDiscountAmount ();
			
			$discountAmount = $quote->getStore ()->roundPrice ( $discountAmount );
			$baseDiscountAmount = $quote->getStore ()->roundPrice ( $baseDiscountAmount );
			$discountAmount = min ( $item->getDiscountAmount () + $discountAmount, $item->getRowTotal () );
			$baseDiscountAmount = min ( $item->getBaseDiscountAmount () + $baseDiscountAmount, $item->getBaseRowTotal () );
			
			$item->setDiscountAmount ( $discountAmount );
			$item->setBaseDiscountAmount ( $baseDiscountAmount );
			
			//@nelkaake 2/6/2010 2:34:20 PM : WDCA CODE BEGIN (just added the if statement)
			if (! $rewards_rule->isPointsRule ()) {
				switch ($rule->getSimpleFreeShipping ()) {
					case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM :
						$item->setFreeShipping ( $rule->getDiscountQty () ? $rule->getDiscountQty () : true );
						break;
					
					case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS :
						$address->setFreeShipping ( true );
						break;
				}
			}
			//@nelkaake 2/6/2010 2:34:38 PM : WDCA CODE END
			

			$appliedRuleIds [$rule->getRuleId ()] = $rule->getRuleId ();
			
			//@nelkaake Added on Monday August 9, 2010:  
			//@nelkaake Changed on Monday August 23, 2010: 
			if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.1.0' )) {
				$this->_maintainAddressCouponCode ( $address, $rule );
			} else {
				if ($rule->getCouponCode () && (strtolower ( $rule->getCouponCode () ) == strtolower ( $this->getCouponCode () ))) {
					$address->setCouponCode ( $this->getCouponCode () );
				}
			}
			if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' )) {
				$this->_addDiscountDescription ( $address, $rule );
			}
			if ($rule->getStopRulesProcessing ()) {
				//@nelkaake Don't break the loop if this is a rewards rule: 
				if ($rewards_rule->isRedemptionRule ()) {
				
				} else {
					break;
				}
			}
		}
		
		$item->setAppliedRuleIds ( join ( ',', $appliedRuleIds ) );
		$address->setAppliedRuleIds ( $this->mergeIds ( $address->getAppliedRuleIds (), $appliedRuleIds ) );
		$quote->setAppliedRuleIds ( $this->mergeIds ( $quote->getAppliedRuleIds (), $appliedRuleIds ) );
		
		return $this;
	}
	
	public function originalProcessAfter141(Mage_Sales_Model_Quote_Item_Abstract $item) {
		$item->setDiscountAmount ( 0 );
		$item->setBaseDiscountAmount ( 0 );
		$item->setDiscountPercent ( 0 );
		$quote = $item->getQuote ();
		$address = $this->_getAddress ( $item );
		
		//Clearing applied rule ids for quote and address
		if ($this->_isFirstTimeProcessRun !== true) {
			$this->_isFirstTimeProcessRun = true;
			$quote->setAppliedRuleIds ( '' );
			$address->setAppliedRuleIds ( '' );
		}
		
		$itemPrice = $item->getDiscountCalculationPrice ();
		if ($itemPrice !== null) {
			$baseItemPrice = $item->getBaseDiscountCalculationPrice ();
		} else {
			$itemPrice = $item->getCalculationPrice ();
			$baseItemPrice = $item->getBaseCalculationPrice ();
		}
		
		$appliedRuleIds = array ();
		foreach ( $this->_getRules () as $rule ) {
			/* @var $rule Mage_SalesRule_Model_Rule */
			if (! $this->_canProcessRule ( $rule, $address )) {
				continue;
			}
			
			if (! $rule->getActions ()->validate ( $item )) {
				continue;
			}
			
			// WDCA CODE BEGIN
			$rewards_rule = $this->getRule ( $rule->getId () );
			// WDCA CODE END
			

			$qty = $item->getTotalQty ();
			$qty = $rule->getDiscountQty () ? min ( $qty, $rule->getDiscountQty () ) : $qty;
			$rulePercent = min ( 100, $rule->getDiscountAmount () );
			
			$discountAmount = 0;
			$baseDiscountAmount = 0;
			switch ($rule->getSimpleAction ()) {
				case 'to_percent' :
					$rulePercent = max ( 0, 100 - $rule->getDiscountAmount () );
				//no break;
				case 'by_percent' :
					$step = $rule->getDiscountStep ();
					if ($step) {
						$qty = floor ( $qty / $step ) * $step;
					}
					$discountAmount = ($qty * $itemPrice - $item->getDiscountAmount ()) * $rulePercent / 100;
					$baseDiscountAmount = ($qty * $baseItemPrice - $item->getBaseDiscountAmount ()) * $rulePercent / 100;
					
					if (! $rule->getDiscountQty () || $rule->getDiscountQty () > $qty) {
						$discountPercent = min ( 100, $item->getDiscountPercent () + $rulePercent );
						$item->setDiscountPercent ( $discountPercent );
					}
					break;
				case 'to_fixed' :
					$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
					$discountAmount = $qty * ($itemPrice - $quoteAmount);
					$baseDiscountAmount = $qty * ($baseItemPrice - $rule->getDiscountAmount ());
					break;
				
				case 'by_fixed' :
					$step = $rule->getDiscountStep ();
					if ($step) {
						$qty = floor ( $qty / $step ) * $step;
					}
					$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
					$discountAmount = $qty * $quoteAmount;
					$baseDiscountAmount = $qty * $rule->getDiscountAmount ();
					break;
				
				case 'cart_fixed' :
					$cartRules = $address->getCartFixedRules ();
					if (! isset ( $cartRules [$rule->getId ()] )) {
						$cartRules [$rule->getId ()] = $rule->getDiscountAmount ();
					}
					if ($cartRules [$rule->getId ()] > 0) {
						$quoteAmount = $quote->getStore ()->convertPrice ( $cartRules [$rule->getId ()] );
						/**
						 * We can't use row total here because row total not include tax
						 */
						$discountAmount = min ( $itemPrice * $qty - $item->getDiscountAmount (), $quoteAmount );
						$baseDiscountAmount = min ( $baseItemPrice * $qty - $item->getBaseDiscountAmount (), $cartRules [$rule->getId ()] );
						$cartRules [$rule->getId ()] -= $baseDiscountAmount;
					}
					$address->setCartFixedRules ( $cartRules );
					break;
				
				case 'buy_x_get_y' :
					$x = $rule->getDiscountStep ();
					$y = $rule->getDiscountAmount ();
					if (! $x || $y >= $x) {
						break;
					}
					$buy = 0;
					$free = 0;
					while ( $buy + $free < $qty ) {
						$buy += $x;
						if ($buy + $free >= $qty) {
							break;
						}
						$free += min ( $y, $qty - $buy - $free );
						if ($buy + $free >= $qty) {
							break;
						}
					}
					$discountAmount = $free * $itemPrice;
					$baseDiscountAmount = $free * $baseItemPrice;
					break;
			}
			
			$result = new Varien_Object ( array ('discount_amount' => $discountAmount, 'base_discount_amount' => $baseDiscountAmount ) );
			Mage::dispatchEvent ( 'salesrule_validator_process', array ('rule' => $rule, 'item' => $item, 'address' => $address, 'quote' => $quote, 'qty' => $qty, 'result' => $result ) );
			
			$discountAmount = $result->getDiscountAmount ();
			$baseDiscountAmount = $result->getBaseDiscountAmount ();
			
			$percentKey = $item->getDiscountPercent ();
			/**
			 * Process "delta" rounding
			 */
			if ($percentKey) {
				$delta = isset ( $this->_roundingDeltas [$percentKey] ) ? $this->_roundingDeltas [$percentKey] : 0;
				$baseDelta = isset ( $this->_baseRoundingDeltas [$percentKey] ) ? $this->_baseRoundingDeltas [$percentKey] : 0;
				$discountAmount += $delta;
				$baseDiscountAmount += $baseDelta;
				
				$this->_roundingDeltas [$percentKey] = $discountAmount - $quote->getStore ()->roundPrice ( $discountAmount );
				$this->_baseRoundingDeltas [$percentKey] = $baseDiscountAmount - $quote->getStore ()->roundPrice ( $baseDiscountAmount );
				$discountAmount = $quote->getStore ()->roundPrice ( $discountAmount );
				$baseDiscountAmount = $quote->getStore ()->roundPrice ( $baseDiscountAmount );
			} else {
				$discountAmount = $quote->getStore ()->roundPrice ( $discountAmount );
				$baseDiscountAmount = $quote->getStore ()->roundPrice ( $baseDiscountAmount );
			}
			
			/**
			 * We can't use row total here because row total not include tax
			 * Discount can be applied on price included tax
			 */
			$discountAmount = min ( $item->getDiscountAmount () + $discountAmount, $itemPrice * $qty );
			$baseDiscountAmount = min ( $item->getBaseDiscountAmount () + $baseDiscountAmount, $baseItemPrice * $qty );
			
			$item->setDiscountAmount ( $discountAmount );
			$item->setBaseDiscountAmount ( $baseDiscountAmount );
			
			//@nelkaake -a 16/11/10: 
			//@nelkaake 2/6/2010 2:34:20 PM : WDCA CODE BEGIN (just added the if statement)
			if (! $rewards_rule->isPointsRule ()) {
				switch ($rule->getSimpleFreeShipping ()) {
					case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM :
						$item->setFreeShipping ( $rule->getDiscountQty () ? $rule->getDiscountQty () : true );
						break;
					
					case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS :
						$address->setFreeShipping ( true );
						break;
				}
			}
			//@nelkaake 2/6/2010 2:34:38 PM : WDCA CODE END
			

			$appliedRuleIds [$rule->getRuleId ()] = $rule->getRuleId ();
			
			$this->_maintainAddressCouponCode ( $address, $rule );
			$this->_addDiscountDescription ( $address, $rule );
			
			//@nelkaake 2/6/2010 2:34:20 PM : WDCA CODE BEGIN (just added the if statement)
			if ($rule->getStopRulesProcessing ()) {
				//@nelkaake Don't break the loop if this is a rewards rule: 
				if (! $rewards_rule->isRedemptionRule ()) {
					break;
				}
			}
		
		//@nelkaake 2/6/2010 2:34:38 PM : WDCA CODE END
		

		}
		$item->setAppliedRuleIds ( join ( ',', $appliedRuleIds ) );
		$address->setAppliedRuleIds ( $this->mergeIds ( $address->getAppliedRuleIds (), $appliedRuleIds ) );
		$quote->setAppliedRuleIds ( $this->mergeIds ( $quote->getAppliedRuleIds (), $appliedRuleIds ) );
		return $this;
	}
	
	/**
	 * Quote item discount calculation process
	 *
	 * @param   Mage_Sales_Model_Quote_Item_Abstract $item
	 * @return  Mage_SalesRule_Model_Validator
	 */
	public function originalProcessAfter142(Mage_Sales_Model_Quote_Item_Abstract $item) {
		$item->setDiscountAmount ( 0 );
		$item->setBaseDiscountAmount ( 0 );
		$item->setDiscountPercent ( 0 );
		$quote = $item->getQuote ();
		$address = $this->_getAddress ( $item );
		
		//Clearing applied rule ids for quote and address
		if ($this->_isFirstTimeProcessRun !== true) {
			$this->_isFirstTimeProcessRun = true;
			$quote->setAppliedRuleIds ( '' );
			$address->setAppliedRuleIds ( '' );
		}
		
		$itemPrice = $this->_getItemPrice ( $item );
		$baseItemPrice = $this->_getItemBasePrice ( $item );
		
		if ($itemPrice <= 0) {
			return $this;
		}
		
		$appliedRuleIds = array ();
		foreach ( $this->_getRules () as $rule ) {
			/* @var $rule Mage_SalesRule_Model_Rule */
			if (! $this->_canProcessRule ( $rule, $address )) {
				continue;
			}
			
			if (! $rule->getActions ()->validate ( $item )) {
				continue;
			}
			
			// WDCA CODE BEGIN
			$rewards_rule = $this->getRule ( $rule->getId () );
			// WDCA CODE END
			

			$qty = $this->_getItemQty ( $item, $rule );
			$rulePercent = min ( 100, $rule->getDiscountAmount () );
			
			$discountAmount = 0;
			$baseDiscountAmount = 0;
			switch ($rule->getSimpleAction ()) {
				case Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION :
					$rulePercent = max ( 0, 100 - $rule->getDiscountAmount () );
				//no break;
				case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION :
					$step = $rule->getDiscountStep ();
					if ($step) {
						$qty = floor ( $qty / $step ) * $step;
					}
					$discountAmount = ($qty * $itemPrice - $item->getDiscountAmount ()) * $rulePercent / 100;
					$baseDiscountAmount = ($qty * $baseItemPrice - $item->getBaseDiscountAmount ()) * $rulePercent / 100;
					
					if (! $rule->getDiscountQty () || $rule->getDiscountQty () > $qty) {
						$discountPercent = min ( 100, $item->getDiscountPercent () + $rulePercent );
						$item->setDiscountPercent ( $discountPercent );
					}
					break;
				case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION :
					$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
					$discountAmount = $qty * ($itemPrice - $quoteAmount);
					$baseDiscountAmount = $qty * ($baseItemPrice - $rule->getDiscountAmount ());
					break;
				
				case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION :
					$step = $rule->getDiscountStep ();
					if ($step) {
						$qty = floor ( $qty / $step ) * $step;
					}
					$quoteAmount = $quote->getStore ()->convertPrice ( $rule->getDiscountAmount () );
					$discountAmount = $qty * $quoteAmount;
					$baseDiscountAmount = $qty * $rule->getDiscountAmount ();
					break;
				
				case Mage_SalesRule_Model_Rule::CART_FIXED_ACTION :
					if (empty ( $this->_rulesItemTotals [$rule->getId ()] )) {
						Mage::throwException ( Mage::helper ( 'salesrule' )->__ ( 'Item totals are not set for rule.' ) );
					}
					$cartRules = $address->getCartFixedRules ();
					if (! isset ( $cartRules [$rule->getId ()] )) {
						$cartRules [$rule->getId ()] = $rule->getDiscountAmount ();
					}
					
					if ($cartRules [$rule->getId ()] > 0) {
						if (1 >= $this->_rulesItemTotals [$rule->getId ()] ['items_count']) {
							$quoteAmount = $quote->getStore ()->convertPrice ( $cartRules [$rule->getId ()] );
							
							$discountAmount = min ( $itemPrice * $qty, $quoteAmount );
							$baseDiscountAmount = min ( $baseItemPrice * $qty, $cartRules [$rule->getId ()] );
						} else {
							$discountRate = $baseItemPrice * $qty / $this->_rulesItemTotals [$rule->getId ()] ['base_items_price'];
							$maximumItemDiscount = $rule->getDiscountAmount () * $discountRate;
							$quoteAmount = $quote->getStore ()->convertPrice ( $maximumItemDiscount );
							
							$discountAmount = min ( $itemPrice * $qty, $quoteAmount );
							$baseDiscountAmount = min ( $baseItemPrice * $qty, $maximumItemDiscount );
							$this->_rulesItemTotals [$rule->getId ()] ['items_count'] --;
						}
						$cartRules [$rule->getId ()] -= $baseDiscountAmount;
					}
					$address->setCartFixedRules ( $cartRules );
					break;
				
				case Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION :
					$x = $rule->getDiscountStep ();
					$y = $rule->getDiscountAmount ();
					if (! $x || $y >= $x) {
						break;
					}
					$buyAndDiscountQty = $x + $y;
					
					$fullRuleQtyPeriod = floor ( $qty / $buyAndDiscountQty );
					$freeQty = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;
					
					$discountQty = $fullRuleQtyPeriod * $y;
					if ($freeQty > $x) {
						$discountQty += $freeQty - $x;
					}
					
					$discountAmount = $discountQty * $itemPrice;
					$baseDiscountAmount = $discountQty * $baseItemPrice;
					break;
			}
			
			$result = new Varien_Object ( array ('discount_amount' => $discountAmount, 'base_discount_amount' => $baseDiscountAmount ) );
			Mage::dispatchEvent ( 'salesrule_validator_process', array ('rule' => $rule, 'item' => $item, 'address' => $address, 'quote' => $quote, 'qty' => $qty, 'result' => $result ) );
			
			$discountAmount = $result->getDiscountAmount ();
			$baseDiscountAmount = $result->getBaseDiscountAmount ();
			
			$percentKey = $item->getDiscountPercent ();
			/**
			 * Process "delta" rounding
			 */
			if ($percentKey) {
				$delta = isset ( $this->_roundingDeltas [$percentKey] ) ? $this->_roundingDeltas [$percentKey] : 0;
				$baseDelta = isset ( $this->_baseRoundingDeltas [$percentKey] ) ? $this->_baseRoundingDeltas [$percentKey] : 0;
				$discountAmount += $delta;
				$baseDiscountAmount += $baseDelta;
				
				$this->_roundingDeltas [$percentKey] = $discountAmount - $quote->getStore ()->roundPrice ( $discountAmount );
				$this->_baseRoundingDeltas [$percentKey] = $baseDiscountAmount - $quote->getStore ()->roundPrice ( $baseDiscountAmount );
				$discountAmount = $quote->getStore ()->roundPrice ( $discountAmount );
				$baseDiscountAmount = $quote->getStore ()->roundPrice ( $baseDiscountAmount );
			} else {
				$discountAmount = $quote->getStore ()->roundPrice ( $discountAmount );
				$baseDiscountAmount = $quote->getStore ()->roundPrice ( $baseDiscountAmount );
			}
			
			/**
			 * We can't use row total here because row total not include tax
			 * Discount can be applied on price included tax
			 */
			$discountAmount = min ( $item->getDiscountAmount () + $discountAmount, $itemPrice * $qty );
			$baseDiscountAmount = min ( $item->getBaseDiscountAmount () + $baseDiscountAmount, $baseItemPrice * $qty );
			
			$item->setDiscountAmount ( $discountAmount );
			$item->setBaseDiscountAmount ( $baseDiscountAmount );
			
			//@nelkaake -a 16/11/10: 
			//@nelkaake 2/6/2010 2:34:20 PM : WDCA CODE BEGIN (just added the if statement)
			if (! $rewards_rule->isPointsRule ()) {
				switch ($rule->getSimpleFreeShipping ()) {
					case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM :
						$item->setFreeShipping ( $rule->getDiscountQty () ? $rule->getDiscountQty () : true );
						break;
					
					case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS :
						$address->setFreeShipping ( true );
						break;
				}
			}
			//@nelkaake 2/6/2010 2:34:38 PM : WDCA CODE END
			

			$appliedRuleIds [$rule->getRuleId ()] = $rule->getRuleId ();
			
			$this->_maintainAddressCouponCode ( $address, $rule );
			$this->_addDiscountDescription ( $address, $rule );
			
			//@nelkaake 2/6/2010 2:34:20 PM : WDCA CODE BEGIN (just added the if statement)
			if ($rule->getStopRulesProcessing ()) {
				//@nelkaake Don't break the loop if this is a rewards rule: 
				if ($rewards_rule->isRedemptionRule ()) {
				
				} else {
					break;
				}
			}
		
		}
		$item->setAppliedRuleIds ( join ( ',', $appliedRuleIds ) );
		$address->setAppliedRuleIds ( $this->mergeIds ( $address->getAppliedRuleIds (), $appliedRuleIds ) );
		$quote->setAppliedRuleIds ( $this->mergeIds ( $quote->getAppliedRuleIds (), $appliedRuleIds ) );
		return $this;
	}
	
	public function getCartPointsSpendRedemRuleDiscountsTotal() {
		$total_discount = 0;
		foreach ( self::$cart_points_spend_redem_rule_discounts as $discount ) {
			$total_discount += $discount;
		}
		return $total_discount;
	}
	
	protected function _registerPointsSpentDiscount($rule, $amount) {
		self::$cart_points_spend_redem_rule_discounts [$rule->getId ()] = $amount;
		return $this;
	}

}