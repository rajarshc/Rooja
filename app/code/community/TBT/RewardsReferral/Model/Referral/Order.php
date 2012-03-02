<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Referral_Order extends TBT_RewardsReferral_Model_Referral_Abstract {
    const REFERRAL_STATUS_ID = 3;

    public function getReferralStatusId() {
        return self::REFERRAL_STATUS_ID;
    }

    public function getReferralTransferMessage($newCustomer) {
        return Mage::getStoreConfig('rewards/transferComments/referralOrder');
    }

    public function getReferralPointsForOrder($ro_rule, $order) {
        $simpleAction = $ro_rule->getSimpleAction();
        $partial_earning = 0;
        
        if ( $simpleAction == 'by_fixed' ) {
            
            $partial_earning = Mage::getModel('rewards/points')->set(array(
                $ro_rule->getPointsCurrencyId() => $ro_rule->getPointsAmount()
            ));
        
        } else {
            
            // Prior to Sweet Tooth Ref 3.1, there was nothing but by_percent, so default to by_percent if nothing is specified
            $simpleAction == 'by_percent';
            
            $percent = $ro_rule->getPointsAmount();
            $full_earning = Mage::getModel('rewards/points')->set($order->getTotalEarnedPoints());
            $partial_earning = $full_earning->getPercent($percent);
        }
        
        return $partial_earning;
    }
    
    /**
     *
     * requires setOrder($order) to be set!
     * requires $referralCustomerId to be set!
     * 
     * @param type $referralCustomerId
     * @return type 
     */
    public function getTotalReferralPoints($referralCustomerId=null) {
        $points = Mage::getModel('rewards/points');
        if ($this->hasOrder()) {
            $applicable_rules = $this->_getApplicableReferralOrderRules();
            foreach ($applicable_rules as $arr) {
                $points->add($this->getReferralPointsForOrder($arr, $this->getOrder(), $referralCustomerId));
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

    /**
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param int $orderId
     * @return TBT_RewardsReferral_Model_Referral_Order 
     */
    public function triggerEvent(Mage_Customer_Model_Customer $customer, $orderId = null ) {
        $this->loadByEmail($customer->getEmail());
        if (!$this->getReferralParentId()) {
            Mage::helper('rewardsref')->initateSessionReferral($customer);
            $this->loadByEmail($customer->getEmail());
            if (!$this->getReferralParentId()) {
                return $this;
            }
        }
                
        // update referral status
        $this->setReferralChildId($customer->getId());
        $this->setReferralStatus($this->getReferralStatusId());
        $this->save();
        
        $points = $this->getTotalReferralPoints($customer->getId());
        if ($points->isEmpty()) {
            return $this;
        }
        
        try {
            foreach ($points->getPoints() as $currencyId => $points_amount) {
                $transfer = Mage::getModel('rewardsref/transfer');
                $transferStatus = Mage::getStoreConfig('rewards/InitialTransferStatus/ReferralOrder');
                $transfer->create($points_amount, $currencyId, $this->getReferralParentId(), $customer->getId(), $this->getReferralTransferMessage($customer), $this->getTransferReasonId(), $transferStatus, $orderId);
            }
            
            // send affiliate an email of the transaction
            $affiliate = $this->getParentCustomer();
            if ($affiliate->getRewardsrefNotifyOnReferral()) {
                $msg = $this->getReferralTransferMessage($customer);
                $this->sendConfirmation($affiliate, $customer->getEmail(), $customer->getName(), $msg, (string)$points);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

}