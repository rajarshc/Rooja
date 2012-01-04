<?php

class TBT_Rewards_Block_Checkout_Onepage_Warnguest extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('rewards/checkout/onepage/warnguest.phtml');
    }

    /**
     * If no user is logged in and quote has points spending/earning, 
     * display a message in the Onepage Checkout telling the customer 
     * that they need to log-in or create an account to earn/redeem points.
     * 
     * @return string
     */
    public function getWarnMessage() {
        if ($this->shouldWarnGuest() == false)
            return '';
        if ($this->hasDistributions() && $this->hasRedemptions())
            return $this->__('Points will be ignored unless you create or login an account!');
        if ($this->hasDistributions())
            return $this->__('You will throw away your points if you dont create or login an account!');
        if ($this->hasRedemptions())
            return $this->__('Redeemed points will be ignored!  You must login if you wish to use your points!');
    }

    /**
     * no user is logged in and quote has points spending/earning, 
     *
     * @return boolean 
     */
    public function shouldWarnGuest() {
        return!$this->isCustomerLoggedIn() && ($this->hasDistributions() || $this->hasRedemptions());
    }

    /**
     * True if the customer is logged in.
     *
     * @return boolean
     */
    public function isCustomerLoggedIn() {
        return $this->_getRewardsSess()->isCustomerLoggedIn();
    }

    // any type of redemptions, cart and catalog
    public function hasRedemptions() {
        return $this->_getRewardsSess()->hasRedemptions();
    }

    // any type of redemptions, cart and catalog
    public function hasDistributions() {
        return $this->_getRewardsSess()->hasDistributions();
    }

    /**
     * Fetches the rewards session singleton
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRewardsSess() {
        return Mage::getSingleton('rewards/session');
    }

}