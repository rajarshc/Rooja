<?php

class TBT_Rewards_Block_Checkout_Onepage_Shipping_Redeem extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'rewards/checkout/onepage/shippingmethod/redeem.phtml' );
	}
	
	/**
	 * True if the customer is logged in.
	 *
	 * @return boolean
	 */
	public function customerHasUsablePoints() {
		return $this->isCustomerLoggedIn () && $this->_getRewardsSess ()->getSessionCustomer ()->hasUsablePoints ();
	}
	
	/**
	 * True if the customer is logged in.
	 *
	 * @return boolean
	 */
	public function isCustomerLoggedIn() {
		return $this->_getRewardsSess ()->isCustomerLoggedIn ();
	}
	
	// any type of redemptions, cart and catalog
	public function cartHasRedemptions() {
		return $this->_getRewardsSess ()->hasRedemptions ();
	}
	
	// any type of redemptions, cart and catalog
	public function cartHasDistributions() {
		return $this->_getRewardsSess ()->hasDistributions ();
	}
	
	public function cartHasAnyCatalogRedemptions() {
		return $this->_getRewardsSess ()->getQuote ()->hasAnyAppliedCatalogRedemptions ();
	}
	
	/**
	 * Fetches the rewards session singleton
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}

}

?>