<?php

class TBT_Rewards_Model_Review_Transfer extends TBT_Rewards_Model_Transfer {
	
	public function __construct() {
		parent::__construct ();
	}
	
	public function setReviewId($id) {
		$this->clearReferences ();
		$this->setReferenceType ( TBT_Rewards_Model_Transfer_Reference::REFERENCE_REVIEW );
		$this->setReferenceId ( $id );
		$this->_data ['review_id'] = $id;
		
		return $this;
	}
	
	public function isReview() {
		return ($this->getReferenceType () == TBT_Rewards_Model_Transfer_Reference::REFERENCE_REVIEW) || isset ( $this->_data ['review_id'] );
	}
	
	public function getTransfersAssociatedWithReview($review_id) {
		return $this->getCollection ()->addFilter ( 'reference_type', TBT_Rewards_Model_Transfer_Reference::REFERENCE_REVIEW )->addFilter ( 'reference_id', $review_id );
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
	protected function _getReviewValidator() {
		return Mage::getSingleton ( 'rewards/review_validator' );
	}

    /**
     * Do the points transfer for the review
     *
     * @param  Mage_Review_Model_Review $review
     * @param  int $rule       : Special Rule
     * @return boolean            : whether or not the point-transfer succeeded
     */
    public function transferReviewPoints($review, $rule) {
        $num_points = $rule->getPointsAmount();
        $currency_id = $rule->getPointsCurrencyId();
        $review_id = $review->getId();
        $rule_id = $rule->getId();
        $transfer = $this->initTransfer($num_points, $currency_id, $rule_id);
        
        $customer_id = $review->getCustomerId();
        
        if ( ! $transfer ) {
            return false;
        }
        
        // get the default starting status - usually Pending
        if ( ! $transfer->setStatus(null, Mage::helper('rewards/config')->getInitialTransferStatusAfterReview()) ) {
            // we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
            return false;
        }
        
        $transfer->setReviewId($review_id)
            ->setComments(Mage::getStoreConfig('rewards/transferComments/reviewOrRatingEarned'))
            ->setCustomerId($customer_id)
            ->save();
        
        return true;
    }


}