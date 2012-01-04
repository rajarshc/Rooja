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
 * Session
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Session extends Mage_Core_Model_Session_Abstract {
	// Session Models
	/**
	 * Rewards session customer instance
	 *
	 * @var TBT_Rewards_Model_Customer
	 */
	protected $_customer = null;
	protected $_customerListeners = array ();
	
	public function __construct() {
		$this->init ( 'rewards' );
	}
	
	/**
	 * Adds customer listener
	 *
	 * @param TBT_Rewards_Model_Customer_Listener $listener
	 * @return TBT_Rewards_Model_Session
	 */
	public function addCustomerListener(TBT_Rewards_Model_Customer_Listener $listener) {
		$this->_customerListeners [] = $listener;
		return $this;
	}
	
	/**
	 * Triggers the after new customer create actions
	 * 
	 * @param TBT_Rewards_Model_Customer $customer
	 * @return TBT_Rewards_Model_Session
	 */
	public function triggerNewCustomerCreate(&$customer) {
	    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($customer);
		foreach ( $this->_customerListeners as $customer_listener ) {
			$customer_listener->onNewCustomerCreate ( $customer );
		}
		return $this;
	}
	
	/**
	 * Fetches the session quote.
	 *
	 * @return TBT_Rewards_Model_Sales_Quote
	 */
	public function getQuote() {
		if ($this->isAdminMode ()) {
			$quote = Mage::getSingleton ( 'adminhtml/session_quote' )->getQuote ();
			$quote = TBT_Rewards_Model_Sales_Quote::wrap ( $quote );
		} else {
			$quote = $this->getCheckoutSession ()->getQuote ();
		}
		return $quote;
	}
	
	/**
	 * Fetches the checkout session
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	public function getCheckoutSession() {
		return Mage::getSingleton ( 'checkout/session' );
	}
	
	/**
	 * Fetches the customer session
	 *
	 * @return Mage_Customer_Model_Session
	 */
	public function getCustomerSession() {
		return Mage::getSingleton ( 'customer/session' );
	}
	
	/**
	 * Fetches the customer in the current session
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	public function getSessionCustomer() {
		if ($this->_customer == null) {
			$this->refreshSessionCustomer ();
		} else {
			
			if (! $this->isCustConfirmPending ()) {
				$customer_id_mismatch = ($this->getCustomerId () != $this->_customer->getId ());
				if ($customer_id_mismatch) {
					$this->refreshSessionCustomer ();
				}
			}
		}
		return Mage::getModel('rewards/customer')->getRewardsCustomer($this->_customer);
	}
	
	public function isCustConfirmPending($customer = null) {
		if ($customer == null) {
			$customer = &$this->_customer;
		}
		if ($customer == null) {
			return false;
		}
		//debug_print_backtrace();
		if ($customer->isConfirmationRequired ()) {
			if ($customer->getConfirmation ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Reloads the customer within the rewards session.
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	public function refreshSessionCustomer($customer_id = null) {
		if ($customer_id == null) {
			$customer_id = $this->getCustomerId ();
		}
		$this->_customer = Mage::getModel ( 'rewards/customer' )->load ( $customer_id );
		return $this;
	}
	
	/**
	 * Sets the rewards session customer.
	 *
	 * @param TBT_Rewards_Model_Customer $customer
	 * @return TBT_Rewards_Model_Session
	 */
	public function setCustomer($customer) {
	    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($customer);
		if ($this->isAdminMode ()) {
			// TODO: What do we do here? ... hmm
		} else {
			if (! $this->isCustConfirmPending ( $customer )) {
				$this->getCustomerSession ()->setCustomer ( $customer );
				$this->getCustomerSession ()->setId ( $customer->getId () );
			}
		
		//     		try {
		//         	   $this->getCustomerSession()->setCustomer($customer);
		//     		} catch (Exception $e) {
		//     			// If you couldn't set the customer into the regular customer session, no big deal, 
		//     			// the rewards session can still be active.
		//     		}
		//     		try {
		//         	   $this->getCustomerSession()->setId($customer->getId());
		//     		} catch (Exception $e) {
		//     			die("couldn't set the customer id into the customer session.. now that's a problem: ". $e->getMessage());
		//     		}
		}
		$this->refreshSessionCustomer ( $customer->getId () );
		return $this;
	}
	
	/**
	 * alias for getSessionCustomer()
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	public function getCustomer() {
		return $this->getSessionCustomer ();
	}
	
	/**
	 * Fetches the admin session singleton
	 *
	 * @return Mage_Admin_Model_Session
	 */
	public function getAdminSession() {
		$admin_sess = Mage::getSingleton ( 'admin/session' );
		return $admin_sess;
	}
	
	/**
	 * True if we're in admin mode and the administrator is logged in.
	 *
	 * @return boolean
	 */
	public function isAdminMode() {
		
		$is_admin_mode = Mage::app ()->getStore ()->isAdmin ();
		return $is_admin_mode;
	}
	
	/**
	 * Gets the rewards csutomer ID for this rewards session.
	 *
	 * @return integer
	 */
	public function getCustomerId() {
		
		if ($this->isCustConfirmPending ()) {
			if ($this->_customer == null) {
				$customer_id = null;
			} else {
				$customer_id = $this->_customer->getId ();
			}
		} else {
			$customer_id = $this->getCustomerSession ()->getCustomerId ();
		}
		if (empty ( $customer_id )) {
			if ($this->isAdminMode ()) {
				$quote_model = $this->getAdminSession ()->getQuote ();
				if (! empty ( $quote_model )) {
					$customer_id = $quote_model->getCustomerId ();
				}
			}
		}
		return $customer_id;
	}
	
	/**
	 * True if the customer id exists; IE if the customer is logged in.
	 * False otherwise.
	 * 
	 * @return boolean
	 */
	public function isCustomerLoggedIn() {
		if ($this->getCustomerId ()) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the rewards catalogrule points action singleton
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Actions
	 */
	private function getActionsSingleton() {
		return Mage::getSingleton ( 'rewards/salesrule_actions' );
	}
	
	/**
	 * If the cart contains too many redemptions this function triggers.
	 *
	 * @return boolean true if too many redemptions are in the cart.
	 */
	public function isCartOverspent() {
		$points_spent = $this->getTotalPointsSpending ();
		$customer = $this->getSessionCustomer ();
		if ($customer->getId ()) {
			return ! $customer->canAfford ( $points_spent );
		} else {
			return false;
		}
	}
	
	/**
	 * True if the rewards session cart contains any redemptions in the 
	 * cart.  False otherwise.
	 *
	 * @return boolean
	 */
	public function hasRedemptions() {
		$redemptions = $this->getTotalPointsSpending ();
		return ! empty ( $redemptions );
	}
	
	/**
	 * True if the rewards session cart contains any distributions in the 
	 * cart.  False otherwise.
	 *
	 * @return boolean
	 */
	public function hasDistributions() {
		$distri = $this->getTotalPointsEarning ();
		return ! empty ( $distri );
	}
	/**
	 * Calculates the amount of points to be given or deducted from a customer's cart, given the
	 * rule that is being executed and possibly a list of items to act upon, if applicable.
	 *
	 * @param   int                                 $rule_id            : the ID of the rule to execute
	 * @param   array(Mage_Sales_Model_Quote_Item)  $order_items        : the list of items to act upon
	 * @param   boolean                             $allow_redemptions  : whether or not to calculate redemption rules
	 * @param	boolean								$prediction_mode	: allows rules to calulcate points even if the rule has not been yet applied to the item(s) in the cart
	 * @return  array                                                   : 'amount' & 'currency' as keys
	 */
	public function calculateCartPoints($rule_id, $order_items, $allow_redemptions, $prediction_mode = false) {
		$rule = Mage::helper ( 'rewards/transfer' )->getSalesRule ( $rule_id );
		$crActions = $this->getActionsSingleton ();
		//$this->setPointsSpending(50);
		//$ss = Mage::getSingleton('rewards/salesrule_session');
		

		$qty = 1;
		if ($rule->getId ()) {
			if ($crActions->isGivePointsAction ( $rule->getPointsAction () )) {
				// give a flat number of points if this rule's conditions are met
				$points_to_transfer = $rule->getPointsAmount ();
			} else if (($rule->getPointsAction () == 'deduct_points') && $allow_redemptions) {
				// deduct a flat number of points if this rule's conditions are met
				$points_to_transfer = $rule->getPointsAmount () * - 1;
			} else if ($rule->getPointsAction () == 'give_by_amount_spent') {
				// give a set qty of points per every given amount spent if this rule's conditions are met
				// - this is a total price amongst ALL associated items, so add it up
				$price = Mage::helper ( 'rewards/transfer' )->getTotalAssociatedItemPrice ( $order_items, $rule->getId () );
				$points_to_transfer = $rule->getPointsAmount () * floor ( $price / $rule->getPointsAmountStep () );
				
				//@nelkaake Added on Sunday May 30, 2010: 
				if ($max_points_spent = $rule->getPointsMaxQty () * $qty) {
					if ($points_to_transfer < 0) {
						if (- $points_to_transfer > $max_points_spent)
							$points_to_transfer = - $max_points_spent;
					} else {
						if ($points_to_transfer > $max_points_spent)
							$points_to_transfer = $max_points_spent;
					}
				}
				$debug = array ('price' => $price, 'points_to_transfer' => $points_to_transfer, 'rule_name' => $rule->getName () );
			} else if (($rule->getPointsAction () == 'deduct_by_amount_spent') && $allow_redemptions) {
				// deduct a set qty of points per every given amount spent if this rule's conditions are met
				// - this is a total price amongst ALL associated items, so add it up
				$price = Mage::helper ( 'rewards/transfer' )->getTotalAssociatedItemPrice ( $order_items, $rule->getId (), null, $prediction_mode );
				$points_to_transfer = $rule->getPointsAmount () * floor ( $price / $rule->getPointsAmountStep () );
				$points_to_transfer *= - 1;
				
				//@nelkaake Added on Sunday May 30, 2010: 
				if ($max_points_spent = $rule->getPointsMaxQty () * $qty) {
					if ($points_to_transfer < 0) {
						if (- $points_to_transfer > $max_points_spent)
							$points_to_transfer = - $max_points_spent;
					} else {
						if ($points_to_transfer > $max_points_spent)
							$points_to_transfer = $max_points_spent;
					}
				}
			
			} else if ($rule->getPointsAction () == 'give_by_qty') {
				// give a set qty of points per every given qty of items if this rule's conditions are met
				// - this is a total quantity amongst ALL associated items, so add it up
				$qty = Mage::helper ( 'rewards/transfer' )->getTotalAssociatedItemQty ( $order_items, $rule->getId () );
				$points_to_transfer = $rule->getPointsAmount () * floor ( $qty / $rule->getPointsQtyStep () );
				
				//@nelkaake Added on Sunday May 30, 2010: 
				if ($max_points_spent = $rule->getPointsMaxQty () * $qty) {
					if ($points_to_transfer < 0) {
						if (- $points_to_transfer > $max_points_spent)
							$points_to_transfer = - $max_points_spent;
					} else {
						if ($points_to_transfer > $max_points_spent)
							$points_to_transfer = $max_points_spent;
					}
				}
			} else if (($rule->getPointsAction () == 'deduct_by_qty') && $allow_redemptions) {
				// deduct a set qty of points per every given qty of items if this rule's conditions are met
				// - this is a total quantity amongst ALL associated items, so add it up
				$qty = Mage::helper ( 'rewards/transfer' )->getTotalAssociatedItemQty ( $order_items, $rule->getId () );
				$points_to_transfer = $rule->getPointsAmount () * floor ( $qty / $rule->getPointsQtyStep () );
				$points_to_transfer *= - 1;
				
				//@nelkaake Added on Sunday May 30, 2010: 
				if ($max_points_spent = $rule->getPointsMaxQty () * $qty) {
					if ($points_to_transfer < 0) {
						if (- $points_to_transfer > $max_points_spent)
							$points_to_transfer = - $max_points_spent;
					} else {
						if ($points_to_transfer > $max_points_spent)
							$points_to_transfer = $max_points_spent;
					}
				}
			
			} else if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT && $allow_redemptions) {
				// deduct a set qty of points per every given amount spent if this rule's conditions are met
				// - this is a total price amongst ALL associated items, so add it up
				$price = Mage::helper ( 'rewards/transfer' )->getTotalAssociatedItemPrice ( $order_items, $rule->getId () );
				if (! empty ( $order_items )) {
					if ($order_items instanceof TBT_Rewards_Model_Sales_Quote || $order_items instanceof TBT_Rewards_Model_Sales_Order) {
						foreach ( $order_items as &$item ) {
							$first_item = $item;
							break;
						}
					} elseif (is_array ( $order_items )) {
						$first_item = $order_items [0];
					}
					if ($first_item->getQuote ()) {
						$additional = $first_item->getQuote ()->getTotalBaseAdditional ( $rule );
					} else {
						$additional = $first_item->getOrder ()->getTotalBaseAdditional ( $rule );
					}
					$price += $additional;
				}
				
				//@nelkaake -a 16/11/10: if the setting to reward cart points only 
				// on the amount after cart discounts is enabled then we should remove 
				// any previously applied discounts with this rule.   
				// TODO log in MANTIS         
				if (Mage::helper ( 'rewards/config' )->calcCartPointsAfterDiscount ()) {
					$price += $this->getPointsSpending () * $rule->getDiscountAmount ();
				}
				
				if ($rule->getSimpleAction () == 'by_percent') {
					$num_percents = 100; //ceil( ($quote_total_before_discounts) * 100 );
					//@nelkaake -a 16/11/10: Add 1 percent to accoutn for rounding error.
					if ($rule->getApplyToShipping ()) {
						$num_percents += 1;
					}
					$num_percents = min ( $num_percents, 100 );
					
					$max_points = $rule->getPointsAmount () * ceil ( (($num_percents) / $rule->getDiscountAmount ()) );
				} else {
					$max_points = $rule->getPointsAmount () * ceil ( $price / $rule->getDiscountAmount () );
				}
				if ($this->getPointsSpending () > $max_points) {
					$this->setPointsSpending ( $max_points );
				}
				
				//@nelkaake Added on Sunday May 30, 2010: 
				$points_to_transfer = $this->getPointsSpending ();
				$points_to_transfer *= - 1;
				if ($max_points_spent = $rule->getPointsMaxQty () * $qty) {
					if ($points_to_transfer < 0) {
						if (- $points_to_transfer > $max_points_spent)
							$points_to_transfer = - $max_points_spent;
					} else {
						if ($points_to_transfer > $max_points_spent)
							$points_to_transfer = $max_points_spent;
					}
				}
			} else {
				// whatever the Points Action is set to is invalid
				// - this means no transfer of points
				$points_to_transfer = 0;
			}
			
			// New code for instore!
            $result = new Varien_Object(array(
				'points_to_transfer'      => $points_to_transfer
            ));
            Mage::dispatchEvent('rewards_calculate_cart_points', array(
				'rule_id'    => $rule->getId(),
				'order_items'    => $order_items,
				'allow_redemptions' => $allow_redemptions,
				'result'   => $result,
            ));
            $points_to_transfer = $result->getPointsToTransfer();
            // End new code for instore

			$points_array = array ('amount' => $points_to_transfer, 'currency' => $rule->getPointsCurrencyId (), 'rule_id' => $rule_id, 'rule_name' => $rule->getName () );
			
			//Mage::helper('rewards/debug')->dd($points_array, false, false);
			return $points_array;
		}
		
		return null;
	}
	/**
	 * Returns the number of points being spent on the cart.
	 *
	 * @return array() array of currency_id=>points_qty combination sums.
	 */
	public function getTotalPointsSpending() {
		return $this->getTotalPointsSpentOnCart ();
	}
	/**
	 * Returns the number of points being spent on the cart.
	 *
	 * @return array() array of currency_id=>points_qty combination sums.
	 */
	public function getTotalPointsSpentOnCart($cart = null) {
		if ($cart == null)
			$cart = $this->getQuote ();
		$points = array ();
		$points_exist = false;
		
		$applied_valid = Mage::getModel ( 'rewards/salesrule_list_valid_applied' )->initQuote ( $cart );
		$applied_redemptions = $applied_valid->getList ();
		foreach ( $applied_redemptions as $rule_id ) {
			$rule = Mage::helper ( 'rewards/transfer' )->getSalesRule ( $rule_id );
			if (! $rule->getId ()) {
				continue;
			}
			
			if (Mage::getModel ( 'rewards/salesrule_actions' )->isRedemptionAction ( $rule->getPointsAction () )) {
				$rule_points = $this->calculateCartPoints ( $rule->getId (), $cart->getAllItems (), true );
				
				if ($rule_points && ($rule_points ['amount'] != 0)) {
					if (isset ( $points [$rule_points ['currency']] )) {
						$points [$rule_points ['currency']] += $rule_points ['amount'] * - 1;
					} else {
						$points_exist = true;
						$points [$rule_points ['currency']] = $rule_points ['amount'] * - 1;
					}
				}
			}
		}
		
		foreach ( $cart->getAllItems () as $item ) {
			if ($item->getParentItem ()) {
				continue;
			}
			
			$points_to_redeem = ( array ) Mage::helper ( 'rewards' )->unhashIt ( $item->getRedeemedPointsHash () );
			if ($this->calculateAccumulatedPoints ( $points_to_redeem, $points )) {
				$points_exist = true;
			}
		}
		
		if (! $points_exist) {
			$points = array ();
		
		//return '<i>'. $this->__('No points') .'</i>.';
		}
		
		return $points;
	}
	
	/**
	 * Calculates the total amount of points that a given shopping cart / quote should
	 * earn the customer, if ordered.
	 *
	 * @param   Mage_Sales_Model_Quote  $cart   : the cart to use when calculating points
	 * @return  array( array )                  : the inner array has 'amount' & 'currency' as keys
	 */
	public function updateShoppingCartPoints($cart = null) {
		if ($cart == null)
			$cart = $this->getQuote ();
		$cart_points = array ();
		
		foreach ( $this->getCartRewardsRuleIds ( $cart ) as $rule_id ) {
			if (! $rule_id) {
				continue;
			}
			
			$rule = Mage::helper ( 'rewards/transfer' )->getSalesRule ( $rule_id );
			if ($rule->isDistributionRule ()) {
				$points = $this->calculateCartPoints ( $rule_id, $cart->getAllItems (), false );
				
				if ($points) {
					$points ['amount'] = ( int ) $points ['amount'];
					$points ['rule_name'] = $points ['rule_name'];
					if ($points ['amount'] != 0) {
						$cart_points [] = $points;
					}
				}
			}
		}
		
		return $cart_points;
	}
	
	/**
	 * Gets a list of all rule ID's that are associated with the given order/shoppingcart/quote.
	 *
	 * @param   Mage_Sales_Model_Order  $order  : The order object with which the returned rules are associated
	 * @return  array(int)                      : An array of rule ID's that are associated with the order
	 */
	public function getCartRewardsRuleIds($order) {
		return Mage::helper ( 'rewards/transfer' )->getCartRewardsRuleIds ( $order );
	}
	
	/**
	 * Returns a map of applicable and applied shopping cart redemptions
	 *
	 * @param   Mage_Sales_Model_Quote  $cart   : the cart to use when calculating points
	 * @return  array( array )                  : m
	 */
	public function collectShoppingCartRedemptions($cart = null) {
		if ($cart == null)
			$cart = $this->getQuote ();
		$applied_redemptions = array ();
		$applicable_redemptions = array ();
		
		$applied_redemptions = Mage::getModel ( 'rewards/salesrule_list_valid_applied' )->initQuote ( $cart )->getList ();
		foreach ( $applied_redemptions as $rule_id ) {
			$rule = Mage::helper ( 'rewards/transfer' )->getSalesRule ( $rule_id );
			if (! $rule_id || ! $rule->getId ()) {
				continue;
			}
			$is_redemption_rule = Mage::getModel ( 'rewards/salesrule_actions' )->isRedemptionAction ( $rule->getPointsAction () );
			if (! $is_redemption_rule) {
				continue;
			}
			$applied_redemptions [$rule_id] = Mage::helper ( 'rewards/rule' )->getQuickCartRedemEntry ( $rule );
		}
		$sorted_applied = Mage::helper ( 'rewards/rule' )->sortQuickCartRedemEntries ( $applied_redemptions );
		$cart_redemptions = $this->getCartRedemptionsMap ();
		foreach ( $cart_redemptions as $rule_id => $is_applicable ) {
			$rule = Mage::helper ( 'rewards/transfer' )->getSalesRule ( $rule_id );
			if (! $rule_id || ! $rule->getId ()) {
				continue;
			}
			$is_redemption_rule = Mage::getModel ( 'rewards/salesrule_actions' )->isRedemptionAction ( $rule->getPointsAction () );
			if (! $is_redemption_rule) {
				continue;
			}
			$applicable_redemptions [$rule_id] = Mage::helper ( 'rewards/rule' )->getQuickCartRedemEntry ( $rule );
		}
		$sorted_applicable = Mage::helper ( 'rewards/rule' )->sortQuickCartRedemEntries ( $applicable_redemptions );
		
		$ret = array ('applied' => $applied_redemptions, 'applicable' => $applicable_redemptions, 'sorted_applicable' => $sorted_applicable, 'sorted_applied' => $sorted_applied );
		return $ret;
	}
	
	/**
	 * Returns a map of cart redemptions to make the search process
	 * easier.
	 * This fetches the _applied_ cart redemptions only.
	 * 
	 * @param   Mage_Sales_Model_Quote  $cart   : the cart to use when calculating points
	 * @return unknown
	 */
	private function getCartRedemptionsMap($cart = null) {
		if ($cart == null)
			$cart = $this->getQuote ();
		$applicable_rules = Mage::getModel ( 'rewards/salesrule_list_valid_applicable' )->initQuote ( $cart );
		$cart_redemptions = $applicable_rules->getList ();
		
		$redem_map = array ();
		foreach ( $cart_redemptions as $redem_id ) {
			if (empty ( $redem_id ) && $redem_id != 0)
				continue;
			$redem_map [$redem_id] = true;
		}
		return $redem_map;
	}
	
	public function getTotalPointsEarnedOnCart($cart = null) {
		
		if ($cart == null)
			$cart = $this->getQuote ();
		
		if ($this->isAdminMode ()) {
			$cart = $this->getQuote ()->updateItemCatalogPoints ();
		}
		
		$points = array ();
		$points_exist = false;
		
		$total_cart_points = $this->updateShoppingCartPoints ( $cart );
		//    	Mage::helper('rewards/debug')->dd($total_cart_points);
		

		foreach ( $total_cart_points as $transfer ) {
			if (isset ( $points [$transfer ['currency']] )) {
				$points [$transfer ['currency']] += $transfer ['amount'];
			} else {
				$points_exist = true;
				$points [$transfer ['currency']] = $transfer ['amount'];
			}
		}
		
		foreach ( $cart->getAllItems () as $item ) {
			if ($item->getParentItem ())
				continue;
			
			$points_to_earn = Mage::helper ( 'rewards' )->unhashIt ( $item->getEarnedPointsHash () );
			if ($this->calculateAccumulatedPoints ( $points_to_earn, $points )) {
				$points_exist = true;
			}
		}
		
		if (! $points_exist) {
			$points = array ();
		
		//return '<i>'. $this->__('No points') .'</i>.';
		}
		
		return $points;
	}
	
	/**
	 * Returns the number of points being earned on the cart.
	 *
	 * @return array() array of currency_id=>points_qty combination sums.
	 */
	public function getTotalPointsEarning() {
		return $this->getTotalPointsEarnedOnCart ();
	}
	/**
	 * Returns the number of points remaining.
	 *
	 * @return array|false false if the customer is not logged in.
	 */
	public function getTotalPointsRemaining() {
		if ($this->isCustomerLoggedIn ()) {
			$customer = $this->getSessionCustomer ();
			$points_spending = $this->getTotalPointsSpending ();
			$points_remain = $customer->predictPointsRemaining ( $points_spending );
		} else {
			return false;
		}
		return $points_remain;
	}
	
	/**
	 * Returns the number of points remaining as a string.
	 *
	 * @return string
	 */
	public function getTotalPointsRemainingAsString() {
		$points_remain = $this->getTotalPointsRemaining ();
		if (sizeof ( $points_remain ) < 1) {
			$points_remain_str = $this->_getNoPointsString ();
		} else {
			$points_remain_str = Mage::getModel ( 'rewards/points' )->set ( $points_remain );
		}
		return $points_remain_str;
	}
	
	/**
	 * Returns the number of points being spent on the cart as a string.
	 *
	 * @return string
	 */
	public function getTotalPointsSpendingAsString() {
		$points_spending = $this->getTotalPointsSpending ();
		if (sizeof ( $points_spending ) < 1) {
			$points_spending = $this->_getNoPointsString ();
		}
		$points_string = Mage::getModel ( 'rewards/points' )->set ( $points_spending );
		return $points_string;
	}
	
	/**
	 * Returns the number of points being earned on the cart as a string.
	 *
	 * @return string
	 */
	public function getTotalPointsEarningAsString() {
		$points_earning = $this->getTotalPointsEarning ();
		if (sizeof ( $points_earning ) < 1) {
			$points_earning = $this->_getNoPointsString ();
		}
		$points_string = Mage::getModel ( 'rewards/points' )->set ( $points_earning );
		return $points_string;
	}
	
	/**
	 * Returns the number of points being spent on the cart as a string list.
	 *
	 * @return string
	 */
	public function getTotalPointsSpendingAsStringList() {
		$points_spending = $this->getTotalPointsSpending ();
		if (sizeof ( $points_spending ) < 1) {
			$points_spending = $this->_getNoPointsString ();
		}
		$points_string = Mage::getModel ( 'rewards/points' )->set ( $points_spending )->getRendering ()->setDisplayAsList ( true );
		return $points_string;
	}
	
	/**
	 * Returns the number of points being earned on the cart as a string list.
	 *
	 * @return string
	 */
	public function getTotalPointsEarningAsStringList() {
		$points_earning = $this->getTotalPointsEarning ();
		if (sizeof ( $points_earning ) < 1) {
			$points_earning = $this->_getNoPointsString ();
		}
		$points_string = Mage::getModel ( 'rewards/points' )->set ( $points_earning )->getRendering ()->setDisplayAsList ( true );
		return $points_string;
	}
	private function calculateAccumulatedPoints($points_to_transfer, &$points) {
		$points_exist = false;
		
		if ($points_to_transfer) {
			foreach ( $points_to_transfer as $points_per_rule ) {
				if ($points_per_rule) {
					$points_per_rule = ( array ) $points_per_rule;
					
					$points_rule_currency = $points_per_rule [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID];
					$points_rule_amount = $points_per_rule [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT];
					$points_rule_applicable_qty = $points_per_rule [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY];
					
					if (isset ( $points [$points_rule_currency] )) {
						$points [$points_rule_currency] += $points_rule_amount * $points_rule_applicable_qty;
					} else {
						$points_exist = true;
						$points [$points_rule_currency] = $points_rule_amount * $points_rule_applicable_qty;
					}
				}
			}
		}
		
		return $points_exist;
	}
	
	/**
	 * Fetches the appropriate group id for the current session customer
	 *
	 * @return integer
	 */
	public function getCustomerGroupId() {
		if ($this->isCustomerLoggedIn ()) {
			$gId = $this->getCustomerSession ()->getCustomerGroupId ();
		} else {
			$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
		}
		return $gId;
	}
	
	/**
	 * Returns a translated string of "no points" italisized.
	 * @deprecated Use the rewards/points model
	 *
	 * @return string
	 */
	protected function _getNoPointsString() {
		return "" . ( string ) (Mage::getModel ( 'rewards/points' ));
	}
	
	public function clearPointsSpending() {
		$this->setPointsSpending ( 0 );
		return $this;
	}
	
	public function hasVariableSpendingCartRules() {
		$cart = $this->getQuote ();
		$points = array ();
		$points_exist = false;
		
		$applied_redemptions = explode ( ',', $cart->getAppliedRedemptions () );
		foreach ( $applied_redemptions as $rule_id ) {
			$rule = Mage::getModel ( 'rewards/salesrule_rule' )->load ( $rule_id );
			if (! $rule->getId ()) {
				continue;
			}
			
			if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
				return true;
			}
		}
		return false;
	}
	
	public function getPointsSpending() {
		if (! $this->hasData ( 'points_spending' )) {
			$this->setPointsSpending ( 0 );
		}
		
		if ($this->isCustomerLoggedIn () == false) {
			$this->setPointsSpending ( 0 );
		}
		
		$uses = ( int ) ($this->getData ( 'points_spending' ));
		return $uses;
	}
	
	public function setPointsSpending($points_qty) {
		$this->setData ( 'points_spending', $points_qty );
		return $this;
	}

}
