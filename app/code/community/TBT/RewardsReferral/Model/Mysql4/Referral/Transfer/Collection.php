<?php

class TBT_RewardsReferral_Model_Mysql4_Referral_Transfer_Collection extends TBT_Rewards_Model_Mysql4_Transfer_Collection {
    
    /**
     * 
     * @param unknown_type $referralobj
     */
    public function getPointsReferralEarned($referralobj) {
        $points_earned = $this->_getReferralPoints($referralobj, 'approved');
        return $points_earned;
    }
    
    
    /**
     * 
     * @param unknown_type $referralobj
     */
    public function getPointsReferralPending($referralobj) {
        $points_pending = $this->_getReferralPoints($referralobj, 'not_approved');
        return $points_pending;
    }
    

    /**
     * 
     * @param unknown_type $referralobj
     */
    public function filterReferralTransfers($referralobj) {
        $ref_col = Mage::getResourceModel('rewardsref/referral_transfer_reference_collection');
        $ref_col->filterReferral($referralobj);                
        $transfer_ids = $ref_col->getTransferIds();
    
        $this->addFieldToFilter('main_table.rewards_transfer_id', array('IN' => $transfer_ids)  );
        
        return $this;
    }
    
    /**
     * 
     * @param unknown_type $referralobj
     * @param unknown_type $status
     */
    protected function _getReferralPoints($referralobj, $status='approved' ) {
        
        $this->filterReferralTransfers($referralobj);
        
        if($status == 'not_approved') {
            $this->addFieldToFilter('status', array('neq' => TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED));
        } else {
            $this->addFieldToFilter('status', TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED);
        }
        
        $this    ->selectOnlyPosTransfers()
                ->sumPoints();
                
        $points_earned = Mage::getModel('rewards/points');
        foreach ($this as $points) {
            $points_earned->add($points->getCurrencyId(), (int) $points->getPointsCount());
        }
        
        return $points_earned;
    }
    
}