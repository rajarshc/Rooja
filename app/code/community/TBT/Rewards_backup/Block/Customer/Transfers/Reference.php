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
 * Customer Transfers
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Customer_Transfers_Reference extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct ();
		$this->_controller = 'customer';
		$this->_blockGroup = 'rewards';
		$this->setTemplate ( 'rewards/customer/transfers/reference.phtml' );
	}
	
	protected function _prepareLayout() {
		parent::_prepareLayout ();
	}
	
	/**
	 * Returns a rewards customer
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	public function getCustomer() {
	    $customer = Mage::registry ( 'customer' );
		return Mage::getModel('rewards/customer')->getRewardsCustomer($customer);
	}
	
	public function _getTransferSummary() {
		$cust = $this->getCustomer ();
		$transfers = $cust->getTransfers ()->setOrder ( 'creation_ts', 'DESC' );
		return $transfers;
	}
	
	/**
	 * Fetches an order ID from a given transfer id
	 * @see TBT_Rewards_Model_Transfer, TBT_Rewards_Model_Transfer_Reference
	 *
	 * @param int $transferId
	 * @return int
	 */
	public function getAssociatedOrder($transferId) {
		$ref = Mage::getModel ( 'rewards/transfer_reference' )->loadReferenceByTransferId ( $transferId );
		
		if ($ref->getReferenceType == TBT_Rewards_Model_Transfer_Reference::REFERENCE_ORDER) {
			return $ref->getReferenceId ();
		} else {
			return null;
		}
	}
	
	/**
	 * Fetches a review ID from a given transfer id
	 * @see TBT_Rewards_Model_Transfer, TBT_Rewards_Model_Transfer_Reference
	 *
	 * @param int $transferId
	 * @return int
	 */
	public function getAssociatedReview($transferId) {
		$ref = Mage::getModel ( 'rewards/transfer_reference' )->loadReferenceByTransferId ( $transferId );
		
		if ($ref->getReferenceType == TBT_Rewards_Model_Transfer_Reference::REFERENCE_REVIEW) {
			return $ref->getReferenceId ();
		} else {
			return null;
		}
	}
	
	/**
	 * Fetches a rating ID from a given transfer id
	 * @see TBT_Rewards_Model_Transfer, TBT_Rewards_Model_Transfer_Reference
	 *
	 * @param int $transferId
	 * @return int
	 */
	public function getAssociatedRating($transferId) {
		$ref = Mage::getModel ( 'rewards/transfer_reference' )->loadReferenceByTransferId ( $transferId );
		
		if ($ref->getReferenceType == TBT_Rewards_Model_Transfer_Reference::REFERENCE_RATING) {
			return $ref->getReferenceId ();
		} else {
			return null;
		}
	}
	
	/**
	 * Fetches a poll ID from a given transfer id
	 * @see TBT_Rewards_Model_Transfer, TBT_Rewards_Model_Transfer_Reference
	 *
	 * @param int $transferId
	 * @return int
	 */
	public function getAssociatedPoll($transferId) {
		$ref = Mage::getModel ( 'rewards/transfer_reference' )->loadReferenceByTransferId ( $transferId );
		
		if ($ref->getReferenceType == TBT_Rewards_Model_Transfer_Reference::REFERENCE_POLL) {
			return $ref->getReferenceId ();
		} else {
			return null;
		}
	}
	
	/**
	 * Fetches an order ID from a given transfer id
	 * @see TBT_Rewards_Model_Transfer, TBT_Rewards_Model_Transfer_Reference
	 *
	 * @param int $transferId
	 * @return int
	 */
	public function getAssociatedTag($transferId) {
		$ref = Mage::getModel ( 'rewards/transfer_reference' )->loadReferenceByTransferId ( $transferId );
		
		if ($ref->getReferenceType == TBT_Rewards_Model_Transfer_Reference::REFERENCE_TAG) {
			return $ref->getReferenceId ();
		} else {
			return null;
		}
	}
	
	/**
	 * Fetches an order ID from a given transfer id
	 * @see TBT_Rewards_Model_Transfer, TBT_Rewards_Model_Transfer_Reference
	 *
	 * @param int $transferId
	 * @return int
	 */
	public function getAssociatedFriend($transferId) {
		$ref = Mage::getModel ( 'rewards/transfer_reference' )->loadReferenceByTransferId ( $transferId );
		
		if ($ref->getReferenceType == TBT_Rewards_Model_Transfer_Reference::REFERENCE_FROM_FRIEND || $ref->getReferenceType () == TBT_Rewards_Model_Transfer_Reference::REFERENCE_TO_FRIEND) {
			return $ref->getReferenceId ();
		} else {
			return null;
		}
	}
	
	public function getStatusCaption($status_id) {
		return Mage::getModel ( 'rewards/transfer_status' )->getStatusCaption ( $status_id );
	}
	
	public function getPointsString($amount, $currency) {
		$str = Mage::helper ( 'rewards' )->getPointsString ( array ($currency => $amount ) );
		return $str;
	}
	
	public function getCustomerEmail($customer_id) {
		return Mage::getModel ( 'rewards/customer' )->load ( $customer_id )->getEmail ();
	}
	
	public function getOrderUrl($order_id) {
		return $this->getUrl ( 'sales/order/view', array ('order_id' => $order_id ) );
	}
	
	public function getReviewUrl($review_id) {
		return $this->getUrl ( 'review/customer/view', array ('id' => $review_id ) );
	}

}