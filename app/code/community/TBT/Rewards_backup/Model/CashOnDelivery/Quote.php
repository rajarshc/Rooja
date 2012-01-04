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
 * Cash On Delivery Quote Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_CashOnDelivery_Quote extends TBT_Rewards_Model_Sales_Quote {
	
	public function getTotals() {
		
		/*
         * Get array from parent
         */
		$res = parent::getTotals ();
		
		/*
         * Set COD name
         */
		if (isset ( $res ['shipping'] ) && is_object ( $res ['shipping'] )) {
			if (! is_null ( $this->_payments ) && $this->getPayment ()->hasMethodInstance () && $this->getPayment ()->getMethodInstance ()->getCode () == 'cashondelivery') {
				$cod = Mage::getModel ( 'cashOnDelivery/cashOnDelivery' );
				$res ['shipping']->setData ( 'title', $res ['shipping']->getData ( 'title' ) . ' + ' . $cod->getCODTitle () );
			}
		}
		
		return $res;
	}
	
	public function collectTotals() {
		
		$cod = Mage::getModel ( 'cashOnDelivery/cashOnDelivery' );
		$res = parent::collectTotals ();
		$codamount = 0;
		
		/*
         * Check if COD is selected
         */
		
		if (! is_null ( $this->_payments ) && $this->getPayment ()->hasMethodInstance () && $this->getPayment ()->getMethodInstance ()->getCode () == 'cashondelivery') {
			
			/*
             * Calculate cost
             */
			foreach ( $res->getAllShippingAddresses () as $address ) {
				
				/*
                 * Save old shipping taxes
                 */
				$oldTax = $address->getShippingTaxAmount ();
				$oldBaseTax = $address->getBaseShippingTaxAmount ();
				
				/*
                 * Add COD cost
                 */
				if ($this->getShippingAddress ()->getCountry () == Mage::getStoreConfig ( 'shipping/origin/country_id' )) {
					$codamount = $cod->getInlandCosts ();
				} else {
					$codamount = $cod->getForeignCountryCosts ();
				}
				$address->setShippingAmount ( $address->getShippingAmount () + $address->getShippingTaxAmount () + $codamount );
				$address->setBaseShippingAmount ( $address->getBaseShippingAmount () + $address->getBaseShippingTaxAmount () + $codamount );
				
				/*
                 * Recalculate tax for shipping including COD
                 */
				$store = $address->getQuote ()->getStore ();
				$shippingTaxClass = Mage::getStoreConfig ( Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store );
				if ($shippingTaxClass) {
					$custTaxClassId = $address->getQuote ()->getCustomerTaxClassId ();
					$taxCalculationModel = Mage::getSingleton ( 'tax/calculation' );
					$request = $taxCalculationModel->getRateRequest ( $address, $address->getQuote ()->getBillingAddress (), $custTaxClassId, $store );
					
					if ($rate = $taxCalculationModel->getRate ( $request->setProductClassId ( $shippingTaxClass ) )) {
						if (! Mage::helper ( 'tax' )->shippingPriceIncludesTax ()) {
							$shippingTax = $address->getShippingAmount () * $rate / 100;
							$shippingBaseTax = $address->getBaseShippingAmount () * $rate / 100;
							
							$address->setShippingTaxAmount ( $shippingTax );
							$address->setBaseShippingTaxAmount ( $shippingBaseTax );
						} else {
							$shippingTax = $address->getShippingTaxAmount ();
							;
							$shippingBaseTax = $address->getBaseShippingTaxAmount ();
						}
						
						$shippingTax = $store->roundPrice ( $shippingTax );
						$shippingBaseTax = $store->roundPrice ( $shippingBaseTax );
						
						$address->setTaxAmount ( $address->getTaxAmount () - $oldTax + $shippingTax );
						$address->setBaseTaxAmount ( $address->getBaseTaxAmount () - $oldBaseTax + $shippingBaseTax );
						
						$this->_saveAppliedTaxes ( $address, $taxCalculationModel->getAppliedRates ( $request ), $shippingTax - $oldTax, $shippingBaseTax - $oldBaseTax, $rate );
					}
				}
				
				$address->setBaseGrandTotal ( $address->getBaseGrandTotal () + $codamount );
				$address->setGrandTotal ( $address->getGrandTotal () + $codamount );
			}
		}
		
		return $res;
	}
	
	protected function _saveAppliedTaxes(Mage_Sales_Model_Quote_Address $address, $applied, $amount, $baseAmount, $rate) {
		$previouslyAppliedTaxes = $address->getAppliedTaxes ();
		$process = count ( $previouslyAppliedTaxes );
		
		foreach ( $applied as $row ) {
			if (! isset ( $previouslyAppliedTaxes [$row ['id']] )) {
				$row ['process'] = $process;
				$row ['amount'] = 0;
				$row ['base_amount'] = 0;
				$previouslyAppliedTaxes [$row ['id']] = $row;
			}
			
			$row ['percent'] = $row ['percent'] ? $row ['percent'] : 1;
			$rate = $rate ? $rate : 1;
			
			$appliedAmount = $amount / $rate * $row ['percent'];
			$baseAppliedAmount = $baseAmount / $rate * $row ['percent'];
			
			if ($appliedAmount || $previouslyAppliedTaxes [$row ['id']] ['amount']) {
				$previouslyAppliedTaxes [$row ['id']] ['amount'] += $appliedAmount;
				$previouslyAppliedTaxes [$row ['id']] ['base_amount'] += $baseAppliedAmount;
			} else {
				unset ( $previouslyAppliedTaxes [$row ['id']] );
			}
		}
		$address->setAppliedTaxes ( $previouslyAppliedTaxes );
	}

}