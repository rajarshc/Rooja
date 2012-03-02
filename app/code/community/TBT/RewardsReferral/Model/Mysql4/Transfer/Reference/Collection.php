<?php

class TBT_RewardsReferral_Model_Mysql4_Transfer_Reference_Collection extends TBT_Rewards_Model_Mysql4_Transfer_Reference_Collection {
    
    /**
     * Gets the transfer ids associated with the referrences in this collection as 
     * a simple array.
     * @return array
     */
    public function getTransferIds() {
        $this->addTransferInfo();
        return $this->distinct(true)->getColumnValues('rewards_transfer_id');
    }    
}