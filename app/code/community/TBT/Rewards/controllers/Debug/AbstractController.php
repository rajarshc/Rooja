<?php

/**
 * Image Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_Rewards_Debug_AbstractController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		echo "This will test something... <BR />";
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
	 * Controller predispatch method
	 *
	 * @return Mage_Adminhtml_Controller_Action
	 */
	public function preDispatch() {
		
		// Authentication Check:
		if (! isset ( $_SERVER ['PHP_AUTH_USER'] )) {
			$this->auth ();
		} else {
			$auth_result = Mage::getModel ( 'admin/user' )->authenticate ( $_SERVER ['PHP_AUTH_USER'], $_SERVER ['PHP_AUTH_PW'] );
			if ($auth_result) {
				return parent::preDispatch ();
			} else {
				unset ( $_SERVER ['PHP_AUTH_USER'] );
				$this->auth ();
			}
		}
	}
	
	/**
	 * Authentication Function
	 */
	protected function auth() {
		$title = "Store Administrator Log-in";
		header ( 'WWW-Authenticate: Basic realm="' . $title . '"' );
		header ( 'HTTP/1.0 401 Unauthorized' );
		echo "You must authenticate yourself before viewing this file.  Please e-mail administration if you don't think you should be seeeing this message.";
		exit ();
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