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
 * Catalog Rule Observer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Catalogrule_Observer extends Mage_CatalogRule_Model_Observer {
	
	//TODO: Clean up alot of this code. Remove all commented sections.
	const APPLICABLE_QTY = TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY;
	const POINTS_RULE_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID;
	const POINTS_AMT = TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT;
	const POINTS_CURRENCY_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID;
	const POINTS_USES = TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES;
	const POINTS_EFFECT = TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT;
	const POINTS_INST_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_INST_ID;
	
	private $is_already_saving = false;
	
	protected $_rulePrices = array ();
	
	private function checkRule($rule) {
		$localDate = Mage::getModel ( 'core/date' )->gmtDate ();
		
		//Check to see if its active
		if (! $rule->getIsActive ())
			return false;
		
		//Make sure its between the active dates    
		if (! (strtotime ( $rule->getFromDate () ) <= strtotime ( $localDate ) && strtotime ( $rule->getToDate () ) >= strtotime ( $localDate )))
			return false;
		
		//Make sure the customer is within the allowed group for the rule   
		if (! $this->isInGroup ( Mage::getSingleton ( 'customer/session' )->getCustomerId (), explode ( ",", $rule->getCustomerGroupIds () ) ))
			return false;
		
		return true;
	}
	
	/**
	 * Returns true if customerId is within the customer groups listed
	 * @param string $customerId                : current customer id
	 * @param array $groupIds                   : customer group ids array
	 * 
	 * @return boolean                          
	 */
	private function isInGroup($customerId, array $groupIds) {
		return array_search ( Mage::getModel ( 'rewards/customer' )->load ( $customerId )->getGroupId (), $groupIds ) !== false;
	}
	
	/**
	 * Updates the points that you can use on products in the catalog for a single product
	 * @see Mage_CatalogRule_Model_Observer::applyAllRulesOnProduct()
	 */
	public function applyAllRulesOnProduct($observer) {
		parent::applyAllRulesOnProduct ( $observer );
		
		$product_id = $observer->getProduct ()->getId ();
		$this->updateRulesHashOnProduct ( $product_id );
		
		//        Mage::getResourceSingleton('catalogrule/rule')->applyAllRulesForDateRange();
		//        Mage::app()->removeCache('catalog_rules_dirty');
		

		return $this;
	}
	
	/**
	 * Updates the points that you can use on products in the catalog afer product save events.
	 * @param unknown_type $observer
	 */
	public function applyRulesOnProductAfterSave($observer) {
		$action = $observer->getControllerAction ();
		$request = $action->getRequest ();
		$product_id = $request->getParam ( "id" );
		$this->updateRulesHashOnProduct ( $product_id );
		return $this;
	}
	
	public function updateRulesHashOnProduct($product_id) {
		Mage::getSingleton ( 'rewards/catalogrule_saver' )->updateRulesHashOnProduct ( $product_id );
		return $this;
	}
	
	/**
	 * Daily update catalog price rule by cron
	 * Update include interval 3 days - current day - 1 days before + 1 days after
	 * This method is called from cron process, cron is workink in UTC time and
	 * we shold generate data for interval -1 day ... +1 day
	 *
	 * @param   Varien_Event_Observer $observer
	 * @return  Mage_CatalogRule_Model_Observer
	 */
	public function dailyCatalogUpdate($observer) {
		Mage::getResourceSingleton ( 'catalogrule/rule' )->applyAllRulesForDateRange ();
		return $this;
	}
	
	public function flushPriceCache() {
		$this->_rulePrices = array ();
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
	 * This gets triggered from some observers on change of the cart and on change of the quote model
	 * @param unknown_type $o
	 */
	public function updateRedemptions($o) {
		// initialize all required data.
		$event = $o->getEvent ();
		$data = $event->getInfo (); // 
		

		$cart = $event->getCart (); // 
		if ($cart) {
			$quote = $cart->getQuote (); // 
		} else {
			$quote = $event->getQuote (); // 
		}
		
		if ($o->hasDate ()) {
			$date = $o->getDate ();
		} else
			$date = null;
		
		if ($o->hasWebsiteId ()) {
			$wId = $o->getWebsiteId ();
		} else
			$wId = null;
		
		$this->updateQuoteCatalogRedemptions ( $quote, $data, $date, $wId );
		
		return $this;
	}
	
	/**
	 * This gets triggered from some observers on change of the cart and on change of the quote model
	 * @param unknown_type $o
	 */
	public function updateRedemptionAfterCartLoad($o) {
		// initialize all required data.
		$event = $o->getEvent ();
		$data = $event->getInfo (); // 
		

		$cart = $event->getCart (); // 
		if ($cart) {
			$quote = $cart->getQuote (); // 
		} else {
			$quote = $event->getQuote (); // 
		}
		
		if ($o->hasDate ()) {
			$date = $o->getDate ();
		} else
			$date = null;
		
		if ($o->hasWebsiteId ()) {
			$wId = $o->getWebsiteId ();
		} else
			$wId = null;
		
		$this->updateQuoteCatalogRedemptions ( $quote, $data, $date, $wId );
		
		return $this;
	}
	
	/**
	 * This function will attempt to update the quote catalog redemptions
	 * data.  This should be triggered when the cart changes.
	 * @param Mage_Sales_Model_Quote $quote
	 * @param array $data[=null]
	 * @param unknown_type $date[=null]
	 * @param int $wId[=null]
	 */
	public function updateQuoteCatalogRedemptions($quote, $data = null, $date = null, $wId = null) {
		
		try {
			if (! $quote)
				return $this;
			
			if ($data) {
				//@nelkaake -a 17/02/11: A save-on-quote event 
				$is_on_quote_save = false;
				$items = $data;
			} else {
				$is_on_quote_save = true;
				$items = $quote->getAllItems ();
			}
			
			if (! is_array ( $items )) {
				$items = array ($items );
			}
			
			$refactorItems = array ();
			foreach ( $items as $key => $itemInfo ) {
				if ($is_on_quote_save) {
					$itemId = $itemInfo->getId ();
					$item = $itemInfo;
				} else {
					$itemId = $key;
					$item = $quote->getItemById ( $itemId );
				}
				
				if (! $itemId || ! $item) {
					continue;
				}
				
				if (! $is_on_quote_save) {
					if (! empty ( $itemInfo ['remove'] ) || (isset ( $itemInfo ['qty'] ) && $itemInfo ['qty'] == '0')) {
						continue;
					}
				}
				
				$product = $item->getProduct ();
				$pId = $product->getId ();
				$storeId = $product->getStoreId ();
				
				if (! $date) {
					$date = Mage::helper ( 'rewards' )->now ();
				}
				
				if ($wId) {
					$wId = Mage::app ()->getStore ( $storeId )->getWebsiteId ();
				}
				
				$gId = Mage::getSingleton ( 'customer/session' )->getCustomerGroupId ();
				if ($gId !== 0 && empty ( $gId )) {
					$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
				}
				
				if ($is_on_quote_save) {
					$qty = $item->getQty ();
				} else {
					$qty = isset ( $itemInfo ['qty'] ) ? ( float ) $itemInfo ['qty'] : false;
				}
				
				// Since the cart has changed, reset our before-redemptions value and let Sweet Tooth recalculate the discount amount.
				Mage::getSingleton ( 'rewards/redeem' )->resetBeforeDiscount ( $item );
				
				$refactorItems [] = $item;
			}
			
			Mage::getSingleton ( 'rewards/redeem' )->refactorRedemptions ( $refactorItems );
		
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			Mage::helper ( 'rewards' )->log ( $e->getMessage () );
			Mage::helper ( 'rewards' )->log ( $e );
			die ( $e->getMessage () );
		}
	}
	
	/**
	 * @deprecated
	 * @param unknown_type $o
	 */
	public function updateGrandTotal($o) {
		return $this;
	}
	
	/**
	 * Gets triggered on the  event to append points information
        Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item'=>$result, 'product'=>$product));
	 * @param unknown_type $o
	 */
	public function appendPointsQuoteAfterAdd($o) {
		$item = $o->getEvent ()->getQuoteItem ();
		$product = $o->getEvent ()->getProduct ();
		//@nelkaake -a 17/02/11: Use the generic request.  
		$request = Mage::app ()->getRequest ();
		
		if (! $request || ! $product || ! $item)
			return $this;
		
		if ($item->getParentItem ()) {
			$item = $item->getParentItem ();
		}
		
		$apply_rule_id = $request->getParam ( 'redemption_rule' );
		$apply_rule_uses = $request->getParam ( 'redemption_uses' );
		$qty = $request->getParam ( 'qty' );
		
		try {
			Mage::getSingleton ( 'rewards/catalogrule_saver' )->appendPointsToQuote ( $product, $apply_rule_id, $apply_rule_uses, $qty, $item );
		} catch ( Exception $e ) {
			Mage::helper ( 'rewards' )->notice ( $e->getMessage () );
			Mage::logException ( $e );
			Mage::getSingleton ( 'core/session' )->addError ( Mage::helper ( 'rewards' )->__ ( "An error occured trying to apply the redemption while adding the product to your cart: " ) . $e->getMessage () );
		}
		
		return $this;
	
	}
	
	/**
	 * Gets triggered on the checkout_cart_add_product_complete event to append points information
	 * @see Mage::dispatchEvent('checkout_cart_add_product_complete', array('product'=>$product, 'request'=>$this->getRequest()));
	 * @param unknown_type $o
	 */
	public function updateOnProductAddComplete($o) {
		$product = $o->getEvent ()->getProduct ();
		$request = $o->getEvent ()->getRequest ();
		
		$this->_getQuote ()->setTotalsCollectedFlag ( false )->collectTotals ();
		return $this;
	}
	
	/**
	 * Fetches the rewards session model
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	private function _getRewardsSession() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * @deprecated use TBT_Rewards_Model_Catalogrule_Saver instead
	 * @param unknown_type $date
	 * @param unknown_type $wId
	 * @param unknown_type $gId
	 * @param unknown_type $pId
	 * @param unknown_type $item
	 * @param unknown_type $apply_rule_id
	 * @param unknown_type $qty
	 * @param unknown_type $adjustQty
	 * @param unknown_type $uses
	 */
	private function updateRedeemedPointsHash($date, $wId, $gId, $pId, $item, $apply_rule_id, $qty, $adjustQty = true, $uses = 1) {
		Mage::getSingleton ( 'rewards/catalogrule_saver' )->updateRedeemedPointsHash ( $date, $wId, $gId, $pId, $item, $apply_rule_id, $qty, $adjustQty, $uses );
		return $this;
	}
	
	/**
	 * Mage::dispatchEvent('checkout_cart_add_product_complete', array('product'=>$product, 'request'=>$this->getRequest()));
	 * @deprecated
	 */
	public function appendPointsQuote($o) {
		try {
			// initialize all required data.
			$product = $o->getEvent ()->getProduct ();
			$request = $o->getEvent ()->getRequest ();
			//@nelkaake Added on Saturday September 4, 2010: 
			if (! $request || ! $product)
				return $this;
			
			$apply_rule_id = $request->getParam ( 'redemption_rule' );
			$apply_rule_uses = $request->getParam ( 'redemption_uses' );
			//@nelkaake Added on Saturday September 4, 2010:  
			if (! $apply_rule_uses)
				$apply_rule_uses = 0;
			if (! $apply_rule_id)
				return $this;
			
			$qty = $request->getParam ( 'qty' );
			if (empty ( $qty ))
				$qty = 1;
			
			$pId = $product->getId ();
			$storeId = $product->getStoreId ();
			
			$item = $this->_getQuote ()->getItemByProduct ( $product );
			
			if ($o->hasDate ()) {
				$date = $o->getDate ();
			} else {
				$date = Mage::helper ( 'rewards' )->now ();
			}
			
			if ($o->hasWebsiteId ()) {
				$wId = $o->getWebsiteId ();
			} else {
				$wId = Mage::app ()->getStore ( $storeId )->getWebsiteId ();
			}
			
			$gId = Mage::getSingleton ( 'customer/session' )->getCustomerGroupId ();
			if ($gId !== 0 && empty ( $gId )) {
				$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
			}
			
			if ($item) {
				// 1. Validate rule
				if (empty ( $apply_rule_id ) && $apply_rule_id != '0') {
					// No new rule applied, so no need to adjust redeemed points set.
					Mage::getSingleton ( 'rewards/redeem' )->refactorRedemptions ( $item );
					return $this;
				}
				
				$this->updateRedeemedPointsHash ( $date, $wId, $gId, $pId, $item, $apply_rule_id, $qty, true, $apply_rule_uses );
				
				Mage::getSingleton ( 'rewards/redeem' )->refactorRedemptions ( $item );
			
			}
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'core/session' )->addError ( Mage::helper ( 'rewards' )->__ ( "An error occured trying to apply the redemption while adding the product to your cart: " ) . $e->getMessage () );
		}
	}
}
