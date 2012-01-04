<?php

class TBT_Rewardssocial_Block_Abstract extends Mage_Core_Block_Template {

    protected $_predictedPoints = null;
    
    public function getTextWithLoginLinks($text) {
        $login_url = $this->getUrl('customer/account/login') ;
        $text = Mage::helper('rewardssocial')->getTextWithLinks($text, 'login_link', $login_url);
        return $text;
    }


    /**
     * 
     * @return boolean
     */
    public function getHasPredictedLikePoints() {
        $predicted = $this->getPredictedLikePoints();
        if ( empty($predicted) ) {
            return false;
        }
        
        return true;
    }
    

    
    /**
     * 
     */
    public function getPredictedLikePoints() {
        if($this->_predictedPoints == null) {
            $this->_predictedPoints = $this->_getFacebookLikeValidator()->getPredictedFacebookLikePoints();
        }
        return $this->_predictedPoints;
    }

    /**
     * 
     * @return boolean
     */
    public function getHasLikedPage() {
        $customer = $this->_getRS()->getSessionCustomer();
        $liked_url = $this->getCurrentPageURI();
        $hasLiked = $this->_getFacebookLikeValidator()->hasLikedPage($customer, $liked_url);
        
        return $hasLiked;
    }
    
    /**
     * @return boolean
     */
    public function getIsCustomerLoggedIn() {
        return $this->_getRS()->isCustomerLoggedIn();
    }
    
    public function getCurrentPageURI() {
        return $this->getRequest()->getRequestUri();
    }
    
    /**
     * Encrypts a page that can be 'liked'
     * @return string
     */
    public function getPageKey() {
        $page_url = $this->getCurrentPageURI();;
        $page_url_encr = Mage::helper('rewardssocial/crypt')->encrypt($page_url);
        
        return $page_url_encr;
    }

    /**
     * If the is_hidden attribute is set, dont output anything.
     * 
     * (overrides parent method)
     */
    protected function _toHtml() {
        if ( $this->getHidden() ) {
            return "";
        }
        return parent::_toHtml();
    }
    
    /**
     * @return TBT_Rewardssocial_Model_Facebook_Like_Validator
     */
    protected function _getFacebookLikeValidator() {
        return Mage::getSingleton('rewardssocial/facebook_like_validator');
    }

    /**
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRS() {
        return Mage::getSingleton('rewards/session');
    }
}