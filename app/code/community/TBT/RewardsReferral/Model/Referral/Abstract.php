<?php

/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_RewardsReferral_Model_Referral_Abstract extends TBT_RewardsReferral_Model_Referral {

    public abstract function getReferralStatusId();

    public abstract function getTransferReasonId();

    public abstract function getTotalReferralPoints();

    public abstract function getReferralTransferMessage($newCustomer);

    //@nelkaake Added on Wednesday May 5, 2010: If $always_save is true the system will always save the referral model
    public function trigger($newCustomer, $always_save=false) {
        $this->loadByEmail($newCustomer->getEmail());
        //@nelkaake Added on Saturday June 26, 2010: Attempt to load the referral model through the session e-mail
        if (!$this->getReferralParentId()) {
            Mage::helper('rewardsref')->initateSessionReferral($newCustomer);
            //@nelkaake Added on Friday October 15, 2010:   
            $this->loadByEmail($newCustomer->getEmail());
        }
        //@nelkaake -a 16/11/10: If no parent id is still specified, then break out becuase referral points for this model are not necessary
        if (!$this->getReferralParentId()) {
            return $this;
        }
        $points = $this->getTotalReferralPoints();
        try {
            if (!$points->isEmpty() || $always_save) {
                $this->setReferralStatus($this->getReferralStatusId());    // update the referral status
                $this->setReferralChildId($newCustomer->getId());
                $this->save();
            }
            if (!$points->isEmpty()) {
                foreach ($points->getPoints() as $cid => $points_amount) {
                    $t = Mage::getModel('rewardsref/transfer')->create(
                                    $points_amount, $cid, $this->getReferralParentId(), $newCustomer->getId(), $this->getReferralTransferMessage($newCustomer), $this->getTransferReasonId()
                    );
                }
                //@nelkaake Added on Wednesday July 21, 2010: 
                $parent = $this->getParentCustomer();
                if ($parent->getRewardsrefNotifyOnReferral()) {
                    $msg = $this->getReferralTransferMessage($newCustomer);
                    $this->sendConfirmation($parent, $newCustomer->getEmail(), $newCustomer->getName(), $msg);
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    public function getParentCustomer() {
        if (!$this->hasData('parent_customer')) {
            $id = $this->getReferralParentId();
            $ret = Mage::getModel('rewards/customer')->load($id);
            $this->setParentCustomer($ret);
        }
        return $this->getData('parent_customer');
    }

    public function getAccumulatedPoints($referralobj) {
        $col = Mage::getModel('rewardsref/transfer')
                ->getCollection()
                ->addFieldToFilter('reference_id', $referralobj->getReferralChildId())
                ->addFieldToFilter('reference_type', TBT_RewardsReferral_Model_Transfer::REFERENCE_REFERRAL)
                ->addFieldToFilter('customer_id', $referralobj->getReferralParentId())
                ->selectOnlyPosTransfers()
                ->sumPoints();
        $points_earned = Mage::getModel('rewards/points');
        foreach ($col as $points) {
            $points_earned->add($points->getCurrencyId(), (int) $points->getPointsCount());
        }
        return $points_earned;
    }

    public function hasReferralPoints() {
        return!$this->getTotalReferralPoints()->isEmpty();
    }

}