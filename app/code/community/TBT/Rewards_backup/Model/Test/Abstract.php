<?php
class TBT_Rewards_Model_Test_Abstract extends Varien_Object {
	
	/**
	 * Output content
	 * @param mixed $content
	 */
	public function o($content) {
		if(!$this->getOutputAdapter()) {
			echo $content; 
		}
		flush();
		return $this;
	}
	
	
	protected function getRedeem() {
		return Mage::getSingleton ( 'rewards/redeem' );
	}
	
	/**
	 *
	 * @return TBT_Rewards_Model_Sales_Quote
	 */
	protected function getQuote() {
		return Mage::getSingleton ( 'rewards/session' )->getQuote ();
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
	 * Get checkout session model instance
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getSession() {
		return Mage::getSingleton ( 'checkout/session' );
	}
	/**
	 * Get checkout session model instance
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSession() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	/**
	 * Get checkout session model instance
	 *
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getCustomerSession() {
		return Mage::getSingleton ( 'customer/session' );
	}
	
	/**
	 * Get current active quote instance
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote() {
		return $this->_getCart ()->getQuote ();
	}
	
	protected function getStore() {
		return Mage::app ()->getStore ();
	}
	
	/**
	 *
	 * @param int $customer_id=1
	 * @return TBT_Rewards_Model_Customer
	 */
	public function ensureCustomerLoggedIn($customer_id = 1) {
		if (! $this->_getRewardsSession ()->isCustomerLoggedIn ()) {
			$this->_getCustomerSession ()->loginById ( $customer_id );
		}
		return $this->_getRewardsSession ()->getCustomer ();
	}
}