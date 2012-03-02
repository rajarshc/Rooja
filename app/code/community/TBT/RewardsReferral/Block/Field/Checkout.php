<?php

class TBT_RewardsReferral_Block_Field_Checkout extends TBT_RewardsReferral_Block_Field_Abstract {

    protected function _toHtml() {
        if (!Mage::getStoreConfigFlag('rewards/referral/show_in_onepage_checkout'))
            return '';
        if (!$this->showReferralEmail() && !$this->showReferralCode() && !$this->showReferralCode())
            return '';
        if ($this->isCustomerLoggedIn())
            return '';
        return parent::_toHtml();
    }

}