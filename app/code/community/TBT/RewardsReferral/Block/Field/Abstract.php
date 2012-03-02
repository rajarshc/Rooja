<?php

class TBT_RewardsReferral_Block_Field_Abstract extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function showReferralCode() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_code');
    }
    
    public function showReferralCodeShort() {
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
    
    /**
     * Returns the current affiliate's code
     * @return string short or long code (depending on store configuration) for the affiliate currently in the session.
     */
    public function getCurrentAffiliate() {
        $affiliate_customer = Mage::helper( 'rewardsref/code' )->getReferringCustomer();
        
        if(!$affiliate_customer) return "";
        
        $code = Mage::helper( 'rewardsref/code' )->getCode( $affiliate_customer->getEmail() );
        
        if ( Mage::getStoreConfigFlag( 'rewards/referral/show_referral_short_code' ) ) {
            $code = Mage::helper( 'rewardsref/shortcode' )->getCode( $affiliate_customer->getId() );
        }
        
        if ( empty( $code ) ) return "";
        
        return $code;
    }

}