<?php

class TBT_Rewardssocial_Model_Facebook_Like_Transfer extends TBT_Rewards_Model_Transfer {

    public function _construct() {
        parent::_construct();
    }

    /**
     * Facebook Like in this context refers to a tuple
     * in the rewardssocial_facebook_like table which
     * contains information about the like action and 
     * article (product, category, cms page, etc).
     *
     * @param unknown_type $id
     * @return unknown
     */
    public function setFacebookLikeId($id) {
        $this->clearReferences();
        $this->setReferenceType(TBT_Rewardssocial_Model_Facebook_Like_Reference::REFERENCE_TYPE_ID);
        $this->setReferenceId($id);
        $this->setReasonId(TBT_Rewardssocial_Model_Facebook_Like_Reason::REASON_TYPE_ID);
        $this->_data[TBT_Rewardssocial_Model_Facebook_Like_Reference::REFERENCE_KEY] = $id;
        
        return $this;
    }

    public function isFacebookLike() {
        return ($this->getReferenceType() == TBT_Rewardssocial_Model_Facebook_Like_Reference::REFERENCE_TYPE_ID) || isset($this->_data['newsletter_id']);
    }

    /**
     * Gets all transfers associated with the given facebook like ID
     * 
     * @param int $facebook_like_id
     */
    public function getTransfersAssociatedWithFacebookLike($facebook_like_id) {
        return $this->getCollection()->addFilter('reference_type', TBT_Rewardssocial_Model_Facebook_Like_Reference::REFERENCE_TYPE_ID)->addFilter('reference_id', $facebook_like_id);
    }

    /**
     * Fetches the transfer helper
     *
     * @return TBT_Rewards_Helper_Transfer
     */
    protected function _getTransferHelper() {
        return Mage::helper('rewards/transfer');
    }

    /**
     * Fetches the rewards special validator singleton
     *
     * @return TBT_Rewards_Model_Special_Validator
     */
    protected function _getSpecialValidator() {
        return Mage::getSingleton('rewards/special_validator');
    }

    /**
     * Fetches the rewards special validator singleton
     *
     * @return TBT_Rewards_Model_Special_Validator
     */
    protected function _getFacebookLikeValidator() {
        return Mage::getSingleton('rewardssocial/facebook_like_validator');
    }

    /**
     * Creates customer points transfers
     *
     * @param unknown_type $customer
     * @param unknown_type $like_id
     * @param unknown_type $rule
     * @return unknown
     */
    public function createFacebookLikePoints($customer, $like_id, $rule) {
        
        $num_points = $rule->getPointsAmount();
        $currency_id = $rule->getPointsCurrencyId();
        $rule_id = $rule->getId();
        $transfer = $this->initTransfer($num_points, $currency_id, $rule_id);
        $store = $customer->getStore();
        
        if (!$transfer) {
            return false;
        }
        
        //get On-Hold initial status override
        if ($rule->getOnholdDuration() > 0) {
            $transfer->setEffectiveStart(date('Y-m-d H:i:s', strtotime("+{$rule->getOnholdDuration()} days")))
                ->setStatus(null, TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_TIME);
        } else {
            //get the default starting status
            $initial_status = Mage::getStoreConfig('rewards/InitialTransferStatus/AfterFacebookLike', $store);
            if (!$transfer->setStatus(null, $initial_status)) {
                return false;
            }
        }
        
        // Translate the message through the core translation engine (nto the store view system) in case people want to use that instead
        // This is not normal, but we found that a lot of people preferred to use the standard translation system insteaed of the 
        // store view system so this lets them use both.
        $initial_transfer_msg = Mage::getStoreConfig('rewards/transferComments/facebookLike', $store);
        $comments = Mage::helper('rewards')->__($initial_transfer_msg);
        
        $this->setFacebookLikeId($like_id)->setComments($comments)->setCustomerId($customer->getId())->save();
        
        return true;
        
    }

}