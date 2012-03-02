<?php

/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Referral_Signup extends TBT_RewardsReferral_Model_Referral_Abstract {
    const STATUS_REFERRAL_SIGNED_UP = 1;

    public function getReferralStatusId() {
        return self::STATUS_REFERRAL_SIGNED_UP;
    }

    public function getReferralTransferMessage($newCustomer) {
        return Mage::getStoreConfig('rewards/transferComments/referralSignup');
    }

    public function getTotalReferralPoints() {
        $applicable_rules = Mage::getSingleton('rewardsref/validator')
                ->getApplicableRules(TBT_RewardsReferral_Model_Special_Signup::ACTION_REFERRAL_SIGNUP);
        $points = Mage::getModel('rewards/points');
        foreach ($applicable_rules as $arr) {
            $points->add($arr);
        }
        return $points;
    }

    //@nelkaake Added on Wednesday May 5, 2010: always save the referral model
    //@nelkaake -d 10/11/10: 
    public function trigger($newCustomer, $always_save=false) {
        return parent::trigger($newCustomer, true);
    }

    public function getTransferReasonId() {
        return TBT_RewardsReferral_Model_Transfer_Reason_Signup::REASON_TYPE_ID;
    }

}