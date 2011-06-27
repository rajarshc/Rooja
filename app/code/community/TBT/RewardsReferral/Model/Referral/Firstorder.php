<?php

/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Referral_Firstorder extends TBT_RewardsReferral_Model_Referral_Abstract {
    const STATUS_REFERRAL_FIRST_ORDER = 2;

    public function getReferralStatusId() {
        return self::STATUS_REFERRAL_FIRST_ORDER;
    }

    public function getTotalReferralPoints() {
        $applicable_rules = Mage::getSingleton('rewardsref/validator')
                ->getApplicableRules(TBT_RewardsReferral_Model_Special_Firstorder::ACTION_REFERRAL_FIRST_ORDER);
        $points = Mage::getModel('rewards/points');
        foreach ($applicable_rules as $arr) {
            $points->add($arr);
        }
        return $points;
    }

    public function getReferralTransferMessage($newCustomer) {
        return Mage::getStoreConfig('rewards/transferComments/referralFirstOrder');
    }

    public function isConfirmed($email) {
        $collection = $this->getCollection()
                ->addFieldToFilter('referral_status', array('gte' => self::STATUS_REFERRAL_FIRST_ORDER));
        $collection->addEmailFilter($email);
        return $collection->count() > 0;
    }

    public function getTransferReasonId() {
        return TBT_RewardsReferral_Model_Transfer_Reason_Firstorder::REASON_TYPE_ID;
    }

}