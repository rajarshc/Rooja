<?php

class TBT_RewardsReferral_Block_Customer_Referral_Abstract extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getReferred() {
        if (!$this->hasData('referred')) {
            $referred = Mage::getResourceModel('rewardsref/referral_collection')
                    ->addClientFilter(Mage::getSingleton('rewards/session')->getCustomerId());
            $this->setReferred($referred);
        }
        return $this->getData('referred');
    }

    public function hasPredictedSignupPoints() {
        return!$this->getPredictedSignupPoints()->isEmpty();
    }

    public function getPredictedSignupPoints() {
        return Mage::getModel('rewardsref/referral_signup')->getTotalReferralPoints();
    }

    public function hasPredictedFirstOrderPoints() {
        return!$this->getPredictedFirstOrderPoints()->isEmpty();
    }

    public function getPredictedFirstOrderPoints() {
        return Mage::getModel('rewardsref/referral_firstorder')->getTotalReferralPoints();
    }

    public function getAccumulatedReferralPoints($referralobj) {
        $p = Mage::getModel('rewardsref/referral_signup')->getAccumulatedPoints($referralobj);
        return $p;
    }

    public function getPendingReferralPoints($referralobj) {
        $p = Mage::getModel('rewardsref/referral_signup')->getPendingReferralPoints($referralobj);
        return $p;
    }

    public function getStatusCaption($status_id) {
        return Mage::getSingleton('rewardsref/referral_status')->getStatusCaption($status_id);
    }

    public function getCustomer() {
        return Mage::getSingleton('rewards/session')->getCustomer();
    }

    public function getReferralEmail() {
        return (string)$this->getCustomer()->getEmail();
    }

    public function getReferralCode() {
        return (string)Mage::helper('rewardsref/code')->getCode($this->getReferralEmail());
    }
    
    public function getReferralShortCode() {
        return (string)Mage::helper('rewardsref/shortcode')->getCode($this->getCustomer()->getId());
    }

    public function getReferralUrl() {
        return (string)Mage::helper('rewardsref/url')->getUrl($this->getCustomer());
    }

    public function showSendReferralForm() {
        return Mage::getStoreConfigFlag('rewards/referral/show_invite_form');
    }

    public function showReferralUrl() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_url');
    }

    public function showReferralCode() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_code');
    }
    
    public function showReferralShortCode() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_short_code');
    }

    public function showReferralEmail() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_email');
    }

    public function showPreferences() {
        return Mage::getStoreConfigFlag('rewards/referral/show_preferences');
    }

    public function showHistory() {
        return Mage::getStoreConfigFlag('rewards/referral/show_history');
    }

}