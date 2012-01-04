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
class TBT_Rewards_Model_Salesrule_Discount_Validator extends Mage_SalesRule_Model_Validator {
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
	
	/**
	 * Reads in applied redemptions and checks if certain applied redemptions are also valid, then puts them in the ready list, if not, removes them from the list.
	 * 
	 * @param Mage_Sales_Model_Quote $quote
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @param Mage_Sales_Model_Quote_Address_Item $item
	 * @param int $rule_id
	 */
	public function validateRedemptionRule(&$quote, &$address, &$item, $rule_id, &$isValidApplicable = false) {
		$rule = $this->getRule ( $rule_id );
		$store = $item->getQuote ()->getStore (); //@nelkaake 17/03/2010 5:01:35 AM
		

		//@nelkaake -a 28/11/10: Get all the rule ids that were validated by Magento
		$valid = Mage::getModel ( 'rewards/salesrule_list_valid' )->initQuote ( $quote );
		$applied = Mage::getModel ( 'rewards/salesrule_list_applied' )->initQuote ( $quote );
		$valid_applicable = Mage::getModel ( 'rewards/salesrule_list_valid_applicable' )->initQuote ( $quote );
		$valid_applied = Mage::getModel ( 'rewards/salesrule_list_valid_applied' )->initQuote ( $quote );
		
		//echo "[{$rule_id}] BEFORE: valid={$valid->out()} applied={$applied->out()} valid_applicable={$valid_applicable->out()} valid_applied={$valid_applied->out()} <BR />";
		$valid_applicable->remove ( $rule );
		$valid_applied->remove ( $rule );
		$valid->remove ( $rule );
		
		if ($this->isValidRedemption ( $rule, $quote, $item )) { 
			$valid->add ( $rule );
			if($rule->isVariablePointsRule()) {
				$applied->add($rule);
			}
			if ($applied->hasRule ( $rule )) {
				$valid_applied->add ( $rule );
				$valid_applicable->remove ( $rule );
			} else {
				$isValidApplicable = true;
				$valid_applicable->add ( $rule );
				$valid_applied->remove ( $rule );
			}
		} else {
			$applied->remove ( $rule );
		}
		
		//echo "[{$rule_id}] AFTER: valid={$valid->out()} applied={$applied->out()} valid_applicable={$valid_applicable->out()} valid_applied={$valid_applied->out()} <BR />";
		$valid->saveToQuote ( $quote );
		$applied->saveToQuote ( $quote );
		$valid_applicable->saveToQuote ( $quote );
		$valid_applied->saveToQuote ( $quote );
		
		return $this;
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param TBT_Rewards_Model_Salesrule_Rule $rule
	 * @param Mage_Sales_Model_Quote_Item $quote
	 */
	public function isValidAppliedRedemption(TBT_Rewards_Model_Salesrule_Rule $rule, $item) {
		$quote = $item->getQuote ();
		
		if (! $this->isValidRedemption ( $rule, $quote, $item)) {
			return false;
		}
		
		$valid_applied = Mage::getModel ( 'rewards/salesrule_list' )->init ( $quote->getRewardsValidAppliedRedemptions () );
		
		return $valid_applied->hasRuleId ( $rule->getId () );
	
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param TBT_Rewards_Model_Salesrule_Rule $rule
	 * @param Mage_Sales_Model_Quote $quote
	 * @param Mage_Sales_Model_Quote_Address_Item $item
	 */
	public function isValidRedemption(TBT_Rewards_Model_Salesrule_Rule $rule, $quote, $item) {
		$rule_id = $rule->getId ();
		
		//@nelkaake -a 11/03/11: Firstly, only deal with points rules.
		if (! $rule->isPointsRule ()) {
			return false;
		}
		
		if (! $rule->isRedemptionRule ()) {
			return false;
		}
	
        if (!$rule->getActions()->validate($item)) {
        	return false;
        }
		
		return true;
	
	}
	
	/**
	 * Fetches a cached rule model
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	protected function &getRule($rule_id) {
		return Mage::helper ( 'rewards/rule' )->getSalesrule ( $rule_id );
	}
	
	/**
	 * 
	 * TODO does this need to be here?
	 * @deprecated does this need to be here?
	 * @param unknown_type $item_id
	 * @param unknown_type $cart_rule_id
	 */
	public function itemHasAppliedRid($item_id, $cart_rule_id) {
		//Mage::log("Reading RID map for item #". $item_id);
		if (! isset ( $this->item_rid_map [$item_id] ))
			return false;
		
		//Mage::log("Item applied rule ids: #". print_r($this->item_rid_map[$item_id], true));
		return (array_search ( $cart_rule_id, $this->item_rid_map [$item_id] ) !== false);
	}
	/**
	 * TODO does this need to be here?
	 * @deprecated does this need to be here?
	 * @param unknown_type $item
	 * @param unknown_type $rule_ids
	 */
	public function setItemAppliedRuleIds($item, $rule_ids) {
		$this->item_rid_map [$item->getId ()] = $rule_ids;
		//Mage::log("Wrote item RID map for item #". $item->getId() . ": ". print_r($rule_ids, true));
		return $this;
	}
	
	/**
	 * TODO does this need to be here?
	 * @deprecated does this need to be here?
	 * @param unknown_type $item
	 * @param unknown_type $rule_id
	 */
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
	 * TODO does this need to be here?
	 * @deprecated does this need to be here?
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
	
	/**
	 * Fetches the redemption calculator model
	 *
	 * @return TBT_Rewards_Model_Redeem
	 */
	private function _getRedeemer() {
		return Mage::getSingleton ( 'rewards/redeem' );
	}

}