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
 * Redeem
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Redeem extends Mage_Core_Model_Abstract {
	const POINTS_RULE_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID;
	const POINTS_APPLICABLE_QTY = TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY;
	const POINTS_EFFECT = TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT;
	const POINTS_USES = TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES;
	const SALES_FLAT_QUOTE_ITEM = "sales_flat_quote_item";
	/**
	 * Tax calculation model
	 *
	 * @var Mage_Tax_Model_Calculation
	 */
	protected $_calculator = null;
	
	protected function _construct() {
		$this->_calculator = Mage::getSingleton ( 'tax/calculation' );
		return $this;
	}
	
	/**
	 * @deprecated Use refactorRedemptions($items, $doSave) instead
	 */
	public function addCatalogRedemptionsToItem($item, $rule_id_list, $customer) {
		return false;
	}
	
	/**
	 * Removes all applicable rules to the item's rule hash.
	 * Returns false if no changes were made.
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @param array $rule_id_list
	 * @param integer $inst_id redemption instance id (this comes out of the item redemptions hash)
	 * @return boolean
	 */
	public function removeCatalogRedemptionsFromItem(&$item, $rule_id_list, $inst_id = 0) {
		//Check to make sure we can load the redeem points hash alright
		if (! $item->getRedeemedPointsHash ()) {
			throw new Exception ( $this->__ ( "Unable to load the redeem points hash" ) );
		}
		$catalog_redemptions = Mage::helper ( 'rewards' )->unhashIt ( $item->getRedeemedPointsHash () );
		foreach ( $catalog_redemptions as $key => $redemption ) {
			$catalog_redemptions [$key] = ( array ) $redemption;
		}
		
		$doSave = false;
		
		foreach ( $rule_id_list as $rule_id ) {
			$rule = Mage::getModel ( 'rewards/catalogrule_rule' )->load ( $rule_id );
			$foundRuleIdIndex = false;
			foreach ( $catalog_redemptions as $index => $redemption ) {
				$rule_id_is_same = ($redemption [TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID] == $rule_id);
				$inst_id_is_same = (($inst_id == 0) ? true : ($redemption [TBT_Rewards_Model_Catalogrule_Rule::POINTS_INST_ID] == $inst_id));
				if ($rule_id_is_same && $inst_id_is_same) {
					$foundRuleIdIndex = $index;
				}
			}
			
			if ($foundRuleIdIndex === false) {
				throw new Exception ( "The rule entitled '" . $rule->getName () . "' is not applied to this product." );
			} else {
				unset ( $catalog_redemptions [$foundRuleIdIndex] );
				$item->setRedeemedPointsHash ( Mage::helper ( 'rewards' )->hashIt ( $catalog_redemptions ) );
				$doSave = true;
			}
		}
		
		if ($doSave) {
			$item->save ();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Retenders the items listed in the item list
	 *
	 * @param array(Mage_Sales_Model_Quote_Item) $items
	 */
	public function refactorRedemptions($items, $doSave = true) {
		if (! is_array ( $items )) {
			$items = array ($items );
		}
		
		foreach ( $items as $item ) {
			$this->refactorRedemption ( $item, $doSave );
			$this->refactorItemTax ( $item );
		}
		$this->refactorGrandTotal ( $items );
	}
	
	/**
	 * If we need to, recalculates the tax for an item model.
	 * 
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function refactorItemTax(&$item) {
		//@nelkaake -a 13/01/11: Fixes a bug that occurs in Magento 1.3.1 where the cart is calculating
		// tax based on the amount before discounts instead of after discounts as configured.
		if (Mage::helper ( 'rewards/version' )->isMage ( '1.3.1' )) {
			if (Mage::helper ( 'tax' )->priceIncludesTax ()) {
				if (Mage::helper ( 'tax' )->applyTaxAfterDiscount ()) {
					return $this;
				}
			}
		}
		
		/*if($item->getQuote())
        	$item->calcTaxAmount();*/
		return $this;
	}
	
	/**
	 * Retenders the item's redemption rules and final row total
	 * @nelkaake Friday March 26, 2010 12:36:50 PM : Changed to protected vs private
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function refactorRedemption(&$item, $doSave = true) {
		// Write to the database the new item row information
		$r = $this->getUpdatedRedemptionData ( $item );
		$row_total = $r ['row_total'];
		$row_total_incl_tax = $r ['row_total_incl_tax'];
		//$row_total = $r['row_total_incl_tax'];
		$redems = $r ['redemptions_data'];
		
		//@nelkaake -a 3/03/11: Failsafe to make sure the total never drops below zero
		if ($row_total < 0)
			$row_total = 0;
		if ($row_total_incl_tax < 0)
			$row_total_incl_tax = 0;
		
		$this->resetItemDiscounts ( $item );
		
		$item->setRowTotal ( $row_total );
		$item->setBaseRowTotal ( Mage::helper ( 'rewards/price' )->getReversedCurrencyPrice ( $row_total ) );
		
		$item->setRowTotalInclTax ( $row_total_incl_tax );
		$item->setBaseRowTotalInclTax ( Mage::helper ( 'rewards/price' )->getReversedCurrencyPrice ( $row_total_incl_tax ) );
		
		$regular_discount = $item->getBaseDiscountAmount ();
		if (empty ( $regular_discount )) {
			$item->setRowTotalWithDiscount ( $item->getRowTotal () );
			$item->setBaseRowTotalWithDiscount ( $item->getBaseRowTotal () );
		}
		
		//@nelkaake -a 16/11/10: 
		$this->_calcTaxAmounts ( $item );
		if ($doSave) {
			$item->save ();
		}
	}
	
	/**                                    
	 * Calculates tax amounts for the row item using $this->_calculator
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return $this
	 */
	protected function _calcTaxAmounts(&$item) {
		//@nelkaake -a 16/11/10: Calculator only works in magento 1.4 and up.
		if (! Mage::helper ( 'rewards/version' )->isMageVersionAtLeast ( '1.4.2' )) {
			return $this;
		}
		
		// @nelkaake -a 16/11/10: Tax calculation methods
		$tax = $this->_calculator->calcTaxAmount ( $item->getRowTotal (), $item->getTaxPercent (), false, false );
		$baseTax = $this->_calculator->calcTaxAmount ( $item->getBaseRowTotal (), $item->getTaxPercent (), false, false );
		
		$item->setTaxAmount ( $tax );
		$item->setBaseTaxAmount ( $baseTax );
		
		$item->setTaxableAmount ( $item->getRowTotal () );
		$item->setBaseTaxableAmount ( $item->getBaseRowTotal () );
		
		//@nelkaake -a 26/01/11: We don't set this so that we can later use the row total including tax to find out 
		// how much of the order was discounted due to catalog redemption rules.

		$item->setRowTotalInclTax ( $item->getRowTotal () + $tax );
		$item->setBaseRowTotalInclTax ( $item->getBaseRowTotal () + $baseTax );
		
		
		return $this;
	}
	
	/**
	 * Returns the item's updated row total after redemptions
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return float
	 */
	public function getRowTotalAfterRedemptions($item) {
		$new_red_data = $this->getUpdatedRedemptionData ( $item );
		$row_total = $new_red_data ['row_total'];
		return $row_total;
	}
	/**
	 * Returns the item's updated row total after redemptions
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return float
	 */
	public function getRowTotalAfterRedemptionsInclTax($item) {
		$new_red_data = $this->getUpdatedRedemptionData ( $item, true );
		$row_total = $new_red_data ['row_total_incl_tax'];
		return $row_total;
	}
	
	/**
	 * Returns the item's updated redemption data as a hash
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return string a hash of the new item redemptions
	 */
	public function getUpdatedRedemptionsHash($item) {
		$new_red_data = $this->getUpdatedRedemptions ( $item );
		$redemptions_data = Mage::helper ( 'rewards' )->hashIt ( $new_red_data );
		return $redemptions_data;
	}
	
	/**
	 * Returns the item's updated redemption data
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return array a map of the new item redemptions
	 */
	public function getUpdatedRedemptions($item) {
		$new_red_data = $this->getUpdatedRedemptionData ( $item );
		$redemptions_data = $new_red_data ['redemptions_data'];
		return $redemptions_data;
	}
	
	/**
	 * Renders the item's redemption rules and final row total and returns it.
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return array a map of the new item redemption data: 
	 * array('redemptions_data'=>{...}, 'row_total'=>float)
	 */
	protected function getUpdatedRedemptionData($item, $do_incl_tax = true) {
		// Step 1: Create a map of usability for all applied redemptions
		//echo "$item->getRedeemedPointsHash()";
		$redeemed_points = Mage::helper ( 'rewards' )->unhashIt ( $item->getRedeemedPointsHash () );
		
		// Prepare data from item and initalize counters
		if ($item->getQuote ())
			$store_currency = round ( $item->getQuote ()->getStoreToQuoteRate (), 4 );
		if ($item->getOrder ())
			$store_currency = round ( $item->getOrder ()->getStoreToQuoteRate (), 4 );
		
		if ($item->hasCustomPrice ()) {
			$product_price = ( float ) $item->getCustomPrice () * $store_currency;
		} else {
			//@nelkaake -a 17/02/11: We need to use our own calculation because the one that was set by the 
			// rest of the Magento system is rounded.
			if (Mage::helper ( 'tax' )->priceIncludesTax () && $item->getPriceInclTax ()) {
				$product_price = $item->getPriceInclTax () / (1 + $item->getTaxPercent () / 100);
			} else {
				$product_price = ( float ) $item->getPrice () * $store_currency;
			}
		
		}
		if ($item->getParentItem () || sizeof ( $redeemed_points ) == 0) {
			return array ('redemptions_data' => array (), 'row_total_incl_tax' => $item->getRowTotalInclTax (), 'row_total' => $item->getRowTotal () );
		}
		
		$total_qty = $item->getQty ();
		$total_qty_redeemed = 0.0000;
		$row_total = 0.0000;
		$new_redeemed_points = array ();
		$ret = array ();
		
		// Loop through and apply all our rules.
		foreach ( $redeemed_points as $key => &$redemption_instance ) {
			$redemption_instance = ( array ) $redemption_instance;
			$applic_qty = $redemption_instance [self::POINTS_APPLICABLE_QTY];
			$rule_id = $redemption_instance [self::POINTS_RULE_ID];
			$effect = $redemption_instance [self::POINTS_EFFECT];
			$uses = isset ( $redemption_instance [self::POINTS_USES] ) ? ( int ) $redemption_instance [self::POINTS_USES] : 1;
			$rule = Mage::helper ( 'rewards/rule' )->getCatalogRule ( $rule_id );
			
			// If a rule was turned off at some point in the back-end it should be removed and not calculated in the cart anymore.
			if (! $rule->getIsActive ()) {
				$this->removeCatalogRedemptionsFromItem ( $item, array ($rule_id ) );
				$effect = "";
			}
			
			$total_qty_remain = $total_qty - $total_qty_redeemed;
			if ($total_qty_remain > 0) {
				if ($total_qty_remain < $applic_qty) {
					$applic_qty = $total_qty_remain;
					$redemption_instance [TBT_Rewards_Model_Redeem::POINTS_APPLICABLE_QTY] = $applic_qty;
				}
				
				$price_after_redem = $this->getPriceAfterEffect ( $product_price, $effect, $item );
				
				$row_total += $applic_qty * ( float ) $price_after_redem;
				$total_qty_redeemed += $applic_qty;
				$new_redeemed_points [] = $redemption_instance;
			} else {
				$redemption_instance [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY] = 0;
				$redemption_instance [TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES] = 1; // used once by default
				unset ( $redeemed_points [$key] );
			}
		}
		
		$ret ['redemptions_data'] = $new_redeemed_points;
		
		// Add in the left over products that perhaps weren't affected by qty adjustment.
		$total_qty_remain = ($total_qty - $total_qty_redeemed);
		if ($total_qty_remain < 0) {
			$total_qty_remain = 0;
			$total_qty_redeemed = $total_qty;
		
		//throw new Exception("Redemption rules may be overlapping.  Please notify the store administrator of this error.");
		}
		$row_total += $total_qty_remain * ( float ) $product_price;
		
		$ret ['row_total'] = $row_total;
		$ret ['row_total_incl_tax'] = $row_total * (1 + $item->getTaxPercent () / 100);
		
		return $ret;
	}
	
	/**
	 * Returns a product price after the given effect has occured.
	 * @see also TBT_Rewards_Helper_Data::priceAdjuster
	 * 
	 * @param decimal $product_price
	 * @param mixed $effect
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @param boolean  $calc_incl_tax_if_applic if applicable, should I calculate the price including tax amount?
	 */
	public function getPriceAfterEffect($product_price, $effect, $item, $calc_incl_tax_if_applic = true) {
		//@nelkaake -a 17/02/11: If it's order mode we don't want to be pulling tax rates from anywhere.
		if (Mage::helper ( 'tax' )->priceIncludesTax () && $calc_incl_tax_if_applic) {
			$product_price = $product_price * (1 + $item->getTaxPercent () / 100);
		}
		
		$price_after_redem = Mage::helper ( 'rewards' )->priceAdjuster ( $product_price, $effect );
		
		if (Mage::helper ( 'tax' )->priceIncludesTax () && $calc_incl_tax_if_applic) {
			$price_after_redem = $price_after_redem / (1 + $item->getTaxPercent () / 100);
		}
		
		return $price_after_redem;
	}
	
	public function refactorGrandTotal($items) {
		$acc_diff = 0;
		
		if (! is_array ( $items )) {
			$items = array ($items );
		}
		
		foreach ( $items as $item ) {
			// Tracking the differences in applying Catalog rules        
			$acc_diff += $item->getRowTotalBeforeRedemptions () - $item->getRowTotal ();
		}
	}
	
	/**
	 * Calculates the resulting total discount amount due to Sweet Tooth catalog redemption
	 * rule discounts.
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function getTotalCatalogDiscount($item) {
		
	    // Reset if OSC to fix a bug #1124
	    $this->_resetTotalsIfOSC($item);
				
		if (Mage::helper ( 'tax' )->priceIncludesTax ()) {
			$row_total = $item->getRowTotalInclTax ();
			$row_total_after = $this->getRowTotalAfterRedemptionsInclTax ( $item );
		} else {
			$row_total = $item->getRowTotal ();
			$row_total_after = $this->getRowTotalAfterRedemptions ( $item );
		}
		$row_total_after = $row_total_after < 0 ? 0 : $row_total_after;
		
		$catalog_discount = $row_total_after - $row_total;
		return $catalog_discount;
	}

    /**
     * Make sure we're calculating the discount based on the original row_total, not one which we previously modified.
     * For now looks like we only need to do this for OneStepCheckout, so we'll address it there only.
     * 
     * @param Mage_Sales_Model_Quote_Item $item
     * @return $this;
     */
    protected function _resetTotalsIfOSC($item) {
        
        if ( ! Mage::getConfig()->getModuleConfig( 'Idev_OneStepCheckout' ) ) {
            return $this;
        }
        if ( ! Mage::getConfig()->getModuleConfig( 'Idev_OneStepCheckout' )->is( 'active', 'true' ) ) {
            return $this;
        }
        
        $this->resetRowTotals( $item );
        
        return $this;
    }
	
	/**
	 * 
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function resetItemDiscounts($item) {
		if (! $item)
			return $this;
		
		if ($item->getRowTotalBeforeRedemptions () == 0) {
			$item->setRowTotalBeforeRedemptions ( $item->getRowTotal () );
			$item->setRowTotalBeforeRedemptionsInclTax ( $item->getRowTotalInclTax () );
		} elseif ($item->getRowTotalBeforeRedemptions () < $item->getRowTotal ()) {
			$item->setRowTotal ( $item->getRowTotalBeforeRedemptions () );
			$item->setRowTotalInclTax ( $item->getRowTotalBeforeRedemptionsInclTax () );
		} 
                else {
			// do nothing
		}
		
		if (!Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' )) {
			// only happens in Magento 1.3
			$rowTotalInclTax = $item->getRowTotalBeforeRedemptions () * (1 + ($item->getTaxPercent () / 100));
			$item->setRowTotalBeforeRedemptionsInclTax ($rowTotalInclTax);			
		}		
		
		return $this;
	}
	
	/**
	 * Resets the RowTotalAfterRedemptions value for the item.
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function resetBeforeDiscount($item) {
		if (! $item)
			return $this;
		
		$item->setRowTotalBeforeRedemptions ( null );
		$item->setRowTotalBeforeRedemptionsInclTax ( null );
		
		return $this;
	}
	
	/**
	* Resets the following item attributes by calling $item->calcRowTotal followed by _calcTaxAmounts():
	* 	row_total,
	*	base_row_total,
	*	row_total_incl_tax,
	*	base_row_total_incl_tax,
	*	tax_amount,
	*	base_tax_amount,	
	*	taxable_amount,
	*	base_taxable_amount	 
	* @param Mage_Sales_Model_Quote_Item $item
	*/
	public function resetRowTotals($item){
		$item->calcRowTotal();
		$this->_calcTaxAmounts($item);		

		return $this;
	}	
}
