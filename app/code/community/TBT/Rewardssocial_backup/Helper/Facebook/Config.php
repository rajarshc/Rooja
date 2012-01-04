<?php
/**
 * Helper Data
 *
 * @category   TBT
 * @package    TBT_Rewardssocial
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewardssocial_Helper_Facebook_Config extends Mage_Core_Helper_Abstract {
    
    public function getMaxLikeRewardsPerDay($store=null) {
        return (int) Mage::getStoreConfig('rewards/facebook/maxLikeRewardsPerDay', $store);
    }
    
    public function getMinSecondsBetweenLikes($store=null) {
        return (int) Mage::getStoreConfig('rewards/facebook/minSecondsBetweenLikes', $store);
    }
    

    
    /**
     * @deprecated unused
     */
    public function getAppId() {
        return '23dhfkjdhfkjsdfh4758479879237';//Mage::getStoreConfig('evlike_evlike_ev_facebook_app_id');
    }
    
    /**
     * @deprecated unused
     */
    public function getAppSecretId() {
        return '0cb3548d9f394bfashfsdoifhrobc251d3cf4622c2c29';
    }
    
    
}
