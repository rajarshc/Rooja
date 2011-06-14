<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PayPal Standard Checkout Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Model_Paypal_Standard extends Mage_Paypal_Model_Standard {
	
	//@nelkaake Sunday April 25, 2010 : Calculated in the base currency.
	public function getPaypalZeroCheckoutFee() {
		return 0.01;
	}
	
	/**
	 * Returns a quote model that is applicable to this checkout model
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote() {
		if ($this->getHasEnsuredQuote ())
			return $this->getEnsuredQuote ();
		$quote = $this->getQuote ();
		$items = $quote->getAllItems ();
		if (empty ( $items )) {
			$orderIncrementId = $this->getCheckout ()->getLastRealOrderId ();
			$order = Mage::getModel ( 'sales/order' )->loadByIncrementId ( $orderIncrementId );
			$quote_id = $order->getQuoteId ();
			$quote = Mage::getModel ( 'rewards/sales_quote' )->load ( $order->getQuoteId () );
		}
		$this->setHasEnsuredQuote ( true );
		$this->setEnsuredQuote ( $quote );

		return $quote;
	}
	
	/**
	 * Appends Sweet Tooth information 
	 *
	 * @return array paypal checkout fields
	 */
	public function getStandardCheckoutFormFields() {


		$items = $this->_getQuote ()->getAllItems ();
		
		//@nelkaake -a 16/11/10: There are two major things we need to do here: 
		// 1. If the balance is 0, make sure there is at least 1 penny in the subtotal so PayPal lets us checkout.  
		// 2. Gather all of our catalog points redemption discounts and add them to the final cart discount total.
		

		Mage::getSingleton ( 'rewards/redeem' )->refactorRedemptions ( $items );
		$scf = parent::getStandardCheckoutFormFields ();
		if (isset ( $scf ['discount_amount_cart'] )) {
			$discountAmount = ( float ) $scf ['discount_amount_cart'];
			if ($discountAmount >= $this->_getQuote ()->getSubtotal ()) {
				//@nelkaake Sunday April 25, 2010 : We're discounting the whole amount, so we need to add a premium in order for PayPal to see the output.
				$scf ['discount_amount_cart'] = ( float ) $scf ['discount_amount_cart'] - $this->getPaypalZeroCheckoutFee ();
				$scf ['discount_amount_cart'] = ( string ) $scf ['discount_amount_cart'];
			}
		} else {
			$scf ['discount_amount_cart'] = 0;
		}
		$scf ['discount_amount_cart'] = ( float ) $scf ['discount_amount_cart'];
		
		//@nelkaake -a 16/11/10: Figure out the accumulated difference in price so we can add to the discount amount 
		//TODO @nelkaake: Can we calculate the discount amount another way, perhaps using the new getCatalogDiscount method in the Redeem singleton?
		$acc_diff = 0;
        
        $acc_diff = $this->_getTotalCatalogDiscount();
        
		$acc_diff = $this->_getQuote ()->getStore ()->roundPrice ( $acc_diff );
		if ($acc_diff == - 0)
			$acc_diff = 0;



		$scf ['discount_amount_cart'] += - $acc_diff;
		
		//@nelkaake Added on Monday October 4, 2010: Uncomment this if you want to see what's being sent to PayPal standard checkout
        // die(print_r($scf, true));
		

		return $scf;
	}
	
	/**
	 * Fetches the redemption calculator model
	 *
	 * @return TBT_Rewards_Model_Redeem
	 */
	protected function _getRedeemer() {
		return Mage::getSingleton ( 'rewards/redeem' );
	}
    
	
	/**
	 * Returns the total accumulated catalog discounts on the quote model that is in this class
	 * @return int negative discount amount
	 */
    protected function _getTotalCatalogDiscount() {
    
		$items = $this->_getQuote ()->getAllItems ();
        
		if (! is_array ( $items )) {
			$items = array ($items );
		}
        
        $acc_discount = 0;
		foreach ( $items as $item ) {
            $acc_discount += $this->_getTotalItemCatalogDiscount($item);
		}
        
        return $acc_discount;
		
    }
    
    /**
	 * Returns the total accumulated catalog discounts on an item
     * @param Mage_Sales_Model_Quote_Item $item
	 * @return int negative discount amount
     */
    protected function _getTotalItemCatalogDiscount($item) {
		if (! $item->getQuoteId () || ! $item->getId ()) {
			return 0;
		}
        
        $row_total_before_disc = $item->getRowTotalBeforeRedemptions ();
		$row_total = $item->getRowTotal ();
		
        if($item->getRewardsCatalogDiscount() ) {
            $total_discount = $item->getRewardsCatalogDiscount();
        } else {
            if(empty($row_total_before_disc)) {
    			$item->setRowTotal ( $item->getRowTotalBeforeRedemptions () );
    			$item->setRowTotalInclTax ( $item->getRowTotalBeforeRedemptionsInclTax () );
                $total_discount = $this->_getRedeemer ()->getTotalCatalogDiscount ( $item );
            } else {
                $total_discount = $item->getRowTotalBeforeRedemptions () - $item->getRowTotal();
            }
        }                                                    
	  
        return $total_discount;
    
    }

}
 