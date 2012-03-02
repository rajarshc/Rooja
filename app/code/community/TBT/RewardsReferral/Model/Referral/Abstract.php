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
abstract class TBT_RewardsReferral_Model_Referral_Abstract extends TBT_RewardsReferral_Model_Referral {

    public abstract function getReferralStatusId();

    public abstract function getTransferReasonId();

    public abstract function getTotalReferralPoints();

    public abstract function getReferralTransferMessage($newCustomer);

    public abstract function triggerEvent( Mage_Customer_Model_Customer $customer);
    
    /**
     * @deprecated moved logic into child classes under function triggerEvent
     * 
     * @param type $newCustomer
     * @param type $always_save @nelkaake Added on Wednesday May 5, 2010: - If $always_save is true the system will always save the referral model
     * @return TBT_RewardsReferral_Model_Referral_Abstract 
     */
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

    /**
     *
     * @return TBT_Rewards_Model_Customer 
     */
    public function getParentCustomer() {
        if (!$this->hasData('parent_customer')) {
            $id = $this->getReferralParentId();
            $ret = Mage::getModel('rewards/customer')->load($id);
            $this->setParentCustomer($ret);
        }
        return $this->getData('parent_customer');
    }

    /**
     * Get the accumulated points earned from this referral object
     * @param TBT_RewardsReferral_Model_Referral_Abstract $referralobj
     * @return TBT_Rewards_Model_Points 
     */
    public function getAccumulatedPoints($referralobj) {
        $references = Mage::getResourceModel( 'rewardsref/referral_transfer_reference_collection' );
        $points_earned = $references->getAccumulatedPoints( $referralobj );
        
        return $points_earned;
    }

    /**
     * @param TBT_RewardsReferral_Model_Referral_Abstract $referralobj
     * @return TBT_Rewards_Model_Points 
     */
    public function getPendingReferralPoints($referralobj) {
        
        $references = Mage::getResourceModel( 'rewardsref/referral_transfer_reference_collection' );
        $points_earned = $references->getPendingReferralPoints( $referralobj );
        
        return $points_earned;
    }

    public function hasReferralPoints() {
        return!$this->getTotalReferralPoints()->isEmpty();
    }

}