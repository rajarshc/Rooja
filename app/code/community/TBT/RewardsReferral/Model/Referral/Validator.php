<?php

/**
 * Referral validator singleton
 * @deprecated not used yet.  This will likely be implemented in the future.
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Referral_Validator extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
    }

    /**
     * If this doesn't throw any exceptions, all the data in the model is fine.
     * //@nelkaake (add) on 1/11/10:      
     * @throws Exception
     */
    public function checkEmailsOnSignup($referral_email, $new_customer_email) {
        if (empty($new_customer_email)) {
            throw new Exception(Mage::helper('rewardsref')->__("New customer e-mail is empty.", 1));
        }
        if (empty($referral_email)) {
            throw new Exception(Mage::helper('rewardsref')->__("Referral is empty.", 1));
        }
        if (Mage::getModel('rewardsref/referral')->isSubscribed($new_customer_email)) {
            throw new Exception(Mage::helper('rewardsref')->__('A referral entry already exists for the new customer from the same person or someone else.', 1));
        }
        if ($referral_email == $new_customer_email) {
            throw new Exception(Mage::helper('rewardsref')->__("Referral e-mail and customer e-mail may not be the same."));
        }
        // Referral model is okay because it passed all checks         '
        return $this;
    }

}
