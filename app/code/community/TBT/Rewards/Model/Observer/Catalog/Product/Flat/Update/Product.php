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
 * Observer Catalog Porduct Flat Update Product
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Observer_Catalog_Product_Flat_Update_Product {
	
	private $associated_rule_ids_by_product = array ();
	private $loaded_rules = array ();
	private $is_redemption_rule = array ();
	
	/* TODO WDCA - Change the classname and path of this to suit the event being observed */
	
	public function __construct() {
	
	}
	
	public function updateCatalogRulesHash($observer) {
		
		Varien_Profiler::start ( "TBT_Rewards:: Update rewards rule information on product(s)" );
		//Mage::log("Update rewards rule information on product(s)");
		//@nelkaake Friday March 12, 2010 03:48:20 PM : Was this was a product save/delete/update request?
		//@nelkaake Changed on Wednesday September 29, 2010: Some Magento stores dont parse the controller action properly so it applies all rules on save.  Fixed by checking passed product
		$target_product_id = $this->_locatedProductId($observer);
		
		//@nelkaake Changed on Wednesday September 22, 2010:
		$this->updateRulesHashForDay ( Mage::helper ( 'rewards/datetime' )->yesterday (), $target_product_id );
		$this->updateRulesHashForDay ( Mage::helper ( 'rewards/datetime' )->now (), $target_product_id );
		$this->updateRulesHashForDay ( Mage::helper ( 'rewards/datetime' )->tomorrow (), $target_product_id );
		
		Varien_Profiler::stop ( "TBT_Rewards:: Update rewards rule information on product(s)" );
		return $this;
	}

	/**
	 * Attempts to retreive a product ID from the observer or the previously dispatch observer.
	 * @param unknown_type $observer
	 */
    protected function _locatedProductId($observer) {
        
        $target_product_id = null;
        
        $event = $observer->getEvent();
        
        $action = $observer->getControllerAction();
        if ( $action ) {
            $request = $action->getRequest();
            $target_product_id = $request->getParam( "id" );
            if ( ! $target_product_id ) $target_product_id = null; //if no product id available, reset our assumption because this must be some other unrecognized request.
        }
        
        $product = $observer->getProduct();
        if ( empty( $product ) && $event instanceof Varien_Event ) {
            $product = $event->getProduct();
        }
        
        if ( $product ) {
            $target_product_id = $product->getEntityId();
            if ( ! $target_product_id ) $target_product_id = null;
        
        }
        
        if ( $target_product_id ) {
            // IF a product ID was fetched, set it into the registry
            if ( Mage::registry( 'rewards_catalogrule_apply_product_id_memory' ) ) {
                Mage::unregister( 'rewards_catalogrule_apply_product_id_memory' );
            }
            Mage::register( 'rewards_catalogrule_apply_product_id_memory', $target_product_id );
        } else {
            // IF a product ID was NOT fetched, attempt to get it from the registry
            if ( Mage::registry( 'rewards_catalogrule_apply_product_id_memory' ) ) {
                $target_product_id = Mage::registry( 'rewards_catalogrule_apply_product_id_memory' );
                // After pulling it from the registry, remove it from the registry so the next immediate action does not recall this.
                Mage::unregister( 'rewards_catalogrule_apply_product_id_memory' );
            }
        }
        
        return $target_product_id;
    }
	
	
	//@nelkaake Added on Wednesday September 22, 2010:
	public function updateRulesHashForDay($now, $target_product_id = null) {
		
		$write = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
		$catalogrule_price_table = Mage::getConfig ()->getTablePrefix () . "catalogrule_product_price";
		$validator = $this->_getValidator ();
		
		$read = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_read' );
		$select = $read->select ()->from ( $catalogrule_price_table, array ('product_id', 'customer_group_id', 'website_id' ) )->where ( 'rule_date = ? ', $now );
		
		//@nelkaake Friday March 12, 2010 03:48:20 PM : if this was a product save/delete/update request, only update that product.
		if ($target_product_id)
			$select->where ( 'product_id = ?', $target_product_id );
		
		$collection = $read->fetchAll ( $select );
		foreach ( $collection as $row ) {
			//Varien_Profiler::start("TBT_Rewards:: Fetch all prev applied rules");
			$product_id = $row ['product_id'];
			$customer_group_id = $row ['customer_group_id'];
			$website_id = $row ['website_id'];
			
			$associated_rule_ids = $this->_getAssociatedRules ( $product_id, $website_id, $customer_group_id, $now );
			if (! $associated_rule_ids) {
				continue;
			}
			$row_hash = array ();
			foreach ( $associated_rule_ids as $rule_id ) {
				$rule = $this->_getCatalogRule ( $rule_id );
				if (! $rule) {
					continue;
				}
				
				if ($rule->isRedemptionRule ()) {
					if ($validator->isValid ( $rule, $customer_group_id, $website_id )) {
						$effect = $rule->getEffect ();
						if (empty ( $effect )) {
							continue;
						}
						
						$item_rule = $rule->getHashEntry ();
						$row_hash [] = $item_rule;
						
						continue;
					}
				}
			}
			//Varien_Profiler::stop("TBT_Rewards:: Calc all prev applied rules");
			//Varien_Profiler::start("TBT_Rewards:: Write updates of rewards rule information on product(s)");
			$updateData = array ("rules_hash" => base64_encode ( json_encode ( $row_hash ) ) );
			$updateWhere = array ("`product_id`='{$product_id}' ", "`customer_group_id`='{$customer_group_id}' ", "`rule_date`='{$now}'", "`website_id`='{$website_id}'" );
			
			try {
				$write->beginTransaction ();
				$write->update ( $catalogrule_price_table, $updateData, $updateWhere );
				$write->commit ();
			} catch ( Exception $e ) {
				Mage::logException ( $e );
				$write->rollback ();
			}
		
		//Varien_Profiler::stop("TBT_Rewards:: Write updates of rewards rule information on product(s)");
		}
	}
	
	/**
	 * Returns a rule and makes sure rules are only ever loaded once
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	protected function _getCatalogRule($rule_id) {
		if (isset ( $this->loaded_rules [$rule_id] )) {
			$rule = &$this->loaded_rules [$rule_id];
		} else {
			$rule = Mage::getModel ( 'rewards/catalogrule_rule' )->load ( $rule_id );
			$this->loaded_rules [$rule_id] = $rule;
		}
		return $rule;
	}
	
	/**
	 * Fetches the associated rule ids for a product and makes sure that
	 * they are not loaded more than once.
	 *
	 * @param integer $product_id
	 * @return array()
	 */
	protected function _getAssociatedRules($product_id, $wId = null, $gId = null, $date = null) {
		//@nelkaake Changed on Sunday August 15, 2010:
		if ($this->_hasARI ( $product_id, $wId, $gId )) {
			$associated_rule_ids = $this->_getARI ( $product_id, $wId, $gId );
		} else {
			$associated_rule_ids = Mage::getModel ( 'rewards/catalog_product' )->setId ( $product_id )->getCatalogRuleIds ( $wId, $gId, $date );
			$assoc_redem_rids = array ();
			foreach ( $associated_rule_ids as &$rule_id ) {
				if ($this->_getCatalogRule ( $rule_id )->isRedemptionRule ()) {
					$assoc_redem_rids [] = $rule_id;
				}
			}
			$associated_rule_ids = $assoc_redem_rids;
			//@nelkaake Changed on Sunday August 15, 2010:
			$this->_setARI ( $product_id, $wId, $gId, $associated_rule_ids );
		}
		return $associated_rule_ids;
	}
	
	/**
	 * Creates and returns a token representing unique catalog case	
	 * @nelkaake Added on Sunday August 15, 2010: 	
	 * @param $pId product id	
	 * @param $wId website id 	 
	 * @param $gId group id 	
	 * @return string token representing unique catalog case
	 * 
	 */
	protected function _getHashToken($pId, $wId, $gId) {
		if ($wId == null)
			$wId = "";
		if ($gId == null)
			$gId = "";
		return "{$pId}|{$wId}|{$gId}";
	}
	
	/**
	 * Fetches a local representation of the catalog case entry
	 * @nelkaake Added on Sunday August 15, 2010: 	
	 * @param $pId product id	
	 * @param $wId website id 	 
	 * @param $gId group id 	
	 * @return array catalog case rule entry array
	 * 
	 */
	protected function _getARI($pId, $wId, $gId) {
		$token = $this->_getHashToken ( $pId, $wId, $gId );
		if ($this->_hasARI ( $pId, $wId, $gId )) {
			$ret = &$this->associated_rule_ids_by_product [$token];
		} else {
			$ret = null;
		}
		return $ret;
	}
	
	/**
	 * Checks to see if a local representation of the catalog case entry exists
	 * @nelkaake Added on Sunday August 15, 2010: 	
	 * @param $pId product id	
	 * @param $wId website id 	 
	 * @param $gId group id 	
	 * @return boolean
	 * 
	 */
	protected function _hasARI($pId, $wId, $gId) {
		$token = $this->_getHashToken ( $pId, $wId, $gId );
		$ret = isset ( $this->associated_rule_ids_by_product [$token] );
		return $ret;
	}
	
	/**
	 * Sets the local representation of the catalog case entry
	 * @nelkaake Added on Sunday August 15, 2010: 	
	 * @param $pId product id	
	 * @param $wId website id 	 
	 * @param $gId group id 	
	 * @return TBT_Rewards_Model_Observer_Catalog_Product_Flat_Update_Product $this
	 * 
	 */
	private function _setARI($pId, $wId, $gId, $ARI) {
		$token = $this->_getHashToken ( $pId, $wId, $gId );
		$this->associated_rule_ids_by_product [$token] = $ARI;
		return $this;
	}
	
	/**
	 * Fetches the rewards catalogrule valiator
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Validator
	 */
	private function _getValidator() {
		return Mage::getSingleton ( 'rewards/catalogrule_validator' );
	}

}