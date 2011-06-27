<?php

class TBT_RewardsReferral_Block_Field_Abstract extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function showReferralCode() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_code');
    }

    public function showReferralEmail() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_email');
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function getCustomer() {
        return Mage::getSingleton('rewards/session')->getCustomer();
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function isCustomerLoggedIn() {
        return Mage::getSingleton('rewards/session')->isCustomerLoggedIn();
    }

}