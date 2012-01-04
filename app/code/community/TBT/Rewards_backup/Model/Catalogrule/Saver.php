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
 * Catalog Rule Saver.  This method is used to save 
 * catalogrule information in the database when building flat tables and 
 * when the shopping cart quotes update.
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Catalogrule_Saver extends Mage_Core_Model_Abstract {
	
	const APPLICABLE_QTY = TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY;
	const POINTS_RULE_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID;
	const POINTS_AMT = TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT;
	const POINTS_CURRENCY_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID;
	const POINTS_USES = TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES;
	const POINTS_EFFECT = TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT;
	const POINTS_INST_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_INST_ID;
	
	/**
	 * Retrieve shopping cart model object
	 *
	 * @return Mage_Checkout_Model_Cart
	 */
	protected function _getCart() {
		return Mage::getSingleton ( 'checkout/cart' );
	}
	/**
	 * Get current active quote instance
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote() {
		return $this->_getCart ()->getQuote ();
	}
	
	/**
	 * Assumes product has already been added to the cart.
	 * @param TBT_Rewards_Model_Product $product
	 * @param Zend_Controller_Request_Http $request
	 */
	public function appendPointsToQuote($product, $apply_rule_id, $apply_rule_uses, $qty, $item = null) {
		//@nelkaake Added on Saturday September 4, 2010: 
		if (! $product)
			return $this;
		
		//@nelkaake Added on Saturday September 4, 2010:  
		if (! $apply_rule_uses)
			$apply_rule_uses = 0;
		if (! $apply_rule_id)
			return $this;
		
		if (empty ( $qty ))
			$qty = 1;
		
		$pId = $product->getId ();
		
		//@nelkaake -a 17/02/11: If the item is null, try to get it from the quote.
		$item = $item == null ? $this->_getQuote ()->getItemByProduct ( $product ) : $item;
		
		$date = Mage::helper ( 'rewards' )->now ();
		
		$storeId = $product->getStoreId ();
		$wId = Mage::app ()->getStore ( $storeId )->getWebsiteId ();
		
		$gId = Mage::getSingleton ( 'customer/session' )->getCustomerGroupId ();
		if ($gId !== 0 && empty ( $gId )) {
			$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
		}
		
		if (! $item) {
			return $this;
		}
		
		// 1. Validate rule
		if (empty ( $apply_rule_id ) && $apply_rule_id != '0') {
			// No new rule applied, so no need to adjust redeemed points set.
			Mage::getSingleton ( 'rewards/redeem' )->refactorRedemptions ( $item );
			return $this;
		}
		
		$this->updateRedeemedPointsHash ( $date, $wId, $gId, $pId, $item, $apply_rule_id, $qty, true, $apply_rule_uses );
		
		//@nelkaake -a 17/02/11: If an ID exists for the item, save the redemption  
		Mage::getSingleton ( 'rewards/redeem' )->refactorRedemptions ( $item, ($item->getId () ? true : false) );
		
		return $this;
	}
	
	/**
	 * Fetches the rewards session model
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	private function _getRewardsSession() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Adjusts a reedeemed points hash
	 * 
	 * @throws Exception
	 *
	 * @param timestamp $date
	 * @param int $wId
	 * @param int $gId
	 * @param int $pId
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @param int $apply_rule_id
	 * @param int $qty
	 * @param boolean $adjustQty	if true will set the price for that rule to the given qty, otherwise will add to the qty
	 */
	private function updateRedeemedPointsHash($date, $wId, $gId, $pId, $item, $apply_rule_id, $qty, $adjustQty = true, $uses = 1) {
		
		/** @var boolean $addedFlag true when the rule qty has been applied to the hash **/
		$mod_flag = false;
		/** @var boolean $customerCantAfford FALSE when the customer can't afford the attempted points redemption **/
		$customer_can_afford = true;
		/** @var boolean $guestNotAllowed FALSE when the customer is not logged in and guest redemptions are not enabled **/
		$guest_allowed = true;
		
		// 1.a Get Applicable rules
		$applicable_rule = Mage::getResourceModel ( 'rewards/catalogrule_rule' )->getApplicableReward ( $date, $wId, $gId, $pId, $apply_rule_id );
		$rule = Mage::getModel ( 'rewards/catalogrule_rule' )->load ( $apply_rule_id );
		$product = Mage::getModel ( 'rewards/catalog_product' )->load ( $pId );
		
		$currency_id = $applicable_rule [self::POINTS_CURRENCY_ID];
		$points_amount = $applicable_rule [self::POINTS_AMT];
		$to_spend = array ($currency_id => $points_amount );
		
		// Double check that the customer can use this rule.
		if ($this->_getRewardsSession ()->isCustomerLoggedIn ()) {
			//check logged in conditions
			$customer = $this->_getRewardsSession ()->getSessionCustomer ();
			$customer_can_afford = $customer->canAfford ( $to_spend );
		} else {
			// check logged out conditions
			$guest_allowed = Mage::helper ( 'rewards/config' )->canUseRedemptionsIfNotLoggedIn ();
		}
		
		$max_uses = $rule->getPointsUsesPerProduct ();
		if (! empty ( $max_uses )) {
			if ($max_uses < $uses) {
				$uses = $max_uses;
			
		// The user is trying to use more than the Maximum USES attribute.
			}
		}
		$uses = empty ( $uses ) ? 1 : $uses;
		
		// 1.b Check if requested rule is in applicable rules. 
		if (! $applicable_rule) {
			//throw new Exception("Rule $apply_rule_id no longer available for product $pId, group $gId, date $date and website $wId.");
			// A more friendly error:
			throw new Exception ( "One or more of points redemptions you are trying to do are no longer available. Please refresh the page." );
		}
		
		$redeemed_points = $applicable_rule;
		$redeemed_points [self::APPLICABLE_QTY] = $qty;
		$redeemed_points [self::POINTS_USES] = $uses;
		
		$product_price = Mage::helper ( 'rewards/price' )->getItemProductPrice ( $item );
		if (! $product_price) {
			Mage::helper ( 'rewards' )->notice ( "Price was 0.00 but the user tried to redeem point on the item.  You cannot allow customers to redeem points on a 0.00 product.  If you're trying to allow customers to *buy* products with points instead of money, set the normal price and add a redemption rule that sets the product price to $0 with X points." );
			return $this;
		}
		
		$cc_ratio = 0;
		if ($product_price > 0) {
			$cc = $item->getQuote ()->getStore ()->getCurrentCurrency ();
			$bc = 1 / ($item->getQuote ()->getStore ()->getBaseCurrency ()->getRate ( $cc ));
			$cc_ratio = $bc;
		}
		$product_price = $cc_ratio * $product_price;
		$redeemed_points [self::POINTS_EFFECT] = $this->_getHelp ()->amplifyEffect ( $product_price, $redeemed_points [self::POINTS_EFFECT], $uses );
		
		$points = Mage::helper ( 'rewards/transfer' )->calculateCatalogPoints ( $apply_rule_id, $item, true );
		if (! $points) {
			throw new Exception ( Mage::helper ( 'rewards' )->__ ( 'The catalog redemption rule entitled %s is invalid and cannot be applied.', $rule->getName () ) );
		}
		$redeemed_points [self::POINTS_AMT] = $uses * $points ['amount'] * - 1;
		
		$old_redeemed_points = Mage::helper ( 'rewards' )->unhashIt ( $item->getRedeemedPointsHash () );
		
		$new_redeemed_points = $old_redeemed_points; // copy data from OLD to NEW
		

		$num_products_currently_affected = 0;
		foreach ( $new_redeemed_points as $i => &$old_redeemed_points_line ) {
			$old_redeemed_points_line = ( array ) $old_redeemed_points_line;
			$num_products_currently_affected += $old_redeemed_points_line [self::APPLICABLE_QTY];
		}
		/**
		 * @var int $avail_extra_applic - the qty we have to work with.  That is, if we're increasing/adding
		 * any sort of redemptions the qty must be less than this amount.
		 */
		//@nelkaake -a 17/02/11: added $qty
		$avail_extra_applic = $item->getQty () - $num_products_currently_affected + $qty;
		
		$num_redemption_instances = 1;
		foreach ( $new_redeemed_points as $i => &$old_redeemed_points_line ) {
			$same_rule_id = $old_redeemed_points_line [self::POINTS_RULE_ID] == $apply_rule_id;
			$same_effects = $old_redeemed_points_line [self::POINTS_EFFECT] == $redeemed_points [self::POINTS_EFFECT];
			$same_num_uses = $old_redeemed_points_line [self::POINTS_USES] == $uses;
			if ($same_rule_id && $same_effects && $same_num_uses) {
				
				// Double check that the customer can use the rule that many times
				

				if ($adjustQty) {
					// Just append the cost with the adjustment qty
					$new_applic_qty = ($redeemed_points [self::APPLICABLE_QTY] + $old_redeemed_points_line [self::APPLICABLE_QTY]);
					// Check if we have room to add this redemption rule
					if ($redeemed_points [self::APPLICABLE_QTY] > $avail_extra_applic) {
						/*throw new Exception("You cannot apply $qty redemptions (max is $avail_extra_applic) ".
											"without overlapping with the other redemptions ".
											"(product id is $pId rule was $apply_rule_id and website $wId. ");
						*/
						return $this;
					
					}
				} else {
					// Set the qty manually.
					$new_applic_qty = $redeemed_points [self::APPLICABLE_QTY];
					if ($qty > 0) {
						// set the qty
						if ($new_applic_qty > $avail_extra_applic) {
							/*throw new Exception("You cannot apply $qty redemptions (max is $avail_extra_applic) ".
												"without overlapping with the other redemptions ".
												"(product id is $pId rule was $apply_rule_id and website $wId. ");
							*/
							return $this;
						}
					}
				
				}
				$old_redeemed_points_line [self::APPLICABLE_QTY] = $new_applic_qty;
				if (! isset ( $old_redeemed_points_line [self::POINTS_USES] ))
					$old_redeemed_points_line [self::POINTS_USES] = 0;
				
				$mod_flag = true;
			}
			$num_redemption_instances ++;
		}
		if (! $mod_flag && $qty != 0) {
			$redeemed_points [self::POINTS_INST_ID] = $num_redemption_instances;
			$new_redeemed_points [] = $redeemed_points;
			$mod_flag = true;
		}
		
		$new_redeemed_points_hash = Mage::helper ( 'rewards' )->hashIt ( $new_redeemed_points );
		$item->setRedeemedPointsHash ( $new_redeemed_points_hash );
		$item->unsetData ( "row_total_before_redemptions" );
		
		if ($item->getId ())
			$item->save ();
		
		return $this;
	}
	
	/**
	 * Fetches the rules hash for a given product entry from the database resource
	 * @param unknown_type $date
	 * @param unknown_type $wId
	 * @param unknown_type $gId
	 * @param unknown_type $pId
	 * @param unknown_type $item
	 * @param unknown_type $apply_rule_id
	 */
	private function getRuleHash($date, $wId, $gId, $pId, $item, $apply_rule_id) {
		$applicable_rule = Mage::getResourceModel ( 'rewards/catalogrule_rule' )->getApplicableReward ( $date, $wId, $gId, $pId, $apply_rule_id );
		
		return $applicable_rule;
	}
	
	/**
	 * Fetches the customer session singleton
	 *
	 * @return Mage_Customer_Model_Sesssion
	 */
	protected function _getCustSession() {
		return Mage::getSingleton ( 'customer/session' );
	}
	/**
	 * Gets the default rewards helper
	 *
	 * @return TBT_Rewards_Helper_Data
	 */
	private function _getHelp() {
		return Mage::helper ( 'rewards' );
	}
	
	/**
	 * Attempts to update the rule information entry for a product in the flat database table.
	 * @param int $product_id
	 */
	public function updateRulesHashOnProduct($product_id) {
		Varien_Profiler::start ( "TBT_Rewards:: Update rewards rule information on product" );
		$associated_rule_ids = Mage::helper ( 'rewards/transfer' )->getCatalogRewardsRuleIdsForProduct ( $product_id );
		$loaded_rules = array ();
		$is_redemption_rule = array ();
		
		$now = date ( "Y-m-d", strtotime ( now () ) );
		
		$read = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_read' );
		$select = $read->select ()->from ( Mage::getConfig ()->getTablePrefix () . "catalogrule_product_price", 'customer_group_id' )->where ( "`rule_date`='{$now}' AND `product_id`='{$product_id}'" );
		$collection = $read->fetchAll ( $select );
		
		foreach ( $collection as $row ) {
			$customer_group_id = $row ['customer_group_id'];
			
			if (! $associated_rule_ids) {
				return $this;
			}
			
			$row_hash = array ();
			foreach ( $associated_rule_ids as $rule_id ) {
				if (isset ( $loaded_rules [$rule_id] )) {
					$rule = &$loaded_rules [$rule_id];
				} else {
					$rule = Mage::getModel ( 'catalogrule/rule' )->load ( $rule_id );
					$loaded_rules [$rule_id] = $rule;
				}
				
				if (! $rule) {
					continue;
				}
				
				if (! isset ( $is_redemption_rule [$rule_id] )) {
					// TODO WDCA: change this to use catalogrule_actions
					$is_redemption_rule [$rule_id] = Mage::getModel ( 'rewards/salesrule_actions' )->isRedemptionAction ( $rule->getPointsAction () );
				}
				
				if ($is_redemption_rule [$rule_id]) {
					// TODO WDCA: any way to optimize this array_search?
					if (array_search ( $customer_group_id, $rule->getCustomerGroupIds () ) !== false) {
						/* TODO WDCA - validate that this rule exists within the current website */
						
						$effect = "";
						if ($rule->getPointsCatalogruleSimpleAction () == 'by_percent') {
							$effect = '-' . $rule->getPointsCatalogruleDiscountAmount () . '%';
						} else if ($rule->getPointsCatalogruleSimpleAction () == 'by_fixed') {
							$effect = '-' . $rule->getPointsCatalogruleDiscountAmount ();
						} else if ($rule->getPointsCatalogruleSimpleAction () == 'to_percent') {
							$effect = $rule->getPointsCatalogruleDiscountAmount () . '%';
						} else if ($rule->getPointsCatalogruleSimpleAction () == 'to_fixed') {
							$effect = $rule->getPointsCatalogruleDiscountAmount ();
						} else {
							continue;
						}
						
						$item_rule = array (TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT => $rule->getPointsAmount (), TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID => $rule->getPointsCurrencyId (), TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID => $rule_id, TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY => 0, TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT => $effect );
						
						$row_hash [] = $item_rule;
						
						break;
					}
				}
			}
			
			$write = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
			try {
				$write->beginTransaction ();
				$updateData = array ("rules_hash" => base64_encode ( json_encode ( $row_hash ) ) );
				$updateWhere = array ("`product_id`='{$product_id}' ", "`customer_group_id`='{$customer_group_id}' ", "`rule_date`='{$now}'" );
				$write->update ( Mage::getConfig ()->getTablePrefix () . "catalogrule_product_price", $updateData, $updateWhere );
				
				$write->commit ();
			
			} catch ( Exception $e ) {
				$write->rollback ();
			}
		}
		
		Varien_Profiler::stop ( "TBT_Rewards:: Update rewards rule information on product" );
		return $this;
	}
}
