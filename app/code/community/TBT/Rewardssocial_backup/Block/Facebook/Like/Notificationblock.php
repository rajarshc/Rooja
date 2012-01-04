<?php

/**
 * 
 * 
 */
class TBT_Rewardssocial_Block_Facebook_Like_Notificationblock extends TBT_Rewardssocial_Block_Abstract {
    protected $_timeUntilNextLike = null;
    
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
    
    
    /**
     * 
     */
    public function getPredictedLikePointsString() {
        $str = (string) Mage::getModel('rewards/points')->set(  $this->getPredictedLikePoints()  );
        
        return $str;
    }
    
    /**
     * 
     */
    public function getTimeUntilNextLike() {
        $customer = $this->_getRS()->getSessionCustomer();
        
        if($this->_timeUntilNextLike == null) {
            $this->_timeUntilNextLike = Mage::getModel('rewardssocial/facebook_like')->getCollection()->getTimeUntilNextLikeAllowed($customer);
        }
        
        return $this->_timeUntilNextLike;
    }

    /**
     * 
     * @return boolean
     */
    public function getCanEarnFacebookPoints() {
        if ( ! $this->getHasPredictedLikePoints() ) {
            return false;
        }
        
        if ( ! $this->getHasLikedPage() ) {
            return false;
        }
        
        return true;
    
    }
    

    /**
     * If the is_hidden attribute is set, dont output anything.
     * 
     * (overrides parent method)
     */
    protected function _toHtml() {
		if ( ! Mage::helper('rewardssocial/facebook_evlike')->isEvlikeEnabled() ) {
		    return "";
		}
		if ( ! Mage::helper('rewardssocial/facebook_evlike')->isEvlikeValidRewardsConfig() ) {
		    return "";
		}
        return parent::_toHtml();
    }
}