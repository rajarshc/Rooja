<?php

class TBT_RewardsReferral_Block_Field_Register extends TBT_RewardsReferral_Block_Field_Abstract {

    protected function _toHtml() {
        if (!Mage::getStoreConfigFlag('rewards/referral/show_in_register'))
            return '';
        if (!$this->showReferralEmail() && !$this->showReferralCode()  )
            return '';
        return parent::_toHtml();
    }

}