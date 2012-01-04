<?php

class TBT_Rewards_Model_Observer_Checkout_Onepage extends Varien_Object {
	
	public function __construct() {
	
	}
	
	public function refreshCartBeforeCheckout($observer) {
		$this->setRequest ( $observer->getControllerAction ()->getRequest () );
		if (! $this->_isMinOrderActive ())
			return $this;
		
		$cart = $this->_getCart ();
		if (! $cart->getQuote ()->getItemsCount ())
			return $this;
		
		$cart->init ();
		$cart->save ();
		$this->_getQuote ()->validateMinimumAmount ();
		
		return $this;
	}
	
	/**
	 * @return true if minimum order config setting is active.
	 */
	protected function _isMinOrderActive() {
		$quote = $this->_getQuote ();
		$storeId = $quote->getStoreId ();
		$minOrderActive = Mage::getStoreConfigFlag ( 'sales/minimum_order/active', $storeId );
		$minOrderMulti = Mage::getStoreConfigFlag ( 'sales/minimum_order/multi_address', $storeId );
		
		return $minOrderActive;
	}
	
	/**
	 * Get current active quote instance
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote() {
		return $this->_getCart ()->getQuote ();
	}
	
	/**
	 * Retrieve shopping cart model object
	 *
	 * @return Mage_Checkout_Model_Cart
	 */
	protected function _getCart() {
		return Mage::getSingleton ( 'checkout/cart' );
	}
	
	/**
	 * @deprecated unused
	 * Enter description here ...
	 * @param unknown_type $observer
	 */
	public function payForShippingWithPoints($observer) {
		$this->setRequest ( $observer->getControllerAction ()->getRequest () );
		
		$pay_for_shipping_with_points = $this->getRequest ()->getParam ( 'pay_for_shipping_with_points', '' );
		
		if ($pay_for_shipping_with_points) {
			$quote = Mage::getSingleton ( 'rewards/session' )->getQuote ();
			$shipaddr = $quote->getShippingAddress ();
			$total_shipping_value = $shipaddr->getShippingAmount ();
			$current_points_spending = Mage::getSingleton ( 'rewards/session' )->getPointsSpending ();
			Mage::log ( "Paying for shipping with points..." );
			
			$rule_ids = explode ( ',', $quote->getAppliedRedemptions () );
			foreach ( $rule_ids as $rid ) {
				$salesrule = Mage::helper ( 'rewards/transfer' )->getSalesRule ( $rid );
				if ($salesrule->getPointsAction () != 'discount_by_points_spent')
					continue;
				if (! $salesrule->getDiscountAmount ())
					continue; // discount amount should not be empty (so we dont divide by zero)
				Mage::log ( "Points step according to quote is {$quote->getPointsStep()}" );
				if ($salesrule->getPointsAmount () == $quote->getPointsStep ()) {
					$uses_to_zero_shipping = ceil ( $total_shipping_value / $salesrule->getDiscountAmount () );
					Mage::getSingleton ( 'rewards/session' )->setPointsSpending ( $uses_to_zero_shipping + $current_points_spending );
					Mage::log ( "Added {$uses_to_zero_shipping} to existing points uage of {$current_points_spending}" );
					break;
				}
			}
		}
		
		return $this;
	}

}