<?php

class TBT_RewardsReferral_Model_Mysql4_Referral_Order_Transfer_Reference_Collection extends TBT_RewardsReferral_Model_Mysql4_Transfer_Reference_Collection {
            
    /**
     * filter records that are of a reference type Referral_Order and have the
     * provided order id.
     *
     * @param type $orderId 
     * @return TBT_RewardsReferral_Model_Mysql4_Referral_Transfer_Collection
     */
    public function filterAssociatedWithOrder($orderId) {
        $this->addFilter('reference_type', TBT_RewardsReferral_Model_Transfer_Reference_Referral_Order::REFERENCE_TYPE_ID)
                ->addFilter('reference_id', $orderId);
        return $this;
    }
      
}