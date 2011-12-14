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
 * Rewards Order model
 *
 * Supported events:
 * sales_order_load_after
 * sales_order_save_before
 * sales_order_save_after
 * sales_order_delete_before
 * sales_order_delete_after
 *
 * @author      WDCA Team (http://www.wdca.ca)
 */
class TBT_Rewards_Model_Sales_Order extends Mage_Sales_Model_Order {
	protected $points_earned = null;
	protected $points_spent = null;
	/**
	 * Loads in a order and returns a points order
	 *
	 * @param Mage_Sales_Model_Order $product
	 * @return TBT_Rewards_Model_Sales_Order
	 */
	public static function wrap(Mage_Sales_Model_Order &$order) {
		return $order;
	}
	
	/**
	 * Fetches a sum of points earned on this order
	 *
	 * @return array
	 */
	public function getTotalEarnedPoints() {
		if ($this->points_earned == null) {
			$point_sums = $this->getAssociatedTransfers ()->selectOnlyPosTransfers ()->sumPoints ();
			$this->points_earned = array ();
			foreach ( $point_sums as $points ) {
				$this->points_earned [$points->getCurrencyId ()] = ( int ) $points->getPointsCount ();
			}
		}
		return $this->points_earned;
	}
	
	/**
	 * Fetches a sum of points earned on this order as a string
	 *
	 * @return String
	 */
	public function getTotalEarnedPointsAsString() {
		$m = Mage::getModel ( 'rewards/points' )->set ( $this->getTotalEarnedPoints () );
		return ( string ) $m;
	}
	
	/**
	 *
	 * @return boolean true if the order has any spent points
	 */
	public function getHasSpentPoints() {
		$pts = $this->getTotalSpentPoints ();
		foreach ( $pts as $pt_entry ) {
			if ($pt_entry > 0)
				return true;
		}
		return false;
	}
	/**
	 * Fetches a sum of points earned on this order, but only selects the first 'points currency' and 
	 * returns a single integer of the points amount.
	 *
	 * @return integer 0 if no points exist or amount is zero.
	 */
	public function getTotalSpentPointsSimple() {
		$pts = $this->getTotalSpentPoints ();
		foreach ( $pts as $pt_entry ) {
			return $pt_entry;
		}
		return 0;
	}
	/**
	 * Fetches a sum of points earned on this order
	 *
	 * @return array
	 */
	public function getTotalSpentPoints() {
		if ($this->points_spent == null) {
			$point_sums = $this->getAssociatedTransfers ()->selectOnlyNegTransfers ()->sumPoints ();
			$this->points_spent = array ();
			foreach ( $point_sums as $points ) {
				$this->points_spent [$points->getCurrencyId ()] = ( int ) $points->getPointsCount ();
				if ($this->points_spent [$points->getCurrencyId ()] < 0) {
					$this->points_spent [$points->getCurrencyId ()] *= - 1;
				}
			}
		}
		return $this->points_spent;
	}
	/**
	 * Fetches a sum of points spent on this order as a string
	 *
	 * @return String
	 */
	public function getTotalSpentPointsAsString() {
		$m = Mage::getModel ( 'rewards/points' )->set ( $this->getTotalSpentPoints () );
		return ( string ) $m;
	}
	
	/**
	 * Fetches the transfer collection for all transfers associated with this order.
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function getAssociatedTransfers() {
		$transferCollection = Mage::getModel ( 'rewards/transfer' )->getCollection ();
		$transferCollection->addFieldToFilter ( 'reference_id', $this->getId () );
		$transferCollection->addFieldToFilter ( 'reference_type', TBT_Rewards_Model_Transfer_Reference::REFERENCE_ORDER );
		return $transferCollection;
	}
	
	public function hasPointsSpending() {
		return sizeof ( $this->getTotalSpentPoints () ) > 0;
	}
	
	public function hasPointsEarning() {
		return sizeof ( $this->getTotalEarnedPoints () ) > 0;
	}
	
	/**
	 * True if points are being earned or being spent on this cart (not exclusively).
	 *
	 * @return boolean
	 */
	public function hasPointsEarningOrSpending() {
		return $this->hasPointsSpending () || $this->hasPointsEarning ();
	}
	
        
        /**
         * return true if the order initial transfer status if of $status 
         * TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_EVENT
         * TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED
         * TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_APPROVAL
         * TBT_Rewards_Model_Transfer_Status::STATUS_CANCELLED
         * 
         * @param TBT_Rewards_Model_Transfer_Status::CONST $status
         * @return bool 
         */
	public function isInitialTransferStatus($status) {
            return $status == Mage::helper('rewards/config')->getInitialTransferStatusAfterOrder();
        }
        
	/**
	 * 
	 *
	 * @return TBT_Rewards_Model_Observer_Sales_Catalogtransfers
	 */
	private function _getCatalogTransfersSingleton() {
		return Mage::getSingleton ( 'rewards/observer_sales_catalogtransfers' );
	}
	
	/**
	 * 
	 *
	 * @return TBT_Rewards_Model_Observer_Sales_Carttransfers
	 */
	private function _getCartTransfersSingleton() {
		return Mage::getSingleton ( 'rewards/observer_sales_carttransfers' );
	}
	
	public function prepareCartPointsTransfers() {
		$order = &$this;
		$order_items = $order->getAllItems ();
		$is_login_notice_given = false;
		
		$cart_transfers = $this->_getCartTransfersSingleton ();
		
		foreach ( Mage::helper ( 'rewards/transfer' )->getCartRewardsRuleIds ( $order ) as $rule_id ) {
			if (! $rule_id) {
				continue;
			}
			
			if ($this->_getRewardsSession ()->isCustomerLoggedIn ()) {
				$points = $this->_getRewardsSession ()->calculateCartPoints ( $rule_id, $order_items, false );
				if ($points) {
					if ($points ['amount']) {
						$cart_transfers->addCartPoints ( $points );
					}
				}
			
		//TODO:Fix for bug 108, will be moved for abstraction in the rewards session
			} else if ($this->_getRewardsSession ()->isAdminMode ()) {
				$points = $this->_getRewardsSession ()->calculateCartPoints ( $rule_id, $order_items, false );
				
				if ($points) {
					if ($points ['amount']) {
						$cart_transfers->addCartPoints ( $points );
					}
				}
			} else {
				if (! $is_login_notice_given) {
					Mage::getSingleton ( 'core/session' )->addNotice ( Mage::helper ( 'rewards' )->__ ( 'If you had created a customer account, you would have earned points for this order.' ) );
					$is_login_notice_given = true;
				}
			}
		}
		
		$cart_transfers->setIncrementId ( $order->getIncrementId () );
	}
	
	public function getTotalBaseTax($cart_rule_id = null) {
		$tax_total = 0;
		foreach ( $this->getAllItems () as $item ) {
			$item_applied = Mage::getModel ( 'rewards/salesrule_list_item_applied' )->initItem ( $item );
			
			if (! $item->getId ())
				continue;
			if ($item->getParentItem ())
				continue;
			if (! empty ( $cart_rule_id ) && ! $item_applied->hasRuleId ( $cart_rule_id ))
				continue;
			$tax_total += $item->getBaseTaxAmount ();
		}
		return $tax_total;
	}
	public function getTotalBaseShipping($cart_rule_id = null) {
		$total_shipping = 0;
		foreach ( $this->getAllItems () as $item ) {
			$item_applied = Mage::getModel ( 'rewards/salesrule_list_item_applied' )->initItem ( $item );
			
			if (! $item->getId ())
				continue;
			if ($item->getParentItem ())
				continue;
			if (! empty ( $cart_rule_id ) && ! $item_applied->hasRuleId ( $cart_rule_id ))
				continue;
			$shipaddr = $item->getOrder ()->getShippingAddress ();
			//@nelkaake Thursday April 22, 2010 12:23:04 PM : Virtual items don't have a shipping address model:
			$total_shipping = empty ( $shipaddr ) ? 0 : $shipaddr->getBaseShippingAmount ();
		}
		return $total_shipping;
	}
	
	public function getTotalBaseAdditional($rule) {
		$total_additional = 0;
		if ($rule->getApplyToShipping ()) {
			$total_additional += $this->getTotalBaseShipping ( $rule->getId () );
		}
		if (Mage::helper ( 'tax' )->discountTax () && ! Mage::helper ( 'tax' )->applyTaxAfterDiscount ()) {
			$total_additional += $this->getTotalBaseTax ();
		}
		return $total_additional;
	}
	
	public function getCatalogDiscounts() {
		return 0;
	}
	
	/**
	 * Fetches the rewards session
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSession() {
		return Mage::getSingleton ( 'rewards/session' );
	}
}
