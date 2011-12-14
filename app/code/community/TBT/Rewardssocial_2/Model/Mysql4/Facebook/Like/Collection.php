<?php

class TBT_Rewardssocial_Model_Mysql4_Facebook_Like_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct () {
        $this->_init('rewardssocial/facebook_like');
    }
    
    public function containsEntry($customer_id, $liked_url, $facebook_account_id = '') {
        
        $collection = $this->addFieldToFilter('customer_id', $customer_id)
            ->addFieldToFilter('url', $liked_url);
            
        if ($facebook_account_id) {
            $collection = $collection->addFieldToFilter('facebook_account_id', $facebook_account_id);
        }
            
        return $collection->getSize() > 0;
    }
    
    public function getTimeUntilNextLikeAllowed($customer) {
        $this->filterAllSinceMinTime($customer);
        $this->getSelect()->columns(new Zend_Db_Expr("UNIX_TIMESTAMP(`created_time`) as `created_ts`"));
        $this->setOrder('created_ts', 'DESC');
        $this->getSelect()->limit(1);
        
        $this->load();
        
        if($this->count() <= 0) {
            return 0;
        }
        
        $min_sec = Mage::helper('rewardssocial/facebook_config')->getMinSecondsBetweenLikes( $customer->getStore() );
        $first_item = $this->getFirstItem();
        
        $time_since_last_like  = time() - $first_item->getCreatedTs() ;

        $min_sec_until_next_like = max(0, $min_sec - $time_since_last_like) ;
        
        return $min_sec_until_next_like;
    }
    

    public function hasMinTimePassed($customer) {
        $this->filterAllSinceMinTime($customer);
        
        if($this->count() > 0) {
            return false;
        }
        
        return true;
    }
    
    public function filterAllSinceMinTime($customer) {
        $min_sec = Mage::helper('rewardssocial/facebook_config')->getMinSecondsBetweenLikes( $customer->getStore() );
        $current_time = time();
        $oldest_req_time = $current_time - $min_sec;
        
        $this ->addFilter('customer_id', $customer->getId())
            ->addFieldToFilter('UNIX_TIMESTAMP(`created_time`)', array('gteq' => $oldest_req_time));
       
        return $this;
    }
    
}
?>