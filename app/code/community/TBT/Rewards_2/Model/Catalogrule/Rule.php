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
 * Catalog Rule Rule
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Catalogrule_Rule extends Mage_CatalogRule_Model_Rule implements TBT_Rewards_Model_Migration_Importable {
	const POINTS_CURRENCY_ID = 'points_currency_id';
	const POINTS_AMT = 'points_amt';
	const POINTS_EFFECT = 'effect';
	const POINTS_RULE_ID = 'rule_id';
	const POINTS_APPLICABLE_QTY = 'applicable_qty';
	const POINTS_USES = 'uses';
	const POINTS_INST_ID = 'redemption_inst_id';
	
	const CACHE_TAG = 'rewards_catalogrule';
	protected $_cacheTag = 'rewards_catalogrule';
	
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'rewards/catalogrule_rule' );
	}
	
	/**
	 * Clear cache related with rule_id
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	public function cleanCache() {
		Mage::app ()->cleanCache ( self::CACHE_TAG . '_' . $this->getId () );
                //TODO: review this call to cleanCache... might be invalid because keys are more complex.
                //$key = "rewards_product_view_points_{$nameInLayout}_{$blockType}_{$product_id}_{$website_id}_{$customer_group_id}";
                //$key = "rewards_product_predictpoints_{$nameInLayout}_{$blockType}_{$product_id}_{$website_id}_{$customer_group_id}";                
		Mage::app ()->cleanCache ( 'rewards_product_predictpoints' );
		Mage::app ()->cleanCache ( 'rewards_product_view_points' );
		return $this;
	}
	
	/**
	 * Clear chache related with product
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	protected function _beforeDelete() {
		$this->cleanCache ();
		return parent::_beforeDelete ();
	}
	
	/**
	 * Check clean cache before save
	 */
	protected function _beforeSave() {
		$this->cleanCache ();
		return parent::_beforeSave ();
	}
	
	/**
	 * Change label for current store
	 */
	protected function _afterLoad() {
		$store = Mage::app ()->getStore ();
		// Not admin
		if ($store->getId () != 0) {
			// Save original name
			$this->setOriginalName ( $this->getName () );
			$label = Mage::getModel ( 'rewards/catalogrule_label' )->getRuleLabelsAsArray ( $this );
			// Load the label for this store
			if (isset ( $label [$store->getId ()] )) {
				$this->setName ( $label [$store->getId ()] );
			} elseif (isset ( $label [0] )) {
				$this->setName ( $label [0] );
			}
		}
		return parent::_afterLoad ();
	}
	
	public function getResourceCollection() {
		return Mage::getResourceModel ( 'rewards/catalogrule_rule_collection' );
	}
	
	/**
	 * Returns true if this a redemption rule
	 *
	 * @return boolean
	 */
	public function isRedemptionRule() {
		$ruleActionSing = Mage::getSingleton ( 'rewards/catalogrule_actions' );
		return $ruleActionSing->isRedemptionAction ( $this->getPointsAction () );
	}
	
	/**
	 * Returns true if this a distribution rule
	 *
	 * @return boolean
	 */
	public function isDistributionRule() {
		$ruleActionSing = Mage::getSingleton ( 'rewards/catalogrule_actions' );
		return $ruleActionSing->isDistributionAction ( $this->getPointsAction () );
	}
	
	/**
	 * Returns true if this a redemption rule
	 *
	 * @return boolean
	 */
	public function isRedemptionAction() {
		return $this->isRedemptionRule ();
	}
	
	/**
	 * Returns true if this a distribution rule
	 *
	 * @return boolean
	 */
	public function isDistributionAction() {
		return $this->isDistributionRule ();
	}
	
	/**
	 * Returns the rule time id
	 *
	 * @return int
	 */
	public function getRuleTypeId() {
		$ruleActionSing = Mage::getSingleton ( 'rewards/catalogrule_actions' );
		return $ruleActionSing->getRuleTypeId ( $this->getPointsAction () );
	}
	
	/**
	 * Fetches a list of all CATALOGRULE rules that
	 * have a points action
	 *
	 * @return Collection
	 */
	public function getPointsRuleIds() {
		$col = $this->getCollection ()->addFieldToFilter ( "points_action", array ('neq' => '' ) );
		return $col;
	}
	
	/**
	 * Checks to see if the customer group id is applicable to this rule
	 * TODO WDCA: any way to optimize this array_search? perhaps a map?
	 * @param integer $customer_group_id
	 * @return boolean	: true if the group id is applicable to this rule, false otherwise
	 */
	public function isApplicableToCustomerGroup($customer_group_id) {
		return array_search ( $customer_group_id, $this->getCustomerGroupIds () ) !== false;
	}
	
	/**
	 * Checks to see if the website id is applicable to this rule
	 * TODO WDCA: any way to optimize this array_search? perhaps a map?
	 * @param integer $website_id
	 * @return boolean	: true if the website is applicable to this rule, false otherwise
	 */
	public function isApplicableToWebsite($website_id) {
		return array_search ( $website_id, $this->getWebsiteIds () ) !== false;
	}
	
	/**
	 * Generates and returns the effect code for this catalogrule 
	 *
	 * @return string
	 */
	public function getEffect() {
		if ($this->getPointsCatalogruleSimpleAction () == 'by_percent') {
			$effect = '-' . $this->getPointsCatalogruleDiscountAmount () . '%';
		} else if ($this->getPointsCatalogruleSimpleAction () == 'by_fixed') {
			$effect = '-' . $this->getPointsCatalogruleDiscountAmount ();
		} else if ($this->getPointsCatalogruleSimpleAction () == 'to_percent') {
			$effect = $this->getPointsCatalogruleDiscountAmount () . '%';
		} else if ($this->getPointsCatalogruleSimpleAction () == 'to_fixed') {
			$effect = $this->getPointsCatalogruleDiscountAmount ();
		} else {
			$effect = null;
		}
		return $effect;
	}
	
	/**
	 * 
	 * @param Mage_Core_Model_Store $store
	 * @param TBT_Rewards_Model_Catalog_Product $product
	 * @param TBT_Rewards_Model_Customer $customer
	 * @param float $redeemable_price
	 * 
	 */
	public function getPointSliderSettings($store, $product, $customer, $price) {
		
		try {
			if (! $this->getId ())
				throw new Exception ( "Bad rule ID" );
			if (! $product->getId ())
				throw new Exception ( "Product ID provided does not exist" );
			
			$redeem_price_per_usage = $price - Mage::helper ( 'rewards' )->priceAdjuster ( $price, $this->getEffect (), false, false );
			
			$redeemable_price = $price;
			
			// limit $redeemable_price to the redeem_percentage_price limit
			if ($this ['points_max_redeem_percentage_price'])
				$redeemable_price = $price * (min ( $this ['points_max_redeem_percentage_price'], 100 ) / 100);
			
		// find the number of redeem_usages needed to get the full product free
			$max_redeem_usage = ($redeemable_price / $redeem_price_per_usage);
			
			// HACK: to keep none hole usage percent
			$max_redeem_usage_part = $max_redeem_usage;
			if ($max_redeem_usage > ( int ) ($max_redeem_usage))
				$max_redeem_usage = ( int ) ($max_redeem_usage) + 1;
			
		// set of max points that can be used
			$points_max_set = array ();
			$points_max_set [] = $this ['points_amount'] * $max_redeem_usage;
			if ($this ['points_max_qty'] > 0)
				$points_max_set [] = $this ['points_max_qty'];
			if ($customer != null && Mage::getSingleton ( 'rewards/session' )->isCustomerLoggedIn ())
				$points_max_set [] = $customer->getUsablePointsBalance ( $this->getPointsCurrencyId () );
			if (Mage::helper ( 'rewards/config' )->canUseRedemptionsIfNotLoggedIn () == false && Mage::getSingleton ( 'rewards/session' )->isCustomerLoggedIn () == false)
				$points_max_set [] = 0;
			
		// set of max usage that can be used
			$usage_max_set = array ();
			$usage_max_set [] = $max_redeem_usage;
			$usage_max_set [] = ( int ) (min ( $points_max_set ) / $this ['points_amount']);
			if ($this ['points_uses_per_product'] > 0)
				$usage_max_set [] = $this ['points_uses_per_product'];
			
		// set of max redeem price
			$redeem_price_max_set = array ();
			$redeem_price_max_set [] = $redeemable_price;
			$redeem_price_max_set [] = max ( $usage_max_set ) * $redeem_price_per_usage;
			
			// --------- min
			

			$usage_min_set = array ();
			$usage_min_set [] = 0;
			
			$rRule = array ();
			
			// HACK: to keep none hole usage percent
			$rRule ['on_price'] = $price;
			$rRule ['max_redeem_usage_part'] = $max_redeem_usage_part;
			
			// TODO - admin option to change redeemer type
			$rRule ['redeam_type'] = 'slider';
			if ($this ['points_uses_per_product'] == 1)
				$rRule ['redeam_type'] = 'once_per_product';
			
		//$rRule['effect_type'] = $rule->getPointsCatalogruleSimpleAction();
			//$rRule['effect'] = $rule->getEffect();
			//$rRule['can_use_rule'] = Mage::getSingleton('rewards/session')->isCustomerLoggedIn() || Mage::helper('rewards/config')->canUseRedemptionsIfNotLoggedIn();
			$rRule ['points_per_usage'] = $this ['points_amount'];
			$rRule ['points_spend_min'] = 0;
			$rRule ['points_spend_max'] = min ( $points_max_set );
			$rRule ['usage_min'] = max ( $usage_min_set ); // mostly 0
			$rRule ['usage_max'] = min ( $usage_max_set ); // the min of all the max
			$rRule ['redeem_price_per_usage'] = $redeem_price_per_usage;
			$rRule ['redeem_price_min'] = 0;
			$rRule ['redeem_price_max'] = min ( $redeem_price_max_set );
			$rRule ['rule_id'] = $this->getId ();
			$rRule ['product_id'] = $product->getId ();
			$rRule ['currency_id'] = $this->getPointsCurrencyId ();
			$rRule ['name'] = $this->getName ();
			$rRule ['currency_id'] = $this->getPointsCurrencyId (); // [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID];
			$rRule ['points'] = Mage::helper ( 'rewards/currency' )->formatCurrency ( $rRule ['points_per_usage'], $rRule ['currency_id'] );
			//  $rRule['points_caption'] = Mage::helper('rewards')->getPointsString(array(
			//            $rRule['currency_id'] => $rRule['points_per_usage']
			//      ));
			

			return $rRule;
		} catch ( Exception $e ) {
			// TODO FIX LOG ERROR
			Mage::log ( $e->getMessage () );
			//die("Error: " . $e->getMessage());
			return null;
		}
	}
	
	/**
	 * Generates and returns a hash that contains:
	 * - the Points amount
	 * - the points currency id
	 * - the rule id
	 * - applicable quantity
	 * - effect
	 *
	 * @return array : a map of the above mentioned fields
	 */
	public function getHashEntry($applicable_quantity = 0) {
		$item_rule = array (self::POINTS_AMT => $this->getPointsAmount (), self::POINTS_CURRENCY_ID => $this->getPointsCurrencyId (), self::POINTS_RULE_ID => $this->getId (), self::POINTS_APPLICABLE_QTY => $applicable_quantity, self::POINTS_EFFECT => $this->getEffect () );
		return $item_rule;
	}
	
	/**
	 * Forcefully Save object data even if ID does not exist
	 * Used for migrating data and ST campaigns.     
	 *
	 * @return Mage_Core_Model_Abstract
	 */
	public function saveWithId() {
		$real_id = $this->getId ();
		$exists = Mage::getModel ( $this->_resourceName )->setId ( null )->load ( $real_id )->getId ();
		
		if (! $exists) {
			$this->setId ( null );
		}
		
		$this->save ();
		
		if (! $exists) {
			$write = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
			$write->update ( $this->_getResource ()->getMainTable (), array ($this->_getResource ()->getIdFieldName () => $real_id ), array ("`{$this->_getResource()->getIdFieldName()}` = {$this->getId()}" ) );
			$write->commit ();
		}
		
		return $this;
	}
	
	/**
	 * Returns true if this a distribution rule
	 *
	 * @return boolean
	 */
	public function isPointsRule() {
		return $this->isDistributionRule () || $this->isRedemptionRule ();
	}

}