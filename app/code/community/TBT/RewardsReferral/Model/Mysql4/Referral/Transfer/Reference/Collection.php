<?php

class TBT_RewardsReferral_Model_Mysql4_Referral_Transfer_Reference_Collection extends TBT_RewardsReferral_Model_Mysql4_Transfer_Reference_Collection {
    
    /**
     * Filters by the referral model provided.  The references should be REFERENCE_REFERRAL type plus the reference should be 
     * linking the referral with the the affiliate
     * @param TBT_RewardsReferral_Model_Referral $referralobj
     */
    public function filterReferral($referralobj) {
       $this->addFieldToFilter('reference_id', $referralobj->getReferralChildId())
                ->addFieldToFilter('reference_type', TBT_RewardsReferral_Model_Transfer::REFERENCE_REFERRAL)
                ->addTransferInfo()
                ->addFieldToFilter('transfers.customer_id', $referralobj->getReferralParentId());
       return $this;
    }
    
    /**
     * Gets the transfer ids associated with the referrences in this collection as 
     * a simple array.
     * @return array
     */
    public function getTransferIds() {
        $this->addTransferInfo();
        return $this->distinct(true)->getColumnValues('rewards_transfer_id');
    }

    /**
     * Get the accumulated points earned from this referral object
     * @param TBT_RewardsReferral_Model_Referral_Abstract $referralobj
     * @return TBT_Rewards_Model_Points 
     */
    public function getAccumulatedPoints($referralobj) {
        $points_earned = Mage::getModel( 'rewards/points' );
        
        // First get and filter the collection of reference data
        $this->addTransferInfo()
            ->filterReferral( $referralobj )
            ->addFieldToFilter( 'status', TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED );
        
        // Fetch all the resulting transfer IDs.
        $transfer_ids = $this->getTransferIds();
        
        if ( empty( $transfer_ids ) ) {
            return $points_earned;
        }
        
        // Get the collection of transfers that have those reference IDs.
        $col = Mage::getModel( 'rewardsref/transfer' )->getCollection()
            ->addFieldToFilter( 'main_table.rewards_transfer_id', array( 'in' => $transfer_ids ) )
            ->selectOnlyPosTransfers()
            ->sumPoints();
        
        // Accumulate the points earned into one points model
        foreach ($col as $points) {
            $points_earned->add( $points->getCurrencyId(), (int) $points->getPointsCount() );
        }
        
        return $points_earned;
    }

    /**
     * Get the accumulated pending points earned from this referral object
     * @param TBT_RewardsReferral_Model_Referral_Abstract $referralobj
     * @return TBT_Rewards_Model_Points 
     */
    public function getPendingReferralPoints($referralobj) {
        $points_earned = Mage::getModel( 'rewards/points' );
        
        // First get and filter the collection of reference data
        $this->addTransferInfo()
            ->filterReferral( $referralobj )
            ->addFieldToFilter( 'status', array( 'neq' => TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED  ) );
        
        // Fetch all the resulting transfer IDs.
        $transfer_ids = $this->getTransferIds();
        
        if ( empty( $transfer_ids ) ) {
            return $points_earned;
        }
        
        // Get the collection of transfers that have those reference IDs.
        $col = Mage::getModel( 'rewardsref/transfer' )->getCollection()
            ->addFieldToFilter( 'main_table.rewards_transfer_id', array( 'in' => $transfer_ids  ) )
            ->selectOnlyPosTransfers()
            ->sumPoints();
        
        foreach ($col as $points) {
            $points_earned->add( $points->getCurrencyId(), (int) $points->getPointsCount() );
        }
        
        return $points_earned;
    }
    
}

