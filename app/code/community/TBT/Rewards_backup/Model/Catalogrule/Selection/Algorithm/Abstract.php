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
 * Abstract rule selection algorithm
 * 
 * USAGE:
 * 1. Extend this class
 * 2. Read the method signatures for all the abstract functions
 * within this class that you must implement.
 * 3. Define the model using your config.xml file like this example:
 * <rewards>
  <rule_selection_alogorithm>
  <model>rewards/catalogrule_selection_algorithm_first</model>
  </rule_selection_alogorithm>
  </rewards>
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_Rewards_Model_Catalogrule_Selection_Algorithm_Abstract {
	
	/**
	 * Rules Collection
	 *
	 * @var unknown_type
	 */
	private $_rules = null;
	/**
	 * Customer
	 *
	 * @var TBT_Rewards_Model_Customer
	 */
	private $_customer = null;
	/**
	 * Product
	 *
	 * @var TBT_Rewards_Model_Catalog_Product
	 */
	private $_product = null;
	
	/**
	 * Fetches the rule result for this algorithm
	 * 
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 *
	 */
	abstract public function getRule();
	
	/**
	 * Checks to see if the algorithm matches any rules at all
	 * @return boolean true if the algorithm matched at least one rule
	 *
	 */
	abstract public function hasRule();
	
	/**
	 * True if the algorithm should even load the model
	 *
	 * @param TBT_Rewards_Model_Catalogrule_Rule $rule
	 * @return boolean
	 */
	abstract protected function isRuleValid($rule);
	
	/**
	 * Initializes the algorithm with the data
	 * it needs to compute the result
	 *
	 * @param TBT_Rewards_Model_Customer $customer
	 * @param TBT_Rewards_Model_Catalog_Product $product
	 * @return TBT_Rewards_Model_Catalogrule_Selection_Algorithm_Abstract
	 */
	public function init($customer, $product) {
		$this->_customer = Mage::getModel('rewards/customer')->getRewardsCustomer($customer);
		$this->_product = $this->ensureProduct ( $product );
		$this->_rules = null;
		return $this;
	}
	
	/**
	 * Ensures that the given product model is a rewards catlaog product
	 *
	 * @param mixed $product
	 * @return TBT_Rewards_Model_Catalog_Product
	 */
	private function ensureProduct($product) {
		if ($product instanceof Mage_Catalog_Model_Product) {
			$product = TBT_Rewards_Model_Catalog_Product::wrap ( $product );
		}
		return $product;
	}
	
	/**
	 * Fetches all redemption rule models for this product and session
	 *
	 * @return Mage_CatalogRule_Model_Mysql4_Rule_Collection
	 */
	protected function getRules() {
		if ($this->_product == null) {
			throw new Exception ( "Redemption rule selection algorithm was not initialized." );
		}
		if ($this->_rules == null) {
			$rule_ids = $this->getRuleIds ( $this->_customer, $this->_product );
			
			// Create filter
			if (empty ( $rule_ids )) {
				$filer = array ('IS NULL' );
			} else {
				$filer = array ('IN' => $rule_ids );
			}
			
			//Attain collection
			$col = $this->getRuleCollection ()->addFieldToFilter ( 'rule_id', $filer );
			
			// Validate each rule
			$this->_rules = $col;
			foreach ( $col as &$rule ) {
				if (! $this->isRuleValid ( $rule )) {
					$this->_rules->removeItemByKey ( $rule->getId () );
				}
			}
		}
		return $this->_rules;
	}
	
	/**
	 * Fetches a generic rule collection model
	 *
	 * @return Mage_CatalogRule_Model_Mysql4_Rule_Collection
	 */
	protected function getRuleCollection() {
		return Mage::getModel ( 'rewards/catalogrule_rule' )->getCollection ();
	}
	
	/**
	 * Fetches any points redeemable options.
	 * @param TBT_Rewards_Model_Customer &$customer
	 * @param TBT_Rewards_Model_Catalog_Product &$product
	 * @return array()
	 */
	protected function getRuleIds() {
		$applicable_rules = $this->_product->getRedeemableOptions ( $this->_customer, $this->_product );
		$rule_ids = array ();
		foreach ( $applicable_rules as &$rule_entry ) {
			$rule_ids [] = $rule_entry [TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID];
		}
		return $applicable_rules;
	}
	
	/**
	 * Fetches the customer model for which this 
	 * algorithm is going to calculate it's stuff with
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	protected function getCustomer() {
		return $this->_customer;
	}
	
	/**
	 * Fetches the product model for which this 
	 * algorithm is going to calculate it's stuff on
	 *
	 * @return TBT_Rewards_Model_Catalog_Product
	 */
	protected function getProduct() {
		return $this->_product;
	}

}