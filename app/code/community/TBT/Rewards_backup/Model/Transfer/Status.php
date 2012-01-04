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
 * Transfer Status
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Transfer_Status extends Varien_Object {
	// status values less than 1 means that transfer is ignored in
	// customer point calculations.
	const STATUS_CANCELLED = 1;
	const STATUS_PENDING_APPROVAL = 3;
	const STATUS_PENDING_EVENT = 4;
	const STATUS_APPROVED = 5;
	const STATUS_PENDING_TIME = 6;
	
	public function getOptionArray() {
		return array (
		    self::STATUS_APPROVED => Mage::helper ( 'rewards' )->__ ( 'Approved' ),
		    self::STATUS_CANCELLED => Mage::helper ( 'rewards' )->__ ( 'Cancelled' ),
		    self::STATUS_PENDING_APPROVAL => Mage::helper ( 'rewards' )->__ ( 'Pending: Approval' ),
		    self::STATUS_PENDING_EVENT => Mage::helper ( 'rewards' )->__ ( 'Pending: Event' ),
		    self::STATUS_PENDING_TIME => Mage::helper ( 'rewards' )->__ ( 'Pending: Time' )
		);
	}
	
	/**
	 * Returns an array of options for the initial statuses to be displayed
	 * in a renderable SELECT block.
	 *
	 * @return unknown
	 */
	public function getInitialStatusOptionArray() {
		$statuses = $this->getInitialStatuses ();
		$options = array ();
		foreach ( $statuses as $status_id => $status_caption ) {
			$options [] = array ('value' => $status_id, 'label' => $status_caption );
		}
		return $options;
	}
	
	public function getCountableStatuses() {
		return array (
		    self::STATUS_APPROVED => Mage::helper ( 'rewards' )->__ ( 'Approved' )
		);
	}
	
	public function getCountableStatusIds() {
		return array_keys ( $this->getCountableStatuses () );
	}
	
	public function getAvailableNextStatuses($current_status) {
		switch ($current_status) {
			case self::STATUS_CANCELLED:
				return array (
				    self::STATUS_CANCELLED
				);
			case self::STATUS_PENDING_APPROVAL:
				return array (
				    self::STATUS_PENDING_APPROVAL,
				    self::STATUS_CANCELLED,
				    self::STATUS_APPROVED
				);
			case self::STATUS_PENDING_EVENT:
				return array (
				    self::STATUS_PENDING_EVENT,
				    self::STATUS_CANCELLED,
				    self::STATUS_APPROVED,
				    self::STATUS_PENDING_APPROVAL,
				    self::STATUS_PENDING_TIME
				);
			case self::STATUS_PENDING_TIME:
			    return array(
			        self::STATUS_PENDING_TIME,
			        self::STATUS_CANCELLED,
			        self::STATUS_APPROVED,
			        self::STATUS_PENDING_APPROVAL,
			        self::STATUS_PENDING_EVENT
			    );
			case self::STATUS_APPROVED:
				return array (
				    self::STATUS_APPROVED
				);
			default:
				return array_keys (
				    $this->getInitialStatuses()
				);
		}
	}
	
	public function getAvailStatuses($current_status) {
		$ids = $this->getAvailableNextStatuses ( $current_status );
		$all = $this->getOptionArray ();
		$ret = array ();
		foreach ( $ids as $id ) {
			$ret [$id] = $all [$id];
		}
		return $ret;
	}
	
	public function getInitialStatuses() {
		return array (
		    self::STATUS_APPROVED => Mage::helper ( 'rewards' )->__ ( 'Approved' ),
		    self::STATUS_PENDING_APPROVAL => Mage::helper ( 'rewards' )->__ ( 'Pending: Approval' ),
		    self::STATUS_PENDING_EVENT => Mage::helper ( 'rewards' )->__ ( 'Pending: Event' ),
		    self::STATUS_PENDING_TIME => Mage::helper ( 'rewards' )->__ ( 'Pending: Time' )
            /* TODO WDCA: add status-REVOKED here, so that it is intuitive for admin to select it from
             *   the drop-down list when creating transfers, then dynamically turn it into a status-APPROVED
             *   transfer with reason-REVOKED
             */
        );
	}
	
	public function genSelectableStatuses() {
		return array (
		    array (
		    	'label' => Mage::helper ( 'rewards' )->__ ( 'Approved' ),
		    	'value' => self::STATUS_APPROVED ),
		    array (
		    	'label' => Mage::helper ( 'rewards' )->__ ( 'Cancelled' ),
		    	'value' => self::STATUS_CANCELLED ),
		    array (
		    	'label' => Mage::helper ( 'rewards' )->__ ( 'Pending: Approval' ),
		    	'value' => self::STATUS_PENDING_APPROVAL ),
		    array (
		    	'label' => Mage::helper ( 'rewards' )->__ ( 'Pending: Event' ),
		    	'value' => self::STATUS_PENDING_EVENT ),
		    array (
		        'label' => Mage::helper ( 'rewards' )->__ ( 'Pending: Time' ),
		        'value' => self::STATUS_PENDING_TIME
		    )
		);
	}
	
	public function canAdjustQty($current_status) {
		switch ($current_status) {
			case self::STATUS_CANCELLED :
				return false;
			case self::STATUS_APPROVED :
				return false;
			case self::STATUS_PENDING_APPROVAL :
				return true;
			case self::STATUS_PENDING_EVENT :
				return true;
			default :
				return true;
		}
	}
	
	/**
	 * You pretty much can't change an associated transfer-order.
	 */
	public function canAdjustReference($current_status) {
		return false;
	}
	
	/**
	 * You can always change the comments.
	 */
	public function canAdjustComments($current_status) {
		return true;
	}
	
	/**
	 * You can adjust the status to another applicable status only
	 */
	public function canAdjustStatus($current_status, $next_status) {
		$availStat = $this->getAvailableNextStatuses ( $current_status );
		return in_array ( $next_status, $availStat );
	}
	
	/**
	 * You can never change customer.  Void this transfer and make a new
	 * transfer for the other customer instead.
	 */
	public function canAdjustCustomer($current_status) {
		return false;
	}
	
	/**
	 * You can't change the reason it was distributed in the first place.  That
	 * would otherwise defeat the purpose of tracking the reason.  Modify
	 * the comments if there's something you need to comment about to the
	 * customer.
	 */
	public function canAdjustReason($current_status) {
		return false;
	}
	
	public function getStatusCaption($id) {
		$statuses = $this->getOptionArray ();
		
		if (array_key_exists($id, $statuses)) {
			return $statuses [$id];
		} else {
			return null;
		}
	}
	
	/**
	 * True if you're trying to go from approved to cancelled.
	 *
	 * @param integer $oldStatus
	 * @param integer $newStatus
	 * @return boolean
	 */
	public function isFromApprovedToCancelled($oldStatus, $newStatus) {
		if ($oldStatus == self::STATUS_APPROVED && $newStatus == self::STATUS_CANCELLED) {
			return true;
		}
		return false;
	}
	
	
	
	/**
	 * @deprecated use STATUS_PENDING_APPROVAL instead
	 */
	const STATUS_ON_HOLD = self::STATUS_PENDING_APPROVAL;
	
	/**
	 * @deprecated use STATUS_PENDING_EVENT instead
	 */
	const STATUS_PENDING = self::STATUS_PENDING_EVENT;

}