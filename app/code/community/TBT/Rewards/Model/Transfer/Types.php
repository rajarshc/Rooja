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
 * Transfer Reference
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Transfer_Types extends Mage_Core_Model_Abstract {
	
	protected function _construct() {
		parent::_construct ();
		$this->loadTypeModels ();
	}
	
	///////////// These functions are specific to the singleton: ===========
	

	public function loadTypeModels() {
		if ($this->getHasLoadedTypeModels ())
			return $this; //don't load more than once...
		

		// First load all the referrence models...
		$sms = array ();
		if (null != Mage::getConfig ()->getNode ( 'rewards/transfer/reference' )) {
			$code_nodes = Mage::getConfig ()->getNode ( 'rewards/transfer/reference' )->children ();
			foreach ( $code_nodes as $code => $special ) {
				$class = ( string ) $special;
				$config_model = Mage::getModel ( $class );
				if (! ($config_model instanceof TBT_Rewards_Model_Transfer_Reference_Abstract)) {
					throw new Exception ( "Transfer reference model with code '$code' should extend TBT_Rewards_Model_Transfer_Reference_Abstract but it appears not to." );
				}
				$sms [$code] = $config_model;
			}
		}
		// Save..
		$this->setReferenceTypeModels ( $sms );
		
		// First load all the reason models...
		$sms = array ();
		if (null != Mage::getConfig ()->getNode ( 'rewards/transfer/reason' )) {
			$code_nodes = Mage::getConfig ()->getNode ( 'rewards/transfer/reason' )->children ();
			foreach ( $code_nodes as $code => $special ) {
				$class = ( string ) $special;
				$config_model = Mage::getModel ( $class );
				if (! ($config_model instanceof TBT_Rewards_Model_Transfer_Reason_Abstract)) {
					throw new Exception ( "Transfer reason model with code '$code' should extend TBT_Rewards_Model_Transfer_Reason_Abstract but it appears not to." );
				}
				$sms [$code] = $config_model;
			}
		}
		// Save..
		$this->setReasonTypeModels ( $sms );
		
		$this->setHasLoadedTypeModels ( true );
		return $this;
	}
	
	protected function _defaultClearReferences($transfer) {
		if ($transfer->hasData ( 'order_id' )) {
			$transfer->unsetData ( 'order_id' );
		}
		if ($transfer->hasData ( 'review_id' )) {
			$transfer->unsetData ( 'review_id' );
		}
		if ($transfer->hasData ( 'rating_id' )) {
			$transfer->unsetData ( 'rating_id' );
		}
		
		// To maintain reverse compatibility  (st v1.x) we're leaving this in.  
		// It should never reach here for new transfers instantiated in Sweet Tooth v2.
		if ($transfer->hasData ( 'newsletter_id' )) {
			$transfer->unsetData ( 'newsletter_id' );
		}
		
		if ($transfer->hasData ( 'poll_id' )) {
			$transfer->unsetData ( 'poll_id' );
		}
		if ($transfer->hasData ( 'tag_id' )) {
			$transfer->unsetData ( 'tag_id' );
		}
		if ($transfer->hasData ( 'reference_transfer_id' )) {
			$transfer->unsetData ( 'reference_transfer_id' );
		}
		if ($transfer->hasData ( 'friend_id' )) {
			$transfer->unsetData ( 'friend_id' );
		}
		return $this;
	}
	
	/**
	 * @param TBT_Rewards_Model_Transfer $transfer
	 */
	protected function _defaultTransferBeforeSave(&$transfer) {
		if (! $transfer->getReferenceType () || ! $transfer->getReferenceId ()) {
			if ($transfer->hasData ( 'order_id' )) {
				$transfer->setOrderId ( $transfer->getData ( 'order_id' ) );
			} else if ($transfer->hasData ( 'review_id' )) {
				$transfer->setReviewId ( $transfer->getData ( 'review_id' ) );
			} else if ($transfer->hasData ( 'rating_id' )) {
				$transfer->setRatingId ( $transfer->getData ( 'rating_id' ) );
			} else if ($transfer->hasData ( 'poll_id' )) {
				$transfer->setPollId ( $transfer->getData ( 'poll_id' ) );
			} else if ($transfer->hasData ( 'tag_id' )) {
				$transfer->setTagId ( $transfer->getData ( 'tag_id' ) );
			} else if ($transfer->hasData ( 'reference_transfer_id' )) {
				$transfer->setReferenceTransferId ( $transfer->getData ( 'reference_transfer_id' ) );
			} else if ($transfer->hasData ( 'friend_id' )) {
				$transfer->setReferenceId ( $transfer->getData ( 'friend_id' ) );
			} else if ($transfer->getReferenceType () === TBT_Rewards_Model_Transfer_Reference::REFERENCE_SIGNUP) {
				$transfer->setReferenceId ( - 1 );
			}
		}
		return $this;
	}
	
	protected function _defaultTransferAfterSave(&$transfer) {
		return $this;
	}
	
	/**
	 * @param TBT_Rewards_Model_Transfer $transfer
	 */
	protected function _defaultLoadReferenceInformation(&$transfer) {
		
		if ($transfer->isOrder ()) {
			$transfer->setOrderId ( $transfer->getReferenceId () );
		
		//        } else if ($transfer->isReview()) {
		// To maintain reverse compatibility  (st v1.x) we're leaving this in.  
		// It should never reach here for new transfers instantiated in Sweet Tooth v2.
		//            $transfer->setReviewId($transfer->getReferenceId());
		} else if ($transfer->isRating ()) {
			$transfer->setRatingId ( $transfer->getReferenceId () );
		} else if ($transfer->isPoll ()) {
			$transfer->setPollId ( $transfer->getReferenceId () );
		
		//        } else if ($transfer->isTag()) {
		//            $transfer->setTagId($transfer->getReferenceId());
		} else if ($transfer->isTransfer ()) {
			$transfer->setReferenceTransferId ( $transfer->getReferenceId () );
		} else if ($transfer->isSignup ()) {
			$transfer->setAsSignup ();
		} else if ($transfer->isFriendTransfer ()) {
			if ($transfer->isToFriend ()) {
				$transfer->setToFriendId ( $transfer->getReferenceId () );
			} else if ($transfer->isFromFriend ()) {
				$transfer->setFromFriendId ( $transfer->getReferenceId () );
			} else {
				$transfer->setFriendId ( $transfer->getReferenceId () );
			}
		}
		return $this;
	}
	
	/**
	 * Returns an array of abstract type models (reason and referrence models)   
	 */
	public function getTypeModels() {
		$all_type_models = array ();
		foreach ( $this->getReferenceTypeModels () as $code => $scm ) {
			$all_type_models [] = $scm;
		}
		foreach ( $this->getReasonTypeModels () as $code => $scm ) {
			$all_type_models [] = $scm;
		}
		return $all_type_models;
	}
	
	///////////// These functions are pulled from configured type classes: ============
	// Type Model Functions:
	public function transferBeforeSave(&$transfer) {
		$this->_defaultTransferBeforeSave ( $transfer );
		foreach ( $this->getTypeModels () as $scm ) {
			$scm->transferBeforeSave ( $transfer );
		}
		return $this;
	}
	
	public function transferAfterSave(&$transfer) {
		$this->_defaulttransferAfterSave ( $transfer );
		foreach ( $this->getTypeModels () as $scm ) {
			$scm->transferAfterSave ( $transfer );
		}
		return $this;
	}
	
	// Reference Model Functions:
	public function loadReferenceInformation(&$transfer) {
		$this->_defaultLoadReferenceInformation ( $transfer );
		foreach ( $this->getReferenceTypeModels () as $code => $scm ) {
			$scm->loadReferenceInformation ( $transfer );
		}
		return $this;
	}
	
	public function clearReferences(&$transfer) {
		$this->_defaultClearReferences ( $transfer );
		foreach ( $this->getReferenceTypeModels () as $code => $scm ) {
			$scm->clearReferences ( $transfer );
		}
		return $this;
	}
	
	public function getReferenceOptions() {
		$cumulative_array = array ();
		foreach ( $this->getReferenceTypeModels () as $code => $scm ) {
			$cumulative_array += $scm->getReferenceOptions ();
		}
		return $cumulative_array;
	}
	
	public function getTRefCellRenderers() {
		$cumulative_array = array ();
		foreach ( $this->getReferenceTypeModels () as $code => $scm ) {
			$cumulative_array += $scm->getTRefCellRenderers ();
		}
		return $cumulative_array;
	}
	
	// Reason Model Functions:
	public function getOtherReasons() {
		$cumulative_array = array ();
		foreach ( $this->getReasonTypeModels () as $code => $scm ) {
			$cumulative_array += $scm->getOtherReasons ();
		}
		return $cumulative_array;
	}
	
	// Reason Model Functions:
	public function getAllReasons() {
		$cumulative_array = array ();
		foreach ( $this->getReasonTypeModels () as $code => $scm ) {
			$cumulative_array += $scm->getAllReasons ();
		}
		return $cumulative_array;
	}
	
	public function getManualReasons() {
		$cumulative_array = array ();
		foreach ( $this->getReasonTypeModels () as $code => $scm ) {
			$cumulative_array += $scm->getManualReasons ();
		}
		return $cumulative_array;
	}
	
	public function getDistributionReasons() {
		$cumulative_array = array ();
		foreach ( $this->getReasonTypeModels () as $code => $scm ) {
			$cumulative_array += $scm->getDistributionReasons ();
		}
		return $cumulative_array;
	}
	
	public function getRedemptionReasons() {
		$cumulative_array = array ();
		foreach ( $this->getReasonTypeModels () as $code => $scm ) {
			$cumulative_array += $scm->getRedemptionReasons ();
		}
		return $cumulative_array;
	}
	
	/**
	 * @param $current_reason         
	 * @param &$available_reasons   alternative means of modification
	 * @return $available_reasons + any new reasons - any removed reasons
	 * 
	 * Passes the $available_reasons array of existing available reasons so that other modules
	 * can remove reasons as well.  This is bad however because the dependencies 
	 * are left unmanaged.  The module creator should keep this in mind when developing add-on extensions.
	 */
	public function getAvailReasons($current_reason, &$available_reasons) {
		$cumulative_array = $available_reasons; //@nelkaake 3/3/2010 7:46:08 AM
		foreach ( $this->getReasonTypeModels () as $code => $scm ) {
			$cumulative_array += $scm->getAvailReasons ( $current_reason, $available_reasons );
		}
		return $cumulative_array;
	}

}