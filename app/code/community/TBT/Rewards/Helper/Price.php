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
 * Helper for the prices of products and quote items with monetary currencies
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Price extends Mage_Core_Helper_Abstract {
	
	const CURRENCY_RATE_ROUND = 4;
	
	/**
	 * This function reverses any prices that have already been converted to the current store
	 * currency rate.
	 * @param float $price
	 * @param float $target_currency_rate
	 * @param boolean $do_round : Should the price be rounded using the current store's rounding function?
	 */
	public function getReversedCurrencyPrice($price, $target_currency_rate = null, $do_round = true) {
		if ($target_currency_rate == null) {
			$cc = Mage::app ()->getStore ()->getCurrentCurrency ();
			$bc = 1 / (Mage::app ()->getStore ()->getBaseCurrency ()->getRate ( $cc ));
			$target_currency_rate = $bc;
		}
		$final_price = $price * $target_currency_rate;
		if ($do_round) {
			$final_price = Mage::app ()->getStore ()->roundPrice ( $final_price );
		}
		return $final_price;
	}
	
	/**
	 * Fetches the item price that should be used for calculating max catalog points spending
	 * for the given item.  Uses the following check hierarchy:
	 * CustomPrice
	 * > if not set use item price
	 * > if not set use product final price
	 * > if not set use 
	 * 
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function getItemProductPrice($item) {
		
		// Prepare data from item and initalize counters
		$store_currency = ( float ) $this->getCurrencyRate ( $item->getQuote () );
		if ($item->hasCustomPrice ()) {
			$product_price = ( float ) $item->getCustomPrice () * $store_currency;
		} elseif (Mage::helper ( 'tax' )->priceIncludesTax () && ($item->getRowTotalBeforeRedemptions () && $item->getRowTotal ())) {
			$rt = ( float ) $item->getRowTotal ();
			$item->setRowTotal ( $item->getRowTotalBeforeRedemptions () );
			$product_price = ( float ) Mage::helper ( 'checkout' )->getPriceInclTax ( $item );
			$item->setRowTotal ( $rt );
		} else {
			// item doesn't have a price, use the item product final price
			$item_price = ( float ) $item->getPrice ();
			$item_price = ! empty ( $item_price ) ? $item->getPrice () : $item->getProduct ()->getFinalPrice ();
			$product_price = ( float ) $item_price * $store_currency;
		}
		
		return $product_price;
	}
	
	/**
	 * Fetches the current store's currency or the one from the quote model.
	 * @param Mage_Sales_Model_Quote $quote [also accepts order model]
	 */
	public function getCurrencyRate($quote = null) {
		if ($quote->getStoreToQuoteRate () && $quote) {
			$c = round ( $quote->getStoreToQuoteRate (), 4 );
		} else {
			if ($quote) {
				$store = ($quote->getStore ()) ? $quote->getStore () : Mage::app ()->getStore ();
			} else {
				$store = Mage::app ()->getStore ();
			}
			
			$baseCurrency = $store->getBaseCurrency ();
			
			if ($quote) {
				$quoteCurrency = $quote->hasForcedCurrency () ? $quote->getForcedCurrency () : $store->getCurrentCurrency ();
			} else {
				$quoteCurrency = $store->getCurrentCurrency ();
			}
			
			$c = $baseCurrency->getRate ( $quoteCurrency );
		}
		return $c;
	
	}
	
	/**
	 * 
	 * this will take an item with row total $100 with 1% tax and change it to 
	 * $99 with $1 tax amount applied.
	 * @param unknown_type $item
	 */
	public function refactorTaxOnItem(&$item) {
		$store = Mage::app ()->getStore ();
		
		$tax = $item->getTaxPercent () / 100;
		$new_tax_amount = ($store->roundPrice ( $item->getRowTotal () * $tax ));
		$new_row_total = ( float ) ($store->roundPrice ( $item->getRowTotal () - $new_tax_amount ));
		
		if ($new_row_total <= - 0)
			$new_row_total = 0;
		$item->setRowTotal ( $new_row_total );
		
		if ($new_tax_amount <= - 0)
			$new_tax_amount = 0;
		$item->setTaxAmount ( $new_tax_amount );
		return $item;
	}
	
	/**
	 * @deprecated not sure where we use this, but we shouldnt be setting row total before redemptions anywhere other than redeem singleton or totals models
	 * @param unknown_type $item
	 */
	public function getUsableOldRowTotal(&$item) {
		if ($item->getRowTotalBeforeRedemptions ()) {
			$old_rt = $item->getRowTotalBeforeRedemptions ();
		} else {
			$old_rt = $item->getRowTotal ();
			$item->setRowTotalBeforeRedemptions ( $old_rt );
		}
		if (Mage::helper ( 'tax' )->priceIncludesTax ()) {
			$old_rt += $item->getTaxAmount ();
		}
		return $old_rt;
	}

}