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
class TBT_Rewards_Model_Transfer_Reference extends Mage_Core_Model_Abstract {
	// status values less than 1 means that transfer is ignored in
	// customer point calculations.
	const REFERENCE_ORDER = 1;
	const REFERENCE_POLL = 3;
	const REFERENCE_RATING = 5;
	const REFERENCE_TRANSFER = 6;
	const REFERENCE_SIGNUP = 7;
	const REFERENCE_TO_FRIEND = 8;
	const REFERENCE_FROM_FRIEND = 9;
	
	
	public function isOrder($typeId) {
		return ($typeId == TBT_Rewards_Model_Transfer_Reference::REFERENCE_ORDER);
	}

	public function isRating($typeId) {
		return ($typeId == TBT_Rewards_Model_Transfer_Reference::REFERENCE_RATING);
	}
	
	
	public function isPoll($typeId) {
		return ($typeId == TBT_Rewards_Model_Transfer_Reference::REFERENCE_POLL);
	}
	
	
	public function isTransfer($typeId) {
		return ($typeId == TBT_Rewards_Model_Transfer_Reference::REFERENCE_TRANSFER);
	}
	
	public function isSignup($typeId) {
		return ($typeId == TBT_Rewards_Model_Transfer_Reference::REFERENCE_SIGNUP);
	}
	
	public function isToFriend($typeId) {
		return ($typeId == TBT_Rewards_Model_Transfer_Reference::REFERENCE_TO_FRIEND);
	}
	
	public function isFromFriend($typeId) {
		return ($typeId == TBT_Rewards_Model_Transfer_Reference::REFERENCE_FROM_FRIEND);
	}
	
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'rewards/transfer_reference' );
	}
	
	public function getOptionArray() {
		$base_options = array (
			self::REFERENCE_ORDER => Mage::helper ( 'rewards' )->__ ( 'Order' ), 
			self::REFERENCE_POLL => Mage::helper ( 'rewards' )->__ ( 'Poll' ), 
			self::REFERENCE_RATING => Mage::helper ( 'rewards' )->__ ( 'Rating' ), 
			self::REFERENCE_TRANSFER => Mage::helper ( 'rewards' )->__ ( 'Transfer' ), 
			self::REFERENCE_SIGNUP => Mage::helper ( 'rewards' )->__ ( 'Signup' ), 
			self::REFERENCE_TO_FRIEND => Mage::helper ( 'rewards' )->__ ( 'To Friend' ), 
			self::REFERENCE_FROM_FRIEND => Mage::helper ( 'rewards' )->__ ( 'From Friend' ) 
		);
		$base_options = array_merge ( $base_options, $this->_getTypes ()->getReferenceOptions () );
		return $base_options;
	}
	
	public function getReferenceCaption($id) {
		$reference = $this->getOptionArray ();
		if (isset ( $reference [$id] )) {
			return $reference [$id];
		} else {
			return null;
		}
	}
	
	/**
	 * Loads a order model by the given transfer id and order id.
	 *
	 * @param int $transfer_id
	 * @param int $ref_type
	 * @param int $ref_id
	 * @return TBT_Rewards_Model_Transfer_Reference
	 */
	public function loadByTransferAndReference($transfer_id, $ref_type, $ref_id) {
		$read = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_read' );
		$select = $read->select ()->from ( $this->_getResource ()->getTable ( 'transfer_reference' ) );
		$select->where ( 'rewards_transfer_id = ?', $transfer_id );
		$select->where ( 'reference_type = ?', $ref_type );
		$select->where ( 'reference_id = ?', $ref_id );
		if ($data = $read->fetchAll ( $select )) {
			$id = $data [0] ['rewards_transfer_reference_id'];
		} else {
			$id = null;
		}
		$this->load ( $id );
		return $this;
	}
	
	/**
	 * Loads this model with the order that's associated with the 
	 * provided trasnfer id.
	 * @see This should be modified in the future if we ever decide
	 * to add the functionality to relate multiple orders to a single
	 * rewards transfer.
	 *
	 * @param int $transfer_id
	 * @return TBT_Rewards_Model_Transfer_Reference
	 */
	public function loadReferenceByTransferId($transfer_id) {
		$read = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_read' );
		$select = $read->select ()->from ( $this->_getResource ()->getTable ( 'transfer_reference' ) )->where ( 'rewards_transfer_id = ?', $transfer_id );
		
		if ($data = $read->fetchAll ( $select )) {
			$id = $data [0] ['rewards_transfer_reference_id'];
		} else {
			$id = null;
		}
		
		$this->load ( $id );
		return $this;
	}


	/**
	 * @deprecated use TBT_Rewards_Model_Tag_Reference::REFERENCE_TYPE_ID
	 * @var int
	 */
	const REFERENCE_TAG = TBT_Rewards_Model_Tag_Reference::REFERENCE_TYPE_ID;
	/**
	 * @deprecated use TBT_Rewards_Model_Review_Behavior_Reference::REFERENCE_TYPE_ID
	 * @var int
	 */
	const REFERENCE_REVIEW = TBT_Rewards_Model_Review_Reference::REFERENCE_TYPE_ID;
	
	/**
	 * @deprecated use TBT_Rewards_Model_Newsletter_Subscription_Reference::REFERENCE_TYPE_ID
	 * @var int
	 */
	const REFERENCE_NEWSLETTER = TBT_Rewards_Model_Newsletter_Subscription_Reference::REFERENCE_TYPE_ID;
	
	
	/**
	 * @deprecated as of Sweet Tooth 1.5
	 */
	public function isReview($typeId) {
		return ($typeId == TBT_Rewards_Model_Review_Reference::REFERENCE_TYPE_ID);
	}
	
	
	/**
	 * @deprecated as of Sweet Tooth 1.5
	 */
	public function isNewsletter($typeId) {
		return ($typeId == TBT_Rewards_Model_Newsletter_Subscription_Reference::REFERENCE_TYPE_ID);
	}
	
	/**
	 * @deprecated as of Sweet Tooth 1.5
	 */
	public function isTag($typeId) {
		return ($typeId == TBT_Rewards_Model_Tag_Reference::REFERENCE_TYPE_ID);
	}
	
	
	
}