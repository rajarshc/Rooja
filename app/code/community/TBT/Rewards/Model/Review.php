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
 * Review
 * 
 * @deprecated this used to be a rewrite but now Sweet Tooth uses an Observer for the Review model
 * @see TBT_Rewards_Model_Review_Observer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Review extends Mage_Review_Model_Review {
	protected $oldData = null; //This is used to store data from the model to compare to future versions
	

	protected function _construct() {
		parent::_construct ();
	}
	
	/**
	 * Processing object before save data
	 *
	 * @return Mage_Core_Model_Abstract
	 */
	protected function _afterLoad() {
		//Before you save, pass all current data into a dummy model for comparison later. 
		$this->oldData = $this->getData ();
		return parent::_afterLoad ();
	}
	
	/**
	 * Processing object after save data
	 *
	 * @return Mage_Core_Model_Abstract
	 */
	protected function _afterSave() {
		//If the review becomes approved, approve all associated pending tranfser
		if ($this->oldData ['status_id'] == self::STATUS_PENDING && $this->isApproved ()) {
			$this->approvePendingTransfers ();
		
		//If the review is new (hence not having an id before) get applicable rules, 
		//and create a pending transfer for each one
		} elseif ($this->getReviewId () && ! isset ( $this->oldData ['review_id'] )) {
			
			$this->ifNewReview ();
		}
		return parent::_afterSave ();
	}
	
	/**
	 * Returns an array outlining the number of points they will receive for making a review
	 *
	 * @return array
	 */
	public function getPredictPoints() {
		Varien_Profiler::start ( "TBT_Rewards:: Predict Review Points" );
		$ruleCollection = Mage::getSingleton ( 'rewards/special_validator' )->getApplicableRulesOnReview ();
		$predict_array = array ();
		foreach ( $ruleCollection as $rule ) {
			$predict_array [$rule->getPointsCurrencyId ()] = $rule->getPointsAmount ();
		}
		
		Varien_Profiler::stop ( "TBT_Rewards:: Predict Review Points" );
		return $predict_array;
	}
	
	/**
	 * Approves all associated transfers with a pending status.
	 */
	protected function approvePendingTransfers() {
		foreach ( $this->getAssociatedTransfers () as $transfer ) {
			if ($transfer->getStatus () == TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_EVENT) {
				//Move the transfer status from pending to approved, and save it!
				$transfer->setStatus ( TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_EVENT, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED );
				$transfer->save ();
			}
		}
	}
	
	/**
	 * Loops through each Special rule. If it applies, create a new pending transfer.
	 */
	private function ifNewReview() {
		$ruleCollection = Mage::getSingleton ( 'rewards/special_validator' )->getApplicableRulesOnReview ();
		foreach ( $ruleCollection as $rule ) {
			$is_transfer_successful = $this->createPendingTransfer ( $rule );
			
			if ($is_transfer_successful) {
				//Alert the customer on the distributed points  
				Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'You will receive %s upon approval of this review', (string)Mage::getModel ( 'rewards/points' )->set ( $rule ) ) );
			}
		}
	}
	
	/**
	 * Returns a collection of all transfers associated with this review
	 *
	 * @return array(TBT_Rewards_Model_Transfer) 		: A collection of all reviews associated with this review
	 */
	protected function getAssociatedTransfers() {
		return Mage::getModel ( 'rewards/transfer' )->getTransfersAssociatedWithReview ( $this->getId () );
	}
	
	/**
	 * Creates a new transfer with a pending status using the rule information
	 *
	 * @param TBT_Rewards_Model_Special $rule
	 */
	private function createPendingTransfer($rule) {
		try {
			$is_transfer_successful = Mage::helper ( 'rewards/transfer' )->transferReviewPoints ( $rule->getPointsAmount (), $rule->getPointsCurrencyId (), $this->getId (), $rule->getId () );
		} catch ( Exception $ex ) {
			Mage::getSingleton ( 'core/session' )->addError ( $ex->getMessage () );
		}
		
		return $is_transfer_successful;
	}
	
	/**
	 * Returns true if it's Pending!
	 * @return boolean
	 */
	public function isPending() {
		return ($this->getStatusId () == self::STATUS_PENDING);
	}
	
	/**
	 * Returns true if it's Approved!
	 * @return boolean
	 */
	public function isApproved() {
		return ($this->getStatusId () == self::STATUS_APPROVED);
	}
	
	/**
	 * Returns true if it's not Approved!
	 * @return boolean
	 */
	public function isNotApproved() {
		return ($this->getStatusId () == self::STATUS_NOT_APPROVED);
	}

}