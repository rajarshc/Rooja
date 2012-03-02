<?php

/**
 * Helper Data
 *
 * @category   TBT
 * @package    TBT_RewardsReferral
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Helper_Data extends Mage_Core_Helper_Abstract {
    const REWARDSREF_URL_STYLE_ID = 1;
    const REWARDSREF_URL_STYLE_EMAIL = 2;
    const REWARDSREF_URL_STYLE_CODE = 3;

    public function log($msg) {
        Mage::log($msg, null, "rewards_referral.log");
        return $this;
    }

    public function notice($msg) {
        $this->log("NOTICE: " . $msg);
        return $this;
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function getReferralUrlStyle() {
        return self::REWARDSREF_URL_STYLE_ID;
    }

    //@nelkaake Added on Saturday June 26, 2010: Same as initateSessionReferral2 but uses customer model instead
    public function initateSessionReferral($newCustomer) {
        return $this->initateSessionReferral2($newCustomer->getEmail(), $newCustomer->getName());
    }

    //@nelkaake Added on Saturday June 26, 2010: Same as initateSessionReferral but uses email and name instead.
    public function initateSessionReferral2($newCustomerEmail, $newCustomerName) {
        try {
            $email = Mage::getSingleton('core/session')->getReferrerEmail();
            if (empty($email)) {
                return $this;
            }
            $website_id = Mage::app()->getStore()->getWebsiteId();
            $referrer = Mage::getModel('rewards/customer')->setWebsiteId($website_id);
            $referrer->loadByEmail($email);
            if (!$referrer->getId()) {
                throw new Exception($this->__("The referral email in the session is invalid: %s", $email));
            }
            Mage::getModel('rewardsref/referral')->registerReferral2($referrer, $newCustomerEmail, $newCustomerName);
        } catch (Exception $e) {
            Mage::helper('rewardsref')->log($e->getMessage());
            Mage::logException($e);
        }
        return $this;
    }

}
