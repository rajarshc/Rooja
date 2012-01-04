<?php

class TBT_Rewards_Model_Review_Observer extends Varien_Object {
	
	/**
	 * The wrapped review
	 * @var TBT_Rewards_Model_Review_Wrapper
	 */
	protected $_review;
	
	/**
	 * @var array
	 */
	protected $oldData;
	
	/**
	 * @var TBT_Rewards_Model_Review_Wrapper
	 */
	protected $_wrapperModel;
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->_wrapperModel = Mage::getModel ( 'rewards/review_wrapper' );
	}

    /**
     * Manages the after_load observer 
     * @param Varien_Event_Observer $o
     * @return TBT_Rewards_Model_Review_Observer
     */
    public function afterLoadReview(Varien_Event_Observer $o) {
        $review = $o->getEvent()->getObject();
        if ( ! ($review instanceof Mage_Review_Model_Review) ) return $this;
        
        //Before you save, pass all current data into a dummy model for comparison later.
        $this->oldData = $review->getData ();
		return $this;
	}
	
	/**
	 * Manages the after_save observer
	 * @param Varien_Event_Observer $o
	 * @return TBT_Rewards_Model_Review_Observer
	 */
	public function afterSaveReview(Varien_Event_Observer $o) {
        $review = $o->getEvent()->getObject();
        if ( ! ($review instanceof Mage_Review_Model_Review) ) return $this;
        
        $review = $this->_wrapperModel->wrap($review);
        //If the review becomes approved, approve all associated pending tranfser
        if ( $this->oldData['status_id'] == Mage_Review_Model_Review::STATUS_PENDING && $review->isApproved() ) {
            $review->approvePendingTransfers ();
                } elseif($this->oldData ['status_id'] == Mage_Review_Model_Review::STATUS_PENDING && $review->isNotApproved ()) {
                        $review->discardPendingTransfers ();
		//If the review is new (hence not having an id before) get applicable rules, 
		//and create a pending transfer for each one
		} elseif ($review->getReview ()->getReviewId () && ! isset ( $this->oldData ['review_id'] )) {
			$review->ifNewReview ();
		}
		return $this;
	}
	
	/**
	 * @return TBT_Rewards_Model_Review_Wrapper
	 */
	protected function _getReview() {
		return $this->_review;
	}
	
	/**
	 * @return TBT_Rewards_Model_Review_Validator
	 */
	protected function _getReviewValidator() {
		return Mage::getSingleton ( 'rewards/review_validator' );
	}

}