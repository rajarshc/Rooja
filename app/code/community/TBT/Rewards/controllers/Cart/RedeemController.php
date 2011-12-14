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
 * Cart Redeem Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Cart_RedeemController extends Mage_Core_Controller_Front_Action {
	
	
	public function cartAction() {
		if (! ($quote = $this->_loadValidQuote ())) {
			return;
		}
		
		if (! ($cart_redemptions = $this->_loadValidRedemptions ())) {
			return;
		}
		
		$quote->setCartRedemptions ( $cart_redemptions )->save ();
	}
	
	protected function _loadValidItem($item_id = null) {
		if ($item_id === null) {
			$item_id = ( int ) $this->getRequest ()->getParam ( 'item_id' );
		}
		if (! $item_id) {
			$this->_forward ( 'noRoute' );
			return null;
		}
		
		$item = Mage::getModel ( 'sales/quote_item' )->load ( $item_id );
		
		return $item;
	}
	
	protected function _loadValidQuote($quote_id = null) {
		if ($quote_id === null) {
			$quote_id = ( int ) $this->getRequest ()->getParam ( 'quote_id' );
		}
		if (! $quote_id) {
			$this->_forward ( 'noRoute' );
			return null;
		}
		
		$quote = Mage::getModel ( 'sales/quote' )->load ( $quote_id );
		
		return $quote;
	}
	
	protected function _loadValidRedemptions($cart_redemptions = null) {
		if ($cart_redemptions === null) {
			$cart_redemptions = $this->getRequest ()->getParam ( 'redem' );
		}
		if (! $cart_redemptions) {
			$this->_forward ( 'noRoute' );
			return null;
		}
		
		return $cart_redemptions;
	}
	
	public function addAction() {
		$pollId = intval ( $this->getRequest ()->getParam ( 'poll_id' ) );
		$answerId = intval ( $this->getRequest ()->getParam ( 'vote' ) );
		
		$poll = Mage::getModel ( 'poll/poll' )->load ( $pollId );
		
		// Check poll data
		if ($poll->getId () && ! $poll->getClosed () && ! $poll->isVoted ()) {
			$vote = Mage::getModel ( 'poll/poll_vote' )->setPollAnswerId ( $answerId )->setIpAddress ( ip2long ( $this->getRequest ()->getServer ( 'REMOTE_ADDR' ) ) )->setCustomerId ( Mage::getSingleton ( 'customer/session' )->getCustomerId () );
			
			$poll->addVote ( $vote );
			Mage::getSingleton ( 'core/session' )->setJustVotedPoll ( $pollId );
		}
		$this->_redirectReferer ();
	}
	
	/**
	 * Adds a series of rule ids to the cart after validating them against the customers point balance
	 * @param string $rule_ids
	 */
	public function cartaddAction() {
		
		Varien_Profiler::start ( "TBT_Rewards:: Add shopping cart redemption to cart" );
		$rule_ids = $this->getRequest ()->get ( 'rids' );
		
		try {
			// Check if customer is logged in.
			$customer = Mage::getSingleton ( 'rewards/session' )->getSessionCustomer ();
			if (! $customer->getId ()) {
				//customer is not logged in yet.
				Mage::getSingleton ( 'customer/session' )->addError ( $this->__ ( "Please log in, or sign up to apply point redemptions!" ) );
				$this->_redirect ( 'customer/account/login' );
				return;
			}
			
			if (empty ( $rule_ids ) && $rule_ids != 0) {
				//customer is not logged in yet. 
				throw new Exception ( $this->__ ( "A valid redemption id to apply to this cart was not selected." ) );
			}
			if (! is_array ( $rule_ids )) {
				$rule_list = explode ( ",", $rule_ids ); //Turn the string of rule ids into an array
			}
			
			$quote_id = Mage::getSingleton ( 'checkout/session' )->getQuoteId ();
			$quote = Mage::getModel ( 'sales/quote' )->load ( $quote_id );
			
			//Load in a temp summary of the customers point balance, so we can check to see if the applied rules will overdraw their points
			$customer_point_balance = array ();
			foreach ( Mage::getModel ( 'rewards/currency' )->getAvailCurrencyIds () as $currency_id ) {
				$customer_point_balance [$currency_id] = $customer->getUsablePointsBalance ( $currency_id );
			}
			$currency_captions = Mage::getModel ( 'rewards/currency' )->getAvailCurrencies ();
			
			$flag = true;
			$doSave = false;
			foreach ( $rule_list as $rule_id ) {
				$rule = Mage::helper ( 'rewards/rule' )->getSalesRule ( $rule_id );
				
				//If the rule does not apply to the cart add it to the error message
				if (array_search ( ( int ) $rule_id, explode ( ',', $quote->getCartRedemptions () ) ) === false) {
					$this->_getSess ()->addError ( $this->__ ( "The rule %s does not apply to your cart.", $rule->getName () ) );
				} else {
					//if (is_null($rule->getPointsCurrencyId()) || $rule->getPointsCurrencyId() == "") continue;        
					if ($customer_point_balance [$rule->getPointsCurrencyId ()] < $rule->getPointsAmount ()) {
						$this->_getSess ()->addError ( $this->__ ( "You do have enough %s Points.", $currency_captions [$rule->getPointsCurrencyId ()] ) . "<br/>\n" . $this->__ ( "The rule entitled '%s' was not applied to your cart.", $rule->getName () ) );
						$flag = false;
					} else {
						$applied = Mage::getModel ( 'rewards/salesrule_list_applied' )->initQuote ( $quote );
						$applied->add ( $rule_id )->saveToQuote ( $quote );
						$doSave = true;
					}
				}
			}
			//If the customer does not have enough points to complete the redemption
			if (! $flag) {
				// At least one of the redemption rules that were applied could not be completed because the customer did not have enough points
			}
			if ($doSave) {
				//@nelkaake 2/6/2010 2:45:18 PM : update shipping rates
				$quote->getShippingAddress ()->setCollectShippingRates ( true );
				$quote->save ();
				$this->_getCart ()->init ()->save ();
				$this->_getSession ()->setCartWasUpdated ( true );
				
				$this->_getSess ()->addSuccess ( $this->__ ( "All requested reward redemptions were applied to your cart" ) );
			}
		} catch ( Exception $e ) {
			$this->_getSess ()->addError ( $this->__ ( "An error occurred while trying to apply the redemption to your cart." ) );
			$this->_getSess ()->addError ( $this->__ ( $e->getMessage () ) );
		}
		Varien_Profiler::stop ( "TBT_Rewards:: Add shopping cart redemption to cart" );
		$this->_redirect ( 'checkout/cart/' );
	
	}
	
	private function _getSess() {
		return Mage::getSingleton ( 'checkout/session' );
	}
	
	public function cartremoveAction() {
		Varien_Profiler::start ( "TBT_Rewards:: remove shopping cart redemption from cart" );
		$rule_ids = $this->getRequest ()->get ( 'rids' );
		try {
			if (! is_array ( $rule_ids )) {
				$rule_list = explode ( ",", $rule_ids ); //Turn the string of rule ids into an array
			}
			
			$quote_id = Mage::getSingleton ( 'checkout/session' )->getQuoteId ();
			$quote = Mage::getModel ( 'sales/quote' )->load ( $quote_id );
			
			$flag = true;
			$doSave = false;
			foreach ( $rule_list as $rule_id ) {
				$rule = Mage::helper ( 'rewards/rule' )->getSalesRule ( $rule_id );
				$applied_redemptions = explode ( ',', $quote->getAppliedRedemptions () );
				$applicable_redemptions = explode ( ',', $quote->getCartRedemptions () );
				
				//If the rule does not apply to the cart add it to the error message
				if (array_search ( ( int ) $rule_id, $applied_redemptions ) === false) {
					$this->_getSess ()->addError ( $this->__ ( "The rule %s was not applied to your cart.", $rule->getName () ) );
				} else {
					// index at which the possibly removable rule id was found.
					$applied = Mage::getModel ( 'rewards/salesrule_list_applied' )->initQuote ( $quote );
					$applied->remove ( $rule_id )->saveToQuote ( $quote );
					$doSave = true;
				}
			}
			//If the customer does not have enough points to complete the redemption
			if (! $flag) {
				// At least one of the redemption rules that were applied could not be completed because the customer did not have enough points
			}
			if ($doSave) {
				//@nelkaake 2/6/2010 2:45:18 PM : update shipping rates
				$quote->getShippingAddress ()->setCollectShippingRates ( true );
				$quote->save ();
				$this->_getCart ()->init ()->save ();
				$this->_getSession ()->setCartWasUpdated ( true );
				
				$this->_getSess ()->addSuccess ( $this->__ ( "All requested reward redemptions were removed from your cart" ) );
			}
		} catch ( Exception $e ) {
			$this->_getSess ()->addError ( $this->__ ( "An error occurred while trying to remove the redemption from your cart." ) );
			$this->_getSess ()->addError ( $this->__ ( $e->getMessage () ) );
		}
		Varien_Profiler::stop ( "TBT_Rewards:: remove shopping cart redemption from cart" );
		$this->_redirect ( 'checkout/cart/' );
	}
	
	public function catalogaddAction() {
		
		$rule_ids = $this->getRequest ()->get ( 'rids' );
		$item_id = $this->getRequest ()->get ( 'item_id' );
		
		// Check if customer is logged in.
		$customer = Mage::getSingleton ( 'rewards/session' )->getSessionCustomer ();
		if (! $customer->getId ()) {
			Mage::getSingleton ( 'customer/session' )->addError ( $this->__ ( "Please log in or sign up to apply point redemptions!" ) );
			$this->_redirect ( 'customer/account/login' );
			return;
		}
		
		if (! $item_id) { //If the item was not good.
			Mage::getSingleton ( 'customer/session' )->addError ( $this->__ ( "An item was not selected or the item selected was invalid" ) );
			$this->_redirect ( 'checkout/cart/' );
			return;
		}
		$item = Mage::getModel ( 'sales/quote_item' )->load ( $item_id );
		
		if (empty ( $rule_ids ) && $rule_ids != 0) {
			throw new Exception ( $this->__ ( "A valid redemption id to apply to this product was not selected." ) );
		}
		if (! is_array ( $rule_ids )) {
			$rule_list = explode ( ",", $rule_ids ); //Turn the string of rule ids into an array
		}
		
		//Call function to apply the redemptions to the item
		try {
			if (Mage::getModel ( 'rewards/redeem' )->addCatalogRedemptionsToItem ( $item, $rule_list, $customer )) {
				$this->_getSess ()->addSuccess ( $this->__ ( "All requested reward redemptions were applied to the product." ) );
			}
		} catch ( Exception $e ) {
			$this->_getSess ()->addError ( $this->__ ( $e->getMessage () ) );
		}
		
		$this->_redirect ( 'checkout/cart/' );
	}
	
	public function catalogremoveAction() {
		$rule_ids = $this->getRequest ()->get ( 'rids' );
		$item_id = $this->getRequest ()->get ( 'item_id' );
		/** @var integer redemption instance id for custom redemptions (like spend X points get Y off) **/
		$red_inst_id = $this->getRequest ()->get ( 'inst_id' );
		
		if (! $item_id) { //If the item was not good.
			Mage::getSingleton ( 'customer/session' )->addError ( $this->__ ( "An item was not selected or the item selected was invalid" ) );
			$this->_redirect ( 'checkout/cart/' );
			return;
		}
		$item = Mage::getSingleton ( 'checkout/cart' )->getQuote ()->getItemById ( $item_id );
		if (! $item || ! $item->getId ()) {
			Mage::getSingleton ( 'customer/session' )->addError ( $this->__ ( "Your logged in session may have expired.  An item was not selected or the item selected was invalid" ) );
			$this->_redirect ( 'checkout/cart/' );
			return;
		}
		
		//Call function to remove the redemptions to the item
		try {
			if (empty ( $rule_ids ) && $rule_ids != 0) {
				//customer is not logged in yet. 
				throw new Exception ( $this->__ ( "A valid redemption id to apply to this product was not selected." ) );
			}
			if (! is_array ( $rule_ids )) {
				$rule_list = explode ( ",", $rule_ids ); //Turn the string of rule ids into an array
			}
			
			if (Mage::getModel ( 'rewards/redeem' )->removeCatalogRedemptionsFromItem ( $item, $rule_list, $red_inst_id )) {
				$this->_getSess ()->addSuccess ( $this->__ ( "All requested reward redemptions were removed from the product." ) );
			}
		} catch ( Exception $e ) {
			$this->_getSess ()->addError ( $this->__ ( $e->getMessage () ) );
		}
		
		$this->_redirect ( 'checkout/cart/' );
	}
	
	/**
	 * This gives a tertiary check.  Since the points usage interface will
	 * never go past the usable number of points, and since the discount
	 * will never go past the maximum discount, we don't really care about
	 * any further validation at this point... unless the customer WANTS
	 * to try to spend more points.
	 *
	 * @param int $sp
	 * @return boolean
	 */
	protected function isValidSpendingAmount($sp) {
		return true; //TODO implement this.
	}
	
	/**
	 * AJAX action called from the Shopping Cart Points slider.
	 * This will either output the shopping cart totals block or an error message string
	 */
	public function changePointsSpendingAction() {
		$new_points_spending = $this->getRequest ()->getParam ( "points_spending" );
		if ($this->isValidSpendingAmount ( $new_points_spending )) {
			Mage::getSingleton ( 'rewards/session' )->setPointsSpending ( $new_points_spending );
		}
		$cart = $this->_getCart ();
		
		// if there are still products in the shopping cart
		if ($cart->getItemsCount ()) {
            $rewardsQuote = Mage::getModel('rewards/sales_quote');
            
			$cart->getQuote ()->getShippingAddress ()->setCollectShippingRates ( true );
			
            $rewardsQuote->updateItemCatalogPoints( $cart->getQuote() );
            
			$cart->getQuote ()->collectTotals ();
			
            $rewardsQuote->updateDisabledEarnings( $cart->getQuote() );
                                    
			$this->loadLayout ();
			$block = $this->getLayout ()->getBlock ( 'checkout.cart.totals' );
			$this->getResponse ()->setBody ( $block->toHtml () );
		} else {
			// probably the session expired.
			$this->refreshResponse ();
		}
	
	}
	
	/**
	 * Outputs a message that tells the user that their session expired.
	 */
	public function refreshResponse() {
		$refresh_js = "";
		$refresh_js .= "<div class='rewards-session_expired'>" . $this->__ ( "Your session has expired.  Please refresh the page." ) . "</div>";
		$this->getResponse ()->setBody ( $refresh_js );
		return $this;
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
	 * Get current active quote instance
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote() {
		return $this->_getCart ()->getQuote ();
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
     * @deprecated unused function
     */
	public function catalogAction() {
		if (! ($item = $this->_loadValidItem ())) {
			return;
		}
		
		if (! ($catalog_redemptions = $this->_loadValidRedemptions ())) {
			return;
		}
		
		$quote->setCartRedemptions ( $cart_redemptions )->save ();
	}
}