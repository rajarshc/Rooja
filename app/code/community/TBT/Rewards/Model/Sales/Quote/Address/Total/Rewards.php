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
 * Sales Quote Address Total Rewards
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Sales_Quote_Address_Total_Rewards extends Mage_Sales_Model_Quote_Address_Total_Abstract {
	protected $discount_amt = 0;
	
	public function __construct() {
		$this->setCode ( 'rewards' );
	}

    /**
     * Triggers AFTER collection methods, only when Magento is trying to show the total amount.
     * @see Mage_Sales_Model_Quote_Address_Total_Abstract::fetch()
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        if ( $address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING ) return $this;
        
        if ( $this->discount_amt != 0 ) {
            $address->addTotal( array(
                'code' => $this->getCode(), 
                'title' => Mage::helper( 'sales' )->__( 'Item Discounts' ), 
                'value' => $this->discount_amt
            ) );
        }
        
        return $this;
    }
	
	/**
	 * This triggers right after the subtotal is calculated
	 * @see Mage_Sales_Model_Quote_Address_Total_Abstract::collect()
	 */
	public function collect(Mage_Sales_Model_Quote_Address $address) {
		// No support for multi-shipping
		if (Mage::helper ( 'rewards' )->isMultishipMode ( $address )) {
			return $this;
		}
		
		$this->_clearRoundingDeltas ( $address );
		
		//Update the subtotals using the points discount
		$final_price = $this->getFinalPrice ( $address );
		$base_final_price = Mage::helper ( 'rewards/price' )->getReversedCurrencyPrice ( $final_price );
		
		$address->setSubtotal ( $address->getSubtotal () + $final_price );
		$address->setBaseSubtotal ( $address->getBaseSubtotal () + $base_final_price );
		//Then update the grandtotals
		$address->setGrandTotal ( $address->getSubtotal () );
		$address->setBaseGrandTotal ( $address->getBaseSubtotal () );
		
		return $this;
	}
	
	/**
	 * Loops through each item within the cart and gets the amount of money discounted by points
	 * <font color="red"><b>Also updates the row total</b></font>
	 *
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @return float amount of money discounted by points
	 */
	public function getFinalPrice(Mage_Sales_Model_Quote_Address $address) {
		$acc_diff = 0;
		$items = $address->getAllItems ();
		
		if (! is_array ( $items )) {
			$items = array ($items );
		}
		//@nelkaake -a 17/02/11: 
		foreach ( $items as &$item ) {
			if (! $item->getQuoteId () || ! $item->getId ()) {
				continue;
			}
			
			if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' ) && $item->getRowTotalBeforeRedemptions () != null) {
				$item->setRowTotal ( $item->getRowTotalBeforeRedemptions () );
				$item->setRowTotalInclTax ( $item->getRowTotalBeforeRedemptionsInclTax () );
			}
			
			if ($item->getRowTotalBeforeRedemptions () == null && $item->getRewardsCatalogDiscount () == null) {
				$this->_getRedeemer ()->resetItemDiscounts ( $item );
			}

			if (!Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' )) {				
				$item->setRowTotalBeforeRedemptions ($item->getRowTotal ());
				$item->setRowTotalBeforeRedemptionsInclTax ($item->getRowTotalInclTax ());
			}
						
			$catalog_discount = $this->_getRedeemer ()->getTotalCatalogDiscount ( $item );
			
			$catalog_discount_rounded = $address->getQuote ()->getStore ()->roundPrice ( $catalog_discount );
			
			$this->_addRoundingDelta ( $item, $catalog_discount, $catalog_discount_rounded );
			
			//@nelkaake -a 17/02/11: TODO implement this field for items in the DB so it can be saved
			$item->setRewardsCatalogDiscount ( $catalog_discount );
			
			$row_total_after_redeem = $this->_getRedeemer ()->getRowTotalAfterRedemptions($item);
			$item->setRowTotalAfterRedemptions($row_total_after_redeem);
			
			$row_total_after_redeem_incl_tax = $this->_getRedeemer ()->getRowTotalAfterRedemptionsInclTax($item);
			$item->setRowTotalAfterRedemptionsInclTax($row_total_after_redeem_incl_tax);
			
			
			$acc_diff += $catalog_discount;
			
			$new_redeemed_points = $this->_getRedeemer ()->getUpdatedRedemptionsHash ( $item );
			$item->setRedeemedPointsHash ( $new_redeemed_points )->save ();
		
		}
		
		// Failsafe to make sure discounts never go positive (and add to the total)
		$acc_diff = ($acc_diff > 0) ? 0 : $acc_diff;
		
		$acc_diff_rounded = $address->getQuote ()->getStore ()->roundPrice ( $acc_diff );
		if ($acc_diff_rounded == - 0)
			$acc_diff_rounded = 0;
		$this->discount_amt = $acc_diff_rounded;
		
		$currency_rate = Mage::helper ( 'rewards/price' )->getCurrencyRate ( $address->getQuote () );
		$acc_diff_base = Mage::helper ( 'rewards/price' )->getReversedCurrencyPrice ( $acc_diff, $currency_rate );
		
		//@nelkaake -a 17/02/11: Save the rewards catalog discount amount into the quote 
		// so that it can be coppied to the order later.
		$address->getQuote ()->setRewardsDiscountAmount ( - $acc_diff );
		$address->getQuote ()->setRewardsBaseDiscountAmount ( - $acc_diff_base );
		
		$this->_addRoundingDelta ( $address, $acc_diff, $acc_diff_rounded );
		
		return $acc_diff;
	}
	
	/**
	 * Remembers the rounding decimal delta.  This is later referenced so that we can adjust for 
	 * rounding errors when tax is included in the product price.
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @param decimal $raw_amount
	 * @param decimal $rounded_amount
	 */
	protected function _addRoundingDelta($address, $raw_amount, $rounded_amount) {
		$delta = $raw_amount - $rounded_amount;
		$address->setRewardsRoundingDelta ( $address->getRewardsRoundingDelta () ? $address->getRewardsRoundingDelta () : 0 );
		$address->setRewardsRoundingDelta ( $delta + $address->getRewardsRoundingDelta () );
		return $this;
	}
	/**
	 * Forgets previously calculated rounding decimal deltas.  This is later referenced so that we can adjust for 
	 * rounding errors when tax is included in the product price.
	 */
	protected function _clearRoundingDeltas($address) {
		$address->setRewardsRoundingDelta ( 0 );
		$items = $address->getAllItems ();
		
		if (! is_array ( $items ))
			$items = array ($items );
		
		foreach ( $items as $item ) {
			$item->setRewardsRoundingDelta ( 0 );
		}
		return $this;
	}
	/**
	 * Process model configuration array.
	 * This method can be used for changing models apply sort order
	 *
	 * @param   array $config
	 * @param   store $store
	 * @return  array
	 */
	public function processConfigArray($config, $store) {
		return $config;
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
	 * Fetches the rewards session.
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	private function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
}
