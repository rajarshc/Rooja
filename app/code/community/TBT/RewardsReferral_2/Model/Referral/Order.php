<?php

/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Referral_Order extends TBT_RewardsReferral_Model_Referral_Abstract {
    const REFERRAL_STATUS_ID = TBT_RewardsReferral_Model_Referral_Firstorder::STATUS_REFERRAL_FIRST_ORDER;

    public function getReferralStatusId() {
        return self::REFERRAL_STATUS_ID;
    }

    public function getReferralTransferMessage($newCustomer) {
        return Mage::getStoreConfig('rewards/transferComments/referralOrder');
    }

    public function getReferralPointsForOrder($ro_rule, $order) {
        $percent = $ro_rule->getPointsAmount();
        $full_earning = Mage::getModel('rewards/points')->set($order->getTotalEarnedPoints());
        $partial_earning = $full_earning->getPercent($percent);
        return $partial_earning;
    }

    public function getTotalReferralPoints() {
        $points = Mage::getModel('rewards/points');
        if ($this->hasOrder()) {
            $applicable_rules = $this->_getApplicableReferralOrderRules();
            foreach ($applicable_rules as $arr) {
                $points->add($this->getReferralPointsForOrder($arr, $this->getOrder()));
            }
        }
        return $points;
    }

    public function getTransferReasonId() {
        return TBT_RewardsReferral_Model_Transfer_Reason_Order::REASON_TYPE_ID;
    }

    protected function _getApplicableReferralOrderRules() {
        $applicable_rules = Mage::getSingleton('rewardsref/validator')
                ->getApplicableRules(TBT_RewardsReferral_Model_Special_Order::ACTION_REFERRAL_ORDER);
        return $applicable_rules;
    }

    public function hasReferralPoints() {
        foreach ($this->_getApplicableReferralOrderRules() as $arr) {
            if ($arr->getPointsAmount() > 0)
                return true;
        }
        return false;
    }

}