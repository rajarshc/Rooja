<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/solutions_page_sweettooth/Sweet_Tooth_License.php
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
 * Tax calulcator rewrite from the original magento
 * tax total calculator 
 * This needed to be done because of the strange way that Magento
 * handles taxes.  
 * Thsi is the same as TBT_Rewards_Model_Sales_Quote_Address_Total_Tax but it is written for Magento 1.4 instead of 1.3 
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Sales_Quote_Address_Total_Tax2 extends Mage_Tax_Model_Sales_Total_Quote_Tax {
	
	/**
	 * Collect tax totals for quote address
	 *
	 * @param   Mage_Sales_Model_Quote_Address $address
	 * @return  Mage_Tax_Model_Sales_Total_Quote
	 */
	public function collect(Mage_Sales_Model_Quote_Address $address) {
		//@nelkaake -a 2/02/11: This should only be run for Magento 1.4+
		if (Mage::helper ( 'rewards/version' )->isBaseMageVersionAtLeast ( "1.4.1" ))
			$this->collect141 ( $address );
		elseif (Mage::helper ( 'rewards/version' )->isBaseMageVersionAtLeast ( "1.4.0" ))
			$this->collect140 ( $address );
		else
			parent::collect ( $address );
		
		return $this;
	}
	
	/**
	 * Collect tax totals for quote address
	 *
	 * @param   Mage_Sales_Model_Quote_Address $address
	 * @return  Mage_Tax_Model_Sales_Total_Quote
	 */
	public function collect140(Mage_Sales_Model_Quote_Address $address) {
		//@nelkaake -a 2/02/11: This should only be run for Magen
		Varien_Profiler::start ( "TBT_Rewards:: Recalculating tax for points redemption purposes." );
		$store = $address->getQuote ()->getStore ();
		$customer = $address->getQuote ()->getCustomer ();
		$shippingTaxClass = Mage::helper ( 'tax' )->getShippingTaxClass ( $store );
		foreach ( $address->getAllItems () as $item ) {
			Mage::getSingleton ( 'rewards/redeem' )->refactorRedemptions ( $item, false );
		}
		Varien_Profiler::stop ( "TBT_Rewards:: Recalculating tax for points redemption purposes." );
		parent::collect ( $address );
		Varien_Profiler::start ( "TBT_Rewards:: Recalculating tax for points redemption purposes." );
		if (! $shippingTaxClass) {
			// Reset shipping tax amount
			$address->setTaxAmount ( $address->getTaxAmount () - $address->getShippingTaxAmount () );
			$address->setBaseTaxAmount ( $address->getBaseTaxAmount () - $address->getBaseShippingTaxAmount () );
			$address->setShippingTaxAmount ( null );
			$address->setBaseShippingTaxAmount ( null );
			
			$request = $this->_calculator->getRateRequest ( $address, $address->getQuote ()->getBillingAddress (), $address->getQuote ()->getCustomerTaxClassId (), $store );
			
			$address->setTotalAmount ( 'tax', max ( 0, $address->getTaxAmount () ) );
			$address->setBaseTotalAmount ( 'tax', max ( 0, $address->getBaseTaxAmount () ) );
			
			/**
			 * Subtract taxes from subtotal amount if prices include tax
			 */
			if ($this->_usePriceIncludeTax ( $store )) {
				$subtotal = $address->getSubtotalInclTax () - $address->getTotalAmount ( 'tax' );
				$baseSubtotal = $address->getBaseSubtotalInclTax () - $address->getBaseTotalAmount ( 'tax' );
				$address->setTotalAmount ( 'subtotal', $subtotal );
				$address->setBaseTotalAmount ( 'subtotal', $baseSubtotal );
			}
			
			// Recalculate shipping tax amounts 
			$this->_calculateShippingTax ( $address, $request );
		}
		
		Varien_Profiler::stop ( "TBT_Rewards:: Recalculating tax for points redemption purposes." );
		return $this;
	}
	
	/**
	 * Collect tax totals for quote address
	 *
	 * @param   Mage_Sales_Model_Quote_Address $address
	 * @return  Mage_Tax_Model_Sales_Total_Quote
	 */
	public function collect141(Mage_Sales_Model_Quote_Address $address) {
		parent::collect ( $address );
		$this->_saveRewardsTaxDiscount ( $address );
		//TODO recalculate tax on shipping discounted amount
		

		return $this;
	}
	
	/**
	 * Saves the tax discount amount and applies it to the total
	 * @param Mage_Sales_Model_Quote_Address $address
	 */
	protected function _saveRewardsTaxDiscount($address) {
		$total_rewards_discount_tax = $this->_getRewardsTaxDiscount ( $address );
		$currency_rate = Mage::helper ( 'rewards/price' )->getCurrencyRate ( $address->getQuote () );
		$total_rewards_base_discount_tax = Mage::helper ( 'rewards/price' )->getReversedCurrencyPrice ( $total_rewards_discount_tax, $currency_rate );
		
		// If prices DONT include tax only should we ACTUALLY subtract from the total tax amount
		// although we still want to save the discounted tax so we can make it appear like 
		// tax was discounted later on, or just for reference.
		$store = $address->getQuote ()->getStore ();
		if (! Mage::helper ( 'tax' )->priceIncludesTax ( $store )) {
			$this->_addAmount ( $total_rewards_discount_tax );
			$this->_addBaseAmount ( $total_rewards_discount_tax );
		}
		
		//@nelkaake -a 17/02/11: save for reference in the order
		$address->setRewardsTaxDiscount ( $total_rewards_discount_tax );
		$address->setRewardsBaseTaxDiscount ( $total_rewards_base_discount_tax );
		
		$address->getQuote ()->setRewardsDiscountTaxAmount ( - $total_rewards_discount_tax );
		$address->getQuote ()->setRewardsBaseDiscountTaxAmount ( - $total_rewards_base_discount_tax );
		
		return $this;
	
	}
	/**
	 * Calculated the total rewards catalog redemption rule tax discount 
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @return decimal
	 */
	protected function _getRewardsTaxDiscount($address) {
		$total_discount_tax_amount_rounded = 0;
		foreach ( $address->getAllItems () as $item ) {
			$row_total_before = $this->_getRedeemer ()->getRowTotalAfterRedemptions ( $item );
			$row_total = $item->getRowTotal ();
			$catalog_discount = $row_total_before - $row_total;
			$catalog_discount_w_tax = $catalog_discount * (1 + $item->getTaxPercent () / 100);
			$discount_tax_amount = $catalog_discount_w_tax - $catalog_discount;
			
			//@nelkaake -a 17/02/11: Add the rounding delta from possible rounding error
			// when catalog prices include tax.  This is set in the (catalog) Rewards totals model
			$discount_tax_amount += $address->getRewardsRoundingDelta ();
			
			$discount_tax_amount_rounded = $address->getQuote ()->getStore ()->roundPrice ( $discount_tax_amount );
			
			//$this->_addRoundingDelta($address, $discount_tax_amount, $discount_tax_amount_rounded);
			

			$total_discount_tax_amount_rounded += $discount_tax_amount_rounded;
		
		}
		return $total_discount_tax_amount_rounded;
	}
	
	/**
	 * Remembers the rounding decimal delta.  This is later referenced so that we can adjust for 
	 * rounding errors when tax is included in the product price.
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @param unknown_type $raw_amount
	 * @param unknown_type $rounded_amount
	 */
	protected function _addRoundingDelta($address, $raw_amount, $rounded_amount) {
		$delta = $raw_amount - $rounded_amount;
		$address->setRewardsRoundingDelta ( $address->getRewardsRoundingDelta () == null ? 0 : $address->getRewardsRoundingDelta () );
		$address->setRewardsRoundingDelta ( $delta + $address->getRewardsRoundingDelta () );
		return $this;
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
