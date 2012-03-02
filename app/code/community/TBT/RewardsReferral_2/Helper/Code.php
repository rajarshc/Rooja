<?php

/**
 * Code generator/parser helper
 *
 * @nelkaake Added on Saturday June 26, 2010:  
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Helper_Code extends Mage_Core_Helper_Abstract {

    public function getEmail($code) {
        $code = base64_decode($code);
        $email = $this->_getEncrypter()->decrypt($code);
        return $email;
    }

    public function getCode($email) {
        $code = $this->_getEncrypter()->encrypt($email);
        $code = base64_encode($code);
        return $code;
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    protected function _getEncrypter() {
        return Mage::getSingleton('core/encryption');
    }

    /**
     * returns true if this is a valid e-mail address
     * @param string $email
     */
    public function check_email_address($email) {
        return Mage::helper('rewardsref/validation')->isValidEmail($email);
    }

    public function parseEmailFromReferralString($refstr) {

        //@nelkaake Added on Thursday July 8, 2010: If it's a valid e-mail return the e-mail address
        if ($this->check_email_address($refstr)) {
            $email = strtolower(trim($refstr));
        } else {
            $email = $this->getEmail($refstr);
        }
        return $email;
    }

    //@nelkaake Added on Thursday July 8, 2010: Sets the referral into the session
    public function setReferral($referral_code_or_email) {
        //@nelkaake Added on Thursday July 8, 2010: 
        $email = Mage::helper('rewardsref/code')->parseEmailFromReferralString($referral_code_or_email);
        Mage::getSingleton('core/session')->setReferrerEmail($email);
        return $this;
    }

    //@nelkaake Added on Wednesday October 6, 2010: Gets the referral into the session
    public function getReferral() {
        return Mage::getSingleton('core/session')->getReferrerEmail();
    }

}
