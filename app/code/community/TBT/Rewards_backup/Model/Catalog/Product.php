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
 * Rewards Catalog Product
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Catalog_Product extends Mage_Catalog_Model_Product {
	
	protected static $rule_usage_map = null;
	
	/**
	 * Initialize resources
	 */
	protected function _construct() {
		return parent::_construct ();
	}
	
	/**
	 * Loads in a salesrule and returns a points salesrule
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return TBT_Rewards_Model_Catalog_Product
	 */
	public static function wrap(Mage_Catalog_Model_Product $product) {
		$rewards_product = Mage::getModel ( 'rewards/catalog_product' )->setData ( $product->getData () )->setId ( $product->getId () );
		return $rewards_product;
	}
	
	/**
	 * Calculates all the points being earned from distribution rules.
	 *
	 * @return array
	 */
	public function getDistriRules() {
		Varien_Profiler::start ( "TBT_Rewards::Fetching earnable points for product" );
		if (! $this->getId ()) {
			return array ();
		}
		
		Varien_Profiler::start ( "TBT_Rewards::Fetching earnable points for product (get rule ids)" );
		$ruleIds = Mage::helper ( 'rewards/transfer' )->getCatalogRewardsRuleIdsForProduct ( $this->getId () );
		Varien_Profiler::stop ( "TBT_Rewards::Fetching earnable points for product (get rule ids)" );
		$rules = array ();
		
		if ($ruleIds) {
			foreach ( $ruleIds as $ruleId ) {
				
				Varien_Profiler::start ( "TBT_Rewards::Fetching earnable points for product (calculate catalog points for rule)" );
				$pointsEarned = Mage::helper ( 'rewards/transfer' )->calculateCatalogPoints ( $ruleId, $this, false );
				Varien_Profiler::stop ( "TBT_Rewards::Fetching earnable points for product (calculate catalog points for rule)" );
				if ($pointsEarned ['amount'] == 0) {
					continue;
				}
				$crModel = Mage::helper ( 'rewards/transfer' )->getCatalogRule ( $ruleId );
				if ($crModel->isDistributionAction ()) {
					$rules [] = array ('rule_id' => $ruleId, 'caption' => $crModel->getName (), 'points' => $pointsEarned );
				}
			}
		}
		
		Varien_Profiler::stop ( "TBT_Rewards::Fetching earnable points for product" );
		
		return $rules;
	}
	
	/**
	 * This returns the product's price before tax. This is useful for when the magento store
	 * includes the tax within the price.
	 * 
	 * Currently this function has error with it. For now it will just return the final price
	 * 
	 * @return decimal
	 */
	public function getFinalPriceBeforeTax() {
		return $this->getFinalPrice ();
	}
	
	//@nelkaake 3/10/2010 4:31:06 PM : highly optimizes the fetching method.
	public function getEarnablePoints() {
		if (! $this->hasData ( "earnable_points" ))
			$this->setData ( "earnable_points", $this->getDistriRewards () );
		return $this->getData ( "earnable_points" );
	}
	
	//@nelkaake 3/10/2010 4:31:06 PM : highly optimizes the fetching method.
	public function getCatalogRuleCollection() {
		if (! $this->hasData ( "all_catalog_rules" ))
			$this->setData ( "all_catalog_rules", $this->getDistriRewards () );
		return $this->getData ( "earnable_points" );
	}
	
	/**
	 * Get distribution rule rewards.
	 * Sums up the rewards in the standard currency=>amt array format
	 *
	 * @return array
	 */
	public function getDistriRewards() {
		$rules = $this->getDistriRules ();
		$rewards = array ();
		if (sizeof ( $rules ) > 0) {
			foreach ( $rules as $rule_data ) {
				$c = $rule_data ['points'] ['currency'];
				if (! isset ( $rewards [$c] ))
					$rewards [$c] = 0;
				$rewards [$c] += $rule_data ['points'] ['amount'];
			}
		}
		
		return $rewards;
	}
	
	/**
	 * Returns an array of the lowest possible price using points, 
	 * and the points used to obtain that price
	 * 
	 * @return array
	 */
	public function getRewardAdjustedPrice() {
		$best_rules = $this->getBestPriceRules ();
		if (! $best_rules) {
			return array ('points_price' => Mage::helper ( 'core' )->formatCurrency ( $this->getFinalPriceBeforeTax () ), 'points_string' => '' );
		}
		$best_rule_array = $this->getBestValuedRule ( $best_rules );
		
		$iteration = $best_rule_array ['iteration'];
		$best_rule_id = $best_rule_array ['rule_id'];
		$best_rule = Mage::helper ( 'rewards/transfer' )->getCatalogRule ( $best_rule_id );
		
		//@nelkaake get base price instead
		$discounted_price = $this->getFinalPriceBeforeTax ();
		$discounted_price -= $this->getPriceDisposition ( $best_rule ) * $iteration;
		if ($discounted_price < 0) {
			$discounted_price = 0;
		}
		
		$points_for_rule = $this->getCatalogPointsForRule ( $best_rule );
		
		if (is_array ( $points_for_rule )) {
			$points_for_rule = $points_for_rule ['amount'];
		}
		if ($points_for_rule < 0) { // redemptions usually come up as a negative pts amt
			$points_for_rule = $points_for_rule * - 1;
		}
		
		$points_used = array ($best_rule->getPointsCurrencyId () => $points_for_rule * $iteration );
		
		$points_string = Mage::getModel ( 'rewards/points' )->set ( $points_used );
		
		$ret = array ('points_price' => Mage::helper ( 'core' )->formatCurrency ( $discounted_price ), 'points_string' => $points_string );
		
		return $ret;
	}
	
	/**
	 * Returns how much one iteration of the rule will change the price by
	 *
	 * @param TBT_Rewards_Model_Catalogrule_Rule||int $rule
	 * @param boolean $is_base     is this in base currency?
	 * 
	 * @nelkaake  12/01/2010 1:48:29 PM : added is_base option to fix issue that optimizer was not considering store currency        
	 */
	protected function getPriceDisposition($rule, $is_base = false) {
		if (! ($rule instanceof TBT_Rewards_Model_Catalogrule_Rule)) {
			// Assume integer was passed
			$rule = Mage::helper ( 'rewards/transfer' )->getCatalogRule ( $rule );
		}
		$effect = $rule->getEffect ();
		//@nelkaake  12/01/2010 1:48:29 PM : added is_base option to fix issue that optimizer was not considering store currency
		$temp_price = Mage::helper ( 'rewards' )->priceAdjuster ( $this->getFinalPriceBeforeTax (), $effect, ! $is_base );
		return $this->getFinalPriceBeforeTax () - $temp_price;
	}
	
	/**
	 * Returns an array of the rules which lower the price the most.
	 * There may be more then one rule in this array.
	 * the key is the rule, and the value is the number of iterations for the rule
	 * 
	 * @param array
	 * @param float
	 * @return array
	 */
	protected function getBestPriceRules() {
		$customer_point_balance = array ();
		
		//Create a map of all the currencies and the customers balance in each         
		$customer = $this->_getRewardsSess ()->getSessionCustomer ();
		if ($this->_getRewardsSess ()->isCustomerLoggedIn ()) {
			$customer_point_balance = $customer->getUsablePoints ();
			//@nelkaake Thursday May 27 : If the customer has 0 points, show highest possible points usage.
			if (! $customer->hasPoints ()) {
				$customer_point_balance = array (1 => Mage::helper ( 'rewards/config' )->getSimulatedPointMax () );
			}
		} else { // Check if customer is logged in. if not, show them the potential price using how many points possible
			foreach ( Mage::helper ( 'rewards/currency' )->getAvailCurrencyIds () as $curr_id ) {
				$customer_point_balance [$curr_id] = Mage::helper ( 'rewards/config' )->getSimulatedPointMax ();
			}
		}
		$best_rules = array ();
		$lowest_price = $this->getFinalPriceBeforeTax ();
		$rule_array = $this->getCatalogRedemptionRules ( $customer );
		
		foreach ( $rule_array as $rule_hash ) {
			$rule = ( array ) $rule_hash;
			$rule = Mage::helper ( 'rewards/transfer' )->getCatalogRule ( $rule ['rule_id'] );
			//@nelkaake  12/01/2010 1:48:29 PM : added is_base option to fix issue that optimizer was not considering store currency (just next line)
			$price_diff = $this->getPriceDisposition ( $rule->getId (), true );
			$points_cost = $rule->getPointsAmount ();
			$points_curr = $rule->getPointsCurrencyId ();
			
			if ($price_diff == 0) {
				$priceIteration = Mage::helper ( 'rewards/config' )->getSimulatedPointMax ();
			} else {
				$priceIteration = ceil ( $this->getFinalPriceBeforeTax () / $price_diff ); //How many calls till price is 0
			}
			
			if ($points_cost == 0) {
				$pointIteration = Mage::helper ( 'rewards/config' )->getSimulatedPointMax ();
			} else {
				$pointIteration = floor ( $customer_point_balance [$points_curr] / $points_cost ); //How many calls till poitns are 0
			}
			
			if ($rule->getPointsUsesPerProduct () == 0) {
				$usesIteration = Mage::helper ( 'rewards/config' )->getSimulatedPointMax ();
			} else {
				$usesIteration = $rule->getPointsUsesPerProduct (); //How many allowed calls
			}
			
			$lowestIteration = min ( $priceIteration, $pointIteration, $usesIteration );
			
			$temp_price = $this->getFinalPriceBeforeTax () - $price_diff * $lowestIteration;
			
			if ($temp_price < 0)
				$temp_price = 0;
			
			if ($temp_price < $lowest_price) {
				$lowest_price = $temp_price;
				$best_rules = array ();
				$best_rules [$rule->getId ()] = $lowestIteration;
			} elseif ($temp_price == $lowest_price) {
				$best_rules [$rule->getId ()] = $lowestIteration;
			}
		}
		return $best_rules;
	}
	
	/**
	 * Figures out which rule costs the customer the least in the number of points
	 * if there is a tie, it will choose the first one.
	 * returns in the format array('rule' => rule, 'iteration' => iteration)
	 * 
	 * @param array
	 * @return array
	 */
	protected function getBestValuedRule($rule_array) {
		/*
         * Revision by Mohsen on November 29, 2010
         * To resolve issue #0000467
         * Added minumum_points_used check to optimize points when multiple rules give same discount  
         */
		$best_rule = array ();
		$best_value = Mage::helper ( 'rewards/config' )->getSimulatedPointMax ();
		$minimum_points_used = Mage::helper ( 'rewards/config' )->getSimulatedPointMax (); // start off with alot of points
		foreach ( $rule_array as $rule_id => $iteration ) {
			$rule = Mage::helper ( 'rewards/transfer' )->getCatalogRule ( $rule_id );
			$temp_points_used = $iteration * $rule->getPointsAmount (); // find out how many points will be used if this rule is selected
			$temp_value = $rule->getBaseCurrencyValue () * $temp_points_used;
			
			if ($temp_value <= $best_value && $temp_points_used < $minimum_points_used) { // if discount is greater or equal to another rule, also check for minimized points usage
				$best_value = $temp_value;
				$minimum_points_used = $temp_points_used;
				$best_rule = array ('rule_id' => $rule_id, 'iteration' => $iteration );
			}
		}
		return $best_rule;
	}
	
	/**
	 * Calculates how many points 
	 *
	 * @param TBT_Rewards_Model_Catalogrule_Rule|int $rule      : id or model
	 * @return array
	 */
	public function getCatalogPointsForRule($rule) {
		if ($rule instanceof TBT_Rewards_Model_Catalogrule_Rule) {
			$rule_id = $rule->getId ();
		} else {
			$rule_id = $rule;
		}
		
		// calculate the proper points quantity based on rule and item
		$points = Mage::helper ( 'rewards/transfer' )->calculateCatalogPoints ( $rule_id, $this, true );
		
		return $points;
	}
	
	/**
	 * Fetches a list of all the applicable rules for this product.
	 *
	 * @param unknown_type $date
	 * @param integer $wId website id
	 * @param integer $gId group id
	 * @return array
	 */
	public function getApplicableCatalogRules($date, $wId, $gId) {
		$res = Mage::getResourceModel ( 'rewards/catalogrule_rule' );
		$applicable_rules = $res->getApplicableRedemptionRewards ( $date, $wId, $gId, $this->getId () );
		return $applicable_rules;
	}
	
	/**
	 * Fetches redemption catalog rules for this products
	 *
	 * @param TBT_Rewards_Model_Customer $customer
	 * @return array
	 */
	public function getCatalogRedemptionRules($customer) {
		$datetime = Mage::helper ( 'rewards' )->now ();
		$wId = Mage::app ()->getStore ( true )->getWebsiteId ();
		if ($customer) {
			$gId = $customer->getGroupId ();
		} else {
			$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
		}
		$rule_array = $this->getApplicableCatalogRules ( $datetime, $wId, $gId );
		
		return $rule_array;
	}
	
	/**
	 * Gets a list of all rule ID's that are associated with the given product id.
	 * @see THIS GETS ALL RULES!!!!!!
	 *
	 * @param integer $wId	website id
	 * @param integer $gId	group id
	 * @return  array(int)                          : An array of rule ID's that are associated with the item
	 */
	public function getCatalogRuleIds($wId = null, $gId = null, $date = null) {
		return $this->getCatalogRewardsRuleIdsForProduct ( $wId, $gId, $date = null );
	}
	
	/**
	 * Gets a list of all rule ID's that are associated with the given product id.
	 * @see THIS GETS ALL RULES!!!!!!
	 *
	 * @param integer $wId	website id
	 * @param integer $gId	group id
	 * @return  array(int)                          : An array of rule ID's that are associated with the item
	 */
	public function getCatalogRewardsRuleIdsForProduct($wId = null, $gId = null, $date = null) {
		/* TODO: make this method return REWARDS-SYSTEM rule id's ONLY */
		// look up all rule objects associated with this item
		$now = ($date == null) ? Mage::helper ( 'rewards' )->now () : $date;
                $wId = ($wId == null) ? Mage::app ()->getStore ()->getWebsiteId () : $wId;
                
		$gId = ($gId == null) ? Mage::getSingleton ( 'customer/session' )->getCustomerGroupId () : $gId;
		
		$productId = $this->getId ();
                
		$rule_ids = array ();
		
		// The getRuleProductsForDateRange function is removed completely in Magento v1.6+
        if (Mage::helper('rewards')->isBaseMageVersionAtLeast('1.5.1')) {
            $rule_data = Mage::getResourceModel('catalogrule/rule')->getRulesFromProduct($now, $wId, $gId, $productId);
            if ($rule_data) {
                foreach ( $rule_data as $ruleId => $rule ) {
                    $rule_ids [] = (int)$rule ['rule_id'];                        
                }
            }
            
        // For older versions of Magento we can use getRuleProductsForDateRange
        } else {
            $rule_data = Mage::getResourceModel( 'catalogrule/rule')->getRuleProductsForDateRange($now, $now, $productId);
            if ($rule_data) {
                foreach ( $rule_data as $ruleId => $rule ) {
                    if (($rule ['from_time'] != 0) && (strtotime ( $now ) < $rule ['from_time'])) {
                            continue;
                    }
                    if (($rule ['to_time'] != 0) && (strtotime ( $now ) > $rule ['to_time'])) {
                            continue;
                    }
                    if ($rule ['website_id'] != $wId) {
                            continue;
                    }
                    if ($rule ['customer_group_id'] != $gId) {
                            continue;
                    }
                    $rule_ids [] = ( int ) $rule ['rule_id'];
                }
            }
        }
		
		$rule_ids = array_unique ( $rule_ids );
		return $rule_ids;
	}
	
	/**
	 * Fetches the rewards session model
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}

}
