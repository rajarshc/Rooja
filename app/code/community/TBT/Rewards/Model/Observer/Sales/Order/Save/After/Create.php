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
 * Observer Sales Order Save After Create
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Observer_Sales_Order_Save_After_Create {
	
	public function __construct() {
	
	}
	
	/**
	 * Applies the special price percentage discount
	 * @param   Varien_Event_Observer $observer
	 * @return  Xyz_Catalog_Model_Price_Observer
	 */
	public function createPointsTransfers($observer) {
		$event = $observer->getEvent ();
		$order = $event->getOrder ();
		
		if (! $order) {
			return $this;
		}
		
		$catalog_transfers = Mage::getSingleton ( 'rewards/observer_sales_catalogtransfers' );
		if ($order->getIncrementId () == $catalog_transfers->getIncrementId ()) {
			foreach ( $catalog_transfers->getAllEarnedPoints () as $earned_point_totals ) {
				if (! $earned_point_totals) {
					continue;
				}
				
				foreach ( $earned_point_totals as $transfer_points ) {
					$transfer_points = ( array ) $transfer_points;
					try {
						$is_transfer_successful = Mage::helper ( 'rewards/transfer' )->transferOrderPoints ( $transfer_points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $transfer_points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY], $transfer_points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID], $order->getId (), $transfer_points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID] );
					} catch ( Exception $ex ) {
						throw new Mage_Core_Exception ( $ex->getMessage () );
					}
					
				/* WDCA: I don't think we want these messages to appear, but we might find in the
                     * future that we actually do, so I left it in. */
				//                    if ($is_transfer_successful) {
				//                        Mage::getSingleton('core/session')->addSuccess(
				//                            Mage::helper('rewards')->__('Successfully cancelled transfer ID #'. $transfer->getId())
				//                        );
				//                    }
				//                    else {
				//                        Mage::getSingleton('core/session')->addError(
				//                            Mage::helper('rewards')->__('Could not successfully revoke points associated with cancelled order.')
				//                        );
				//                    }
				}
			}
			$catalog_transfers->clearEarnedPoints ();
			
			foreach ( $catalog_transfers->getAllRedeemedPoints () as $redeemed_point_totals ) {
				if (! $redeemed_point_totals) {
					continue;
				}
				
				foreach ( $redeemed_point_totals as $transfer_points ) {
					$transfer_points = ( array ) $transfer_points;
					try {
						$is_transfer_successful = Mage::helper ( 'rewards/transfer' )->transferOrderPoints ( $transfer_points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $transfer_points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY] * - 1, $transfer_points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID], $order->getId (), $transfer_points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID] );
					} catch ( Exception $ex ) {
						throw new Mage_Core_Exception ( $ex->getMessage () );
					}
				}
			}
			$catalog_transfers->clearRedeemedPoints ();
			
			$catalog_transfers->clearIncrementId ();
		}
		
		$cart_transfers = Mage::getSingleton ( 'rewards/observer_sales_carttransfers' );
		if ($order->getIncrementId () == $cart_transfers->getIncrementId ()) {
			foreach ( $cart_transfers->getAllCartPoints () as $cart_points ) {
				if (! $cart_points) {
					continue;
				}
				
				try {
					$is_transfer_successful = Mage::helper ( 'rewards/transfer' )->transferOrderPoints ( $cart_points ['amount'], $cart_points ['currency'], $order->getId (), $cart_points ['rule_id'] );
				} catch ( Exception $ex ) {
					throw new Mage_Core_Exception ( $ex->getMessage () );
				}
			}
			
			foreach ( $cart_transfers->getRedemptionRuleIds () as $rule_id ) {
				try {
					$points = $this->_getRewardsSession ()->calculateCartPoints ( $rule_id, $order->getAllItems (), true );
				} catch ( Exception $e ) {
					die ( $e->getMessage () );
				}
				
				if (is_array ( $points )) {
					try {
						$is_transfer_successful = Mage::helper ( 'rewards/transfer' )->transferOrderPoints ( $points ['amount'], $points ['currency'], $order->getId (), $rule_id );
					} catch ( Exception $ex ) {
						throw new Mage_Core_Exception ( $ex->getMessage () );
					}
				}
			}
			
			$earned_points_string = Mage::getModel ( 'rewards/points' )->set ( $order->getTotalEarnedPoints () );
			$redeemed_points_string = Mage::getModel ( 'rewards/points' )->set ( $order->getTotalSpentPoints () );
			
			if ($order->hasPointsEarning ()) {
				if ($this->_getRewardsSession ()->isAdminMode ()) {
					Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'The customer was credited %s for the order.', $earned_points_string ) );
				} else {
					Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'You earned %s on the order you just placed.', $earned_points_string ) );
				}
			}
			if ($order->hasPointsSpending ()) {
				if ($this->_getRewardsSession ()->isAdminMode ()) {
					Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'The customer was deducted %s for the order.', $redeemed_points_string ) );
				} else {
					Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'You spent %s on the order you just placed.', $redeemed_points_string ) );
				}
			}
			
            if ($order->hasPointsEarningOrSpending()) {
                if ($order->isInitialTransferStatus(TBT_Rewards_Model_Transfer_Status::STATUS_PENDING)) {
                    if ($this->_getRewardsSession()->isAdminMode()) {
                        Mage::getSingleton('core/session')->addNotice(Mage::helper('rewards')->__("The customer's point transactions may be in the 'pending' status."));
                    } else {
                        Mage::getSingleton('core/session')->addNotice(Mage::helper('rewards')->__('Your point transactions are currently pending and will be approved when we finish processing your order.'));
                    }
                } elseif ($order->isInitialTransferStatus(TBT_Rewards_Model_Transfer_Status::STATUS_ON_HOLD)) {
                    if ($this->_getRewardsSession()->isAdminMode()) {
                        Mage::getSingleton('core/session')->addNotice(Mage::helper('rewards')->__("The customer's point transactions may be in the 'on hold' status."));
                    } else {
                        Mage::getSingleton('core/session')->addNotice(Mage::helper('rewards')->__('Your point transactions are currently on hold, they can be approved after we process your order.'));
                    }
                }
            }
			
			$cart_transfers->clearIncrementId ();
			$cart_transfers->clearCartPoints ();
			//@nelkaake Monday March 29, 2010 03:55:05 AM : Reset points spending
			Mage::getSingleton ( 'rewards/session' )->setPointsSpending ( 0 );
		}
		
		return $this;
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
