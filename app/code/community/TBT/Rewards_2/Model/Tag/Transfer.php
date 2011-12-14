<?php

class TBT_Rewards_Model_Tag_Transfer extends TBT_Rewards_Model_Transfer {
	
	public function __construct() {
		parent::__construct ();
	}
	
	public function setTagId($id) {
		$this->clearReferences ();
		$this->setReferenceType ( TBT_Rewards_Model_Transfer_Reference::REFERENCE_TAG );
		$this->setReferenceId ( $id );
		$this->_data ['tag_id'] = $id;
		
		return $this;
	}
	
	public function isTag() {
		return ($this->getReferenceType () == TBT_Rewards_Model_Transfer_Reference::REFERENCE_TAG) || isset ( $this->_data ['tag_id'] );
	}
	
	public function getTransfersAssociatedWithTag($tag_id) {
		return $this->getCollection ()->addFilter ( 'reference_type', TBT_Rewards_Model_Transfer_Reference::REFERENCE_TAG )->addFilter ( 'reference_id', $tag_id );
	}
	
	/**
	 * Fetches the transfer helper
	 *
	 * @return TBT_Rewards_Helper_Transfer
	 */
	protected function _getTransferHelper() {
		return Mage::helper ( 'rewards/transfer' );
	}
	
	/**
	 * Fetches the rewards special validator singleton
	 *
	 * @return TBT_Rewards_Model_Special_Validator
	 */
	protected function _getSpecialValidator() {
		return Mage::getSingleton ( 'rewards/special_validator' );
	}
	
	/**
	 * Fetches the rewards special validator singleton
	 *
	 * @return TBT_Rewards_Model_Special_Validator
	 */
	protected function _getTagValidator() {
		return Mage::getSingleton ( 'rewards/tag_validator' );
	}
	
	/**
	 * Do the points transfer for the tag
	 *
	 * @param  TBT_Rewards_Model_Tag_Wrapper $tag
	 * @param  int $rule       : Special Rule
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function transferTagPoints($tag, $rule) {
		$num_points = $rule->getPointsAmount ();
		$currency_id = $rule->getPointsCurrencyId ();
		$tag_id = $tag->getId ();
		$rule_id = $rule->getId ();
		$transfer = $this->initTransfer ( $num_points, $currency_id, $rule_id );
		
		if (! $transfer) {
			return false;
		}
		
		// get the default starting status - usually Pending
		if (! $transfer->setStatus ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterTag () )) {
			return false;
		}
		
		$transfer->setTagId ( $tag_id )->setCustomerId ( Mage::getModel ( 'tag/tag' )->load ( $tag_id )->getFirstCustomerId () )->setComments ( Mage::getStoreConfig ( 'rewards/transferComments/tagEarned' ) )->save ();
		
		return true;
	}

}