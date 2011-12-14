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
 * Sales quote model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Sales_Quote extends Mage_Sales_Model_Quote {
	/**
	 * Loads in a quote and returns a points quote
	 * This is just for developers using eclipse (for code assist)	 
	 *
	 * @param Mage_Sales_Model_Quote $product
	 * @return TBT_Rewards_Model_Sales_Quote
	 */
	public static function wrap(Mage_Sales_Model_Quote &$quote) {
		return $quote;
	}
	
	public function hasAnyAppliedCatalogRedemptions() {
		foreach ( $this->getAllItems () as $item ) {
			if ($this->_hasAppliedCatalogRedemptions ( $item )) {
				return true;
			}
		}
		return false;
	}
	
	public function hasAnyAppliedCatalogDistributions() {
		foreach ( $this->getAllItems () as $item ) {
			if ($this->_hasAppliedCatalogDistributions ( $item )) {
				return true;
			}
		}
		return false;
	}
	
	public function hasAnyAppliedCatalogRules() {
		return $this->hasAnyAppliedCatalogRedemptions () || $this->hasAnyAppliedCatalogDistributions ();
	}
	/**
	 * Returns true if the item has catalog redemptions within it.
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function _hasAppliedCatalogRedemptions($item) {
		$redeemed_point_totals = $item->getRedeemedPointsHash ();
		$redeemed_point_totals = Mage::helper ( 'rewards' )->unhashIt ( $redeemed_point_totals );
		$hash_is_empty = empty ( $redeemed_point_totals );
		return ! $hash_is_empty;
	}
	/**
	 * Returns true if the item has catalog distributions within it.
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function _hasAppliedCatalogDistributions($item) {
		$point_totals = $item->getEarnedPointsHash ();
		$point_totals = Mage::helper ( 'rewards' )->unhashIt ( $point_totals );
		$hash_is_empty = empty ( $point_totals );
		return ! $hash_is_empty;
	}
	
	/**
	 * True if the quote object has any applied redemptions
	 *
	 * @param TBT_Rewards_Model_Quote $quote
	 * @return boolean
	 */
	public function _hasAppliedCartRedemptions($quote = null) {
		if ($quote == null) {
			$quote = &$this;
		}
		$redeem_rules = explode ( ',', $quote->getAppliedRedemptions () );
		if (empty ( $redeem_rules )) {
			return false;
		}
		foreach ( $redeem_rules as $rr ) {
			if (! empty ( $rr )) {
				//@nelkaake Thursday April 22, 2010 02:28:43 AM : check for variable usable rules
				$rr_model = Mage::helper ( 'rewards/rule' )->getSalesRule ( $rr );
				if ($rr_model->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
					if (Mage::getSingleton ( 'rewards/session' )->getPointsSpending () > 0) {
						return true;
					} else {
						return false;
					}
				} else {
					return true;
				}
			}
		}
		return false;
	}

    protected function _areEarningsEnabled($item, $quote = null) {

        if ($quote == null) {
            $quote = &$this;
        }

        // Should we ignore the distributions because of a catalog redemption?
        if ($this->_getCfg()->doIgnoreCDWhenCR()) {
            if ($this->_hasAppliedCatalogRedemptions($item)) {
                $item->setEarnedPointsHash(Mage::helper('rewards')->hashIt(array()));
                return false;
            }
        }

        // Should we ignore the distri rules because of a shopping cart redemption?
        if ($this->_getCfg()->doIgnoreCDWhenSCR()) {
            if ($this->_hasAppliedCartRedemptions($quote)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     *
     * @param TBT_Rewards_Model_Sales_Quote $item
     * @param TBT_Rewards_Model_Sales_Quote $quote
     * @return array 
     */
    protected function _calculateItemCatalogEarnings($item, $quote = null) {
        if ($quote == null) {
            $quote = &$this;
        }
        
        $earned_point_totals = array();
        if(false == $this->_areEarningsEnabled($item, $quote)) {
            return $earned_point_totals;
        }

        $wId = $quote->getStore()->getWebsiteId();
        $gId = $quote->getCustomerGroupId();

        $catalog_rule_ids = $this->_getTransferHelp()->getCatalogRewardsRuleIdsForProduct($item->getProductId(), $wId, $gId);

        if ($catalog_rule_ids) {
            foreach ($catalog_rule_ids as $rule_id) {
                if (!$rule_id) {
                    continue;
                }
                $points = $this->_getTransferHelp()->calculateCatalogPoints($rule_id, $item, false);
                if ($points) {
                    if ($points ['amount']) {
                        //@nelkaake 04/03/2010 1:55:03 PM : earned points get divided over the quantity then multiplied by the item quantity
                        //$points['amount'] = $points['amount'] / $item->getQty(); 
                        $earned_point_totals[] = array(
                            TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID => $points['currency'], 
                            TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT => $points['amount'], 
                            TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID => $rule_id, 
                            TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY => 1
                        );
                    }
                }
            }
        }
        return $earned_point_totals;
    }
    
    /**
     * Revoke point earnings from quote items if earnings is not available for the item
     *
     * @param TBT_Rewards_Model_Sales_Quote $quote
     * @return TBT_Rewards_Model_Sales_Quote 
     */
    public function updateDisabledEarnings($quote = null) {
        if ($quote == null) {
            $quote = &$this;
        }

        $quote_items = $quote->getAllItems();
        foreach ($quote_items as &$item) {
            if (!$item->getId())
                continue;

            if ($item->getParentItem())
                continue;

            if (false == $this->_areEarningsEnabled($item, $quote)) {
                $item->setEarnedPointsHash(Mage::helper('rewards')->hashIt(array()));
            }
        }

        return $quote;
    }

    /**
     * Updates this quotes' item catalog points.
     * @param TBT_Rewards_Model_Sales_Quote|Mage_Sales_Model_Quote $quote = null
     * @return TBT_Rewards_Model_Sales_Quote
     *
     */
    public function updateItemCatalogPoints($quote = null) {
        if ($quote == null) {
            $quote = &$this;
        }

        $quote_items = $quote->getAllItems();

        foreach ($quote_items as &$item) {
            if (!$item->getId()) {
                continue;
            }

            if ($item->getParentItem()) {
                continue;
            }
            $earned_point_totals = $this->_calculateItemCatalogEarnings($item, $quote);
            $item->setEarnedPointsHash(Mage::helper('rewards')->hashIt($earned_point_totals));
        }

        return $quote;
    }

	
	/**
	 * 
	 *
	 * @return TBT_Rewards_Model_Observer_Sales_Catalogtransfers
	 */
	protected function _getCatalogTransfersSingleton() {
		return Mage::getSingleton ( 'rewards/observer_sales_catalogtransfers' );
	}
	
	/**
	 * 
	 *
	 * @return TBT_Rewards_Model_Observer_Sales_Carttransfers
	 */
	protected function _getCartTransfersSingleton() {
		return Mage::getSingleton ( 'rewards/observer_sales_carttransfers' );
	}
	
	public function collectQuoteToOrderTransfers() {
		if ($this->_getRewardsSession ()->isAdminMode ()) {
			//		    Mage::helper('rewards')->notice("Detected admin mode in TBT_Rewards_Model_Sales_Quote::collectQuoteToOrderTransfers().");
			$this->updateItemCatalogPoints ();
		}
		
		$order_items = $this->getAllItems ();
		$is_login_notice_given = false;
		
		$catalog_transfers = $this->_getCatalogTransfersSingleton ();
		foreach ( $order_items as $item ) {
			$redeemed_point_totals = $this->_getRH ()->unhashIt ( $item->getRedeemedPointsHash () );
			if (! empty ( $redeemed_point_totals )) {
				if ($this->_getRewardsSession ()->isCustomerLoggedIn ()) {
					$customer = $this->_getRewardsSession ()->getSessionCustomer ();
					if (! $customer->canAffordFromPointsHash ( $redeemed_point_totals )) {
						throw new Mage_Core_Exception ( Mage::helper ( 'rewards' )->__ ( 'You do not have enough points to spend on this order.  ' . 'Please return to your cart and remove necessary point redemptions.' ) );
					}
					$catalog_transfers->addRedeemedPoints ( $redeemed_point_totals );
				} else {
					throw new Mage_Core_Exception ( $this->_getRH ()->__ ( 'You must be logged in to spend points.  Please return to your cart and remove the applied point redemptions.' ) );
				}
			}
			
			$earned_point_totals = $this->_getRH ()->unhashIt ( $item->getEarnedPointsHash () );
			//		    Mage::helper('rewards')->notice("Customer earned the following catalog points for item #{$item->getId()} named '{$item->getName()}': ". base64_decode($item->getEarnedPointsHash()));
			if (! empty ( $earned_point_totals )) {
				if ($this->_getRewardsSession ()->isCustomerLoggedIn ()) {
					$catalog_transfers->addEarnedPoints ( $earned_point_totals );
				} 

				//TODO:Fix for bug 108, will be moved for abstraction in the rewards session
				else if ($this->_getRewardsSession ()->isAdminMode ()) {
					$catalog_transfers->addEarnedPoints ( $earned_point_totals );
				} 

				else {
					if (! $is_login_notice_given) {
						Mage::getSingleton ( 'core/session' )->addNotice ( Mage::helper ( 'rewards' )->__ ( 'If you had created a customer account, you would have earned points for this order.' ) );
						$is_login_notice_given = true;
					}
				}
			}
		}
		
		$cart_redemptions = $this->_getCartTransfersSingleton ();
		
		$applied = Mage::getModel ( 'rewards/salesrule_list_applied' )->initQuote ( $this );
		$cart_redemptions->setRedemptionRuleIds ( $applied->getList () );
		
		if ($this->_getRewardsSession ()->getCustomerId ()) {
			$points_earning = $this->_getRewardsSession ()->getTotalPointsEarningAsString ();
			$points_spending = $this->_getRewardsSession ()->getTotalPointsSpendingAsString ();
			$cart_redemptions->setEarnedPointsString ( $points_earning );
			$cart_redemptions->setRedeemedPointsString ( $points_spending );
		}
		
		$this->reserveOrderId ();
		$catalog_transfers->setIncrementId ( $this->getReservedOrderId () );
	}
	
	public function getTotalBaseTax($cart_rule_id = null) {
		$tax_total = 0;
		foreach ( $this->getAllItems () as $item ) {
			$item_applied = Mage::getModel ( 'rewards/salesrule_list_item_applied' )->initItem ( $item );
			
			if (! $item->getId ())
				continue;
			if ($item->getParentItem ())
				continue;
			if (! empty ( $cart_rule_id ) && ! $item_applied->hasRuleId ( $cart_rule_id ))
				continue;
			$tax_total += $item->getBaseTaxAmount ();
		}
		//        Mage::log("Discountable tax \$\$ is {$tax_total}.");
		return $tax_total;
	}
	public function getTotalBaseShipping($cart_rule_id = null) {
		$total_shipping = 0;
		foreach ( $this->getAllItems () as $item ) {
			
			$item_applied = Mage::getModel ( 'rewards/salesrule_list_item_applied' )->initItem ( $item );
			
			if (! $item->getId ())
				continue;
			if ($item->getParentItem ())
				continue;
			if (! empty ( $cart_rule_id ) && ! $item_applied->hasRuleId ( $cart_rule_id ))
				continue;
			$shipaddr = $item->getQuote ()->getShippingAddress ();
			$total_shipping = $shipaddr->getBaseShippingAmount (); //@nelkaake 17/03/2010 12:04:27 AM : This is like this on purpose
			if ($shipaddr->hasOriginalBaseShippingAmount ()) {
				$total_shipping = $shipaddr->getOriginalBaseShippingAmount (); //@nelkaake : If it exists, use this one instead since it's unaltered.
				$shippingTaxClass = Mage::helper ( 'tax' )->getShippingTaxClass ( $this->getStore () );
			}
		}
		//Mage::log("Discountable shipping \$\$ is {$total_shipping}.");
		return $total_shipping;
	}
	
	public function getTotalBaseAdditional($rule) {
		$total_additional = 0;
		if ($rule->getApplyToShipping ()) {
			$total_additional += $this->getTotalBaseShipping ( $rule->getId () );
		}
		if (Mage::helper ( 'tax' )->discountTax ( $this->getStore () ) && ! Mage::helper ( 'tax' )->applyTaxAfterDiscount ( $this->getStore () )) {
			$total_additional += $this->getTotalBaseTax ();
			
			// We need to subtract the amount that was already discounted from the tax by catalog rules.
			if (! Mage::helper ( 'tax' )->priceIncludesTax ( $this->getStore () )) {
				$total_additional -= $this->getRewardsDiscountTaxAmount ();
			}
		}
		//Mage::log("Discountable additional \$\$ is {$total_additional}.");
		return $total_additional;
	}
	
	public function getAssociatedBaseTotal($cart_rule_id) {
		$price = 0;
		//Mage::log("Checking total against rule #{$cart_rule_id}");
		// Get the store configuration
		$prices_include_tax = Mage::helper ( 'tax' )->priceIncludesTax ( $this->getStore () );
		
		foreach ( $this->getAllAddresses () as $address ) {
			
			foreach ( $address->getAllItems () as $item ) {
                $item_applied = Mage::getModel ( 'rewards/salesrule_list_item_applied' )->initItem ( $item );
                
                if ($this->_skipItemSumCalc($item)) {
                    continue;
                }
                    
                if (! $item_applied->hasRuleId ( $cart_rule_id )) {
                    continue;
                }
				
				$price += $item->getBaseRowTotal (); // + $item->getBaseTaxAmount ();
			}
		}
		if ($price < 0.00001 && $price > - 0.00001) {
			$price = 0;
		}
		return $price;
	}
	
	/**
	 * 
	 * @param Mage_Sales_Model_Quote_Address_Item $item
	 */
	protected function _skipItemSumCalc($item) {
	    if($item->getParentItem () ) {
	        if(($item->getParentItem()->getProductType() != 'bundle')) {
	            return true;
	        }
	    }
	    return false;
	}
	
	/**
	 *
	 * @param array $rule_ids
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	protected function _getHighestPriorityRule($rule_ids) {
		$highest_priority_rule = null;
		foreach ( $rule_ids as $rid ) {
			$salesrule = Mage::helper ( 'rewards/transfer' )->getSalesRule ( $rid );
			if ($salesrule->getPointsAction () != 'discount_by_points_spent')
				continue; //@nelkaake Friday April 6, 2010 03:45:29 AM :
			if ($highest_priority_rule == null) {
				$highest_priority_rule = $salesrule;
				continue;
			}
			if ($salesrule->getSortOrder () < $highest_priority_rule->getSortOrder ()) {
				$highest_priority_rule = $salesrule;
				continue;
			}
		}
		return $highest_priority_rule;
	}
	
	/**
	 * Calculates the maximum points usable using spending rules 
	 * for this quote model.
	 * Initiates points_step and min_spendable_points, max_spendable_points
	 * local variables.
	 * @return $this
	 */
	protected function _calculateMaxPointsUsage() {
		if ($this->getHasCalculatedMaxUsage ())
			return $this;
		
		$quote = &$this;
		$store = $quote->getStore ();
		
		$applied = Mage::getModel ( 'rewards/salesrule_list_applied' )->initQuote ( $this );
		$rule_ids = $applied->getList ();
		
		// First select the highest priority rule that applies to the quote 
		$highest_priority_rule = $this->_getHighestPriorityRule ( $rule_ids );
		$salesrule = $highest_priority_rule;
		
		if ($highest_priority_rule != null) {
			$spendings_discount = $this->getTotalBaseRewardsSpendingDiscount ();
			$quote_total = $this->getAssociatedBaseTotal ( $highest_priority_rule->getId () ) + $this->getTotalBaseAdditional ( $salesrule );
			
			$quote_total_before_discounts = $quote_total;
			//echo("Discountable total is $quote_total_before_discounts + {$this->getShippingAddress()->getDiscountAmount()} + $spendings_discount = {$quote_total}.");
			

			//@nelkaake Added on Wednesday May 5, 2010:  Subtract any nonspending discounts
			$quote_total += $this->getShippingAddress ()->getDiscountAmount ();
			
			if (($salesrule->getSimpleAction () == 'by_percent' && $quote_total > 0) || $salesrule->getSimpleAction () != 'by_percent') {
				$quote_total += $spendings_discount;
			}
			
			$quote_total = max ( 0, $quote_total );
			//Mage::log("Discountable total \$\$ is {$this->getAssociatedBaseTotal()}.");
			$min_divisible_step = 1;
			$min = 0;
			$max = $quote->getBaseSubtotal () * 1000;
			$highest_priority_step = 0;
			$cust = Mage::getSingleton ( 'rewards/session' )->getSessionCustomer ();
			
			$salesrule = $highest_priority_rule;
			if ($highest_priority_step == 0 || $salesrule->getPriority () > $highest_priority_step) {
				$min_divisible_step = $salesrule->getPointsAmount ();
			}
			
			//@mhadianfard -c 16/11/10: 
			if ($salesrule->getSimpleAction () == 'by_percent') {
				$num_percents = 100; //ceil( ($quote_total_before_discounts) * 100 );
				

				//@nelkaake -a 16/11/10: Add 1 percent to accoutn for rounding error.
				if ($salesrule->getApplyToShipping ()) {
					$num_percents += 1;
				}
				
				$num_percents = min ( $num_percents, 100 );
				
				$max = min ( $max, ceil ( (($num_percents) / $salesrule->getDiscountAmount ()) ) * $min_divisible_step );
			} else {
				$max = min ( $max, ceil ( $quote_total / $salesrule->getDiscountAmount () ) * $min_divisible_step );
				if (Mage::getSingleton ( 'rewards/session' )->isCustomerLoggedIn ()) {
					$cust_usable_points = $cust->getUsablePointsBalance ( $salesrule->getPointsCurrencyId () );
					$cust_usable_points_even = $cust_usable_points - ($cust_usable_points % $min_divisible_step);
					$max = min ( $max, $cust_usable_points_even );
				}
			}
			
			if (sizeof ( $rule_ids ) <= 0) {
				$max = $min_divisible_step = $min = 0;
			}
			
			//@nelkaake Added on Sunday May 30, 2010: 
			$max_points_spent = $salesrule->getPointsMaxQty ();
			if ($max_points_spent) {
				if ($max > $max_points_spent) {
					$max = $max_points_spent;
				}
			}
                        
                        // truncate the overflow on the max usages to be a divisible step size
                        if( $min_divisible_step > 1 && $max > 1 ) {
                            $max = ((int)($max / $min_divisible_step)) * $min_divisible_step;
                        }		
		} else {
			$max = $min_divisible_step = $min = 0;
		}
		
		$this->setPointsStep ( $min_divisible_step );
		$this->setMinSpendablePoints ( $min );
		$this->setMaxSpendablePoints ( $max );
		$this->setHasCalculatedMaxUsage ( true );
		/*echo ("Step: {$this->getPointsStep()}, Min: {$this->getMinSpendablePoints()}, ".
        	"Max: {$this->getMaxSpendablePoints()} ");*/
		return $this;
	}
	
	public function getPointsStep() {
		$this->_calculateMaxPointsUsage ();
		return $this->getData ( 'points_step' );
	}
	public function getMinSpendablePoints() {
		$this->_calculateMaxPointsUsage ();
		return $this->getData ( 'min_spendable_points' );
	}
	public function getMaxSpendablePoints() {
		$this->_calculateMaxPointsUsage ();
		return $this->getData ( 'max_spendable_points' );
	}
	
	public function getTotalBaseRewardsSpendingDiscount() {
		$total = 0;
		foreach ( $this->getAllAddresses () as $address ) {
			$total += $address->getTotalBaseRewardsSpendingDiscount ();
		}
		return $total;
	}
	
	/*
    protected function GCD($a, $b) {
        while ( $b != 0) {
            $remainder = $a % $b;
            $a = $b;
            $b = $remainder;
        }
        return abs ($a);
    } 
    */
	
	/**
	 * Fetches the rewards session
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSession() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Fetches the rewards transfer helper
	 *
	 * @return TBT_Rewards_Helper_Transfer
	 */
	protected function _getTransferHelp() {
		return Mage::helper ( 'rewards/transfer' );
	}
	
	/**
	 * Fetches the rewards config helper
	 *
	 * @return TBT_Rewards_Helper_Config
	 */
	protected function _getCfg() {
		return Mage::helper ( 'rewards/config' );
	}
	
	/**
	 * Fetches the rewards generic helper
	 *
	 * @return TBT_Rewards_Helper_Data
	 */
	protected function _getRH() {
		return Mage::helper ( 'rewards' );
	}

}

