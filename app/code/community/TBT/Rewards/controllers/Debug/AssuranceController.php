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
 * Image Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Debug_AssuranceController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		echo "This utility will run an array of tests to ensure that Sweet Tooth is running properly on your store <BR />";
		echo "If you're shopping on the front-end your cart contents will be lost. <BR />";
		
		//$this->printCart();
		//   $this->test1_a(11, 1, array(52,51));
		//  $this->test1_a(8, 1, array(52,51));
		$this->test1_a ( 11, 2, array (52, 51 ) );
	}
	
	public function test1_a($rule, $applicable, $cart) {
		$this->test1 ( 50, $rule, $applicable, $cart );
		$this->test1 ( 1, $rule, $applicable, $cart );
		$this->test1 ( 100, $rule, $applicable, $cart );
		$this->test1 ( 500, $rule, $applicable, $cart );
		$this->test1 ( 130, $rule, $applicable, $cart );
		$this->test1 ( 129, $rule, $applicable, $cart );
		$this->removeAllItems ();
		echo "<BR /><BR />";
		
		return $this;
	}
	
	public function test1($uses, $rule_id, $applicable_qty, $cart) {
		// Add in two products, the first will have a redemption the other will not.
		$this->removeAllItems ()->addProducts ( $cart );
		$items = $this->_getQuote ()->getAllItems ();
		;
		$item1 = $items [0];
		
		echo "_____TEST 1_____<BR />";
		echo "_____BEFORE: ";
		$this->printCart ();
		
		$red_instance = Mage::getModel ( 'rewards/redemption_instance' );
		$red_instance->setItem ( $item1 )->setUses ( $uses )->setApplicableQty ( $applicable_qty )->setRedemptionInstId ( 1 )->loadFromRule ( $rule_id )->calcEffect ();
		
		echo "_____$uses uses of rule={$rule_id} for effect={$red_instance->getEffect()} on {$applicable_qty}x{$item1->getName()}.<BR />";
		
		$red_collection = Mage::getModel ( 'rewards/redemption_instance_collection' );
		$red_collection->setQuoteItem ( $item1 )->addItem ( $red_instance )->saveToItem ();
		
		$item1->save ();
		$this->getRedeem ()->refactorRedemptions ( $items );
		$this->getRedeem ()->refactorRedemptions ( $items );
		
		echo "______AFTER: ";
		$this->printCart ();
		
		return $this;
	}
	
	public function addProducts($product_ids) {
		foreach ( $product_ids as $pid ) {
			$this->_getCart ()->addProduct ( $pid );
		}
		$this->_getCart ()->save ();
		$this->_getSession ()->setCartWasUpdated ( true );
		
		return $this;
	}
	
	public function removeAllItems() {
		foreach ( $this->_getCart ()->getItems () as $item ) {
			$this->_getCart ()->removeItem ( $item->getId () );
		}
		$this->_getCart ()->save ();
		return $this;
	}
	
	public function printCart() {
		$cart = $this->getQuote ();
		echo "Cart Contents: ";
		if (! $cart->hasItems ()) {
			echo "(empty)";
		} else {
			$printed_item = array ();
			foreach ( $cart->getAllItems () as $item ) {
				if (! isset ( $printed_item [$item->getProductId ()] )) {
					echo "[{$item->getQty()}x'{$item->getName()}'=={$item->getRowTotal()}] ";
					echo ", ";
				}
				$printed_item [$item->getProductId ()] = true;
			}
		}
		echo "<BR />";
		
		return $this;
	}
	
	protected function getRedeem() {
		return Mage::getSingleton ( 'rewards/redeem' );
	}
	
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

}