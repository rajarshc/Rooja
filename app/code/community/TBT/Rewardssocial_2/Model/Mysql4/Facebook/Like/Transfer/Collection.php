<?php

class TBT_Rewardssocial_Model_Mysql4_Facebook_Like_Transfer_Collection 
        extends TBT_Rewards_Model_Mysql4_Transfer_Collection {
        
    /**
     * 
     * 
     * (overrides parent method)
     */
    public function _initSelect () {
        parent::_initSelect();
        
        $this->addFilter('reason_id', TBT_Rewardssocial_Model_Facebook_Like_Reason::REASON_TYPE_ID );
        
        return $this;
    }
    /**
     * 
     * @param TBT_Rewardssocial_Model_Facebook_Like $fb_like_model
     * 
     * @return TBT_Rewardssocial_Model_Mysql4_Facebook_Like_Transfer_Collection 
     */
    public function addFacebookLikeFilter($fb_like_model) {
        $ref_collection = Mage::getModel('rewards/transfer_reference')->getCollection()
                ->addFilter('reference_type', TBT_Rewardssocial_Model_Facebook_Like_Reference::REFERENCE_TYPE_ID )
                ->addFilter('reference_id', $fb_like_model->getId() );
        $transfer_ids = $ref_collection->getColumnValues('rewards_transfer_id');
        
        $this->addFieldToFilter('main_table.rewards_transfer_id', array('IN' => $transfer_ids) );
        $this->addFilter('customer_id', $fb_like_model->getCustomerId());
        
        return $this;
            
    }
        
    /**
     * 
     * @param TBT_Rewardssocial_Model_Facebook_Like $fb_like_model
     * 
     * @return TBT_Rewardssocial_Model_Mysql4_Facebook_Like_Transfer_Collection 
     */
    public function addFacebookLikePageFilter($page_url, $customer_id) {
        $ref_collection = Mage::getModel('rewards/transfer_reference')->getCollection()
                ->addFilter('reference_type', TBT_Rewardssocial_Model_Facebook_Like_Reference::REFERENCE_TYPE_ID )
                ->addFilter('page_url', $page_url )
                ->addFilter('customer_id', $customer_id );
        $transfer_ids = $ref_collection->getColumnValues('rewards_transfer_id');
        
        $this->addFieldToFilter('main_table.rewards_transfer_id', array('IN' => $transfer_ids) );
        $this->addFilter('customer_id', $customer_id);
        
        return $this;
            
    }
        
    /**
     * 
     * @param TBT_Rewardssocial_Model_Facebook_Like $fb_like_model
     * 
     * @return TBT_Rewardssocial_Model_Mysql4_Facebook_Like_Transfer_Collection 
     */
    public function filterCustomerRewardsSince($customer_id, $oldest_req_time) {
        $this    ->addFilter('customer_id', $customer_id)
                ->addFieldToFilter('UNIX_TIMESTAMP(creation_ts)', array('gteq' => $oldest_req_time )  )  ;
        return $this;
            
    }
    
    public function _construct() {
		$this->_init ( 'rewardssocial/facebook_like_transfer', 'rewards/transfer');
		return $this;
    }
}
?>