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

    public function getStatusCaption($status_id) {
        return Mage::getSingleton('rewardsref/referral_status')->getStatusCaption($status_id);
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function getCustomer() {
        return Mage::getSingleton('rewards/session')->getCustomer();
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function getReferralEmail() {
        $email = $this->getCustomer()->getEmail();
        return $email;
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function getReferralCode() {
        $email = $this->getReferralEmail();
        $code = Mage::helper('rewardsref/code')->getcode($email);
        return $code;
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function getReferralUrl() {
        //@nelkaake Added on Tuesday July 27, 2010: TODO move this to a URL helper class so that it can be included in e-mails and other modules
        switch (Mage::helper('rewardsref')->getReferralUrlStyle()) {
            case TBT_RewardsReferral_Helper_Data::REWARDSREF_URL_STYLE_EMAIL:
                $url_data = array(
                    'email' => urlencode($this->getReferralEmail()),
                );
                break;
            case TBT_RewardsReferral_Helper_Data::REWARDSREF_URL_STYLE_CODE:
                $url_data = array(
                    'code' => urlencode($this->getReferralCode()),
                );
                break;
            default:
                $url_data = array(
                    'id' => urlencode(Mage::getSingleton('rewards/session')->getCustomerId()),
                );
        }
        $url = $this->getUrl('rewardsref/index/refer', $url_data);
        return $url;
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function showSendReferralForm() {
        return Mage::getStoreConfigFlag('rewards/referral/show_invite_form');
    }

    public function showReferralUrl() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_url');
    }

    public function showReferralCode() {
        return Mage::getStoreConfigFlag('rewards/referral/show_referral_code');
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