<?php

class TBT_Rewardssocial_Model_Facebook_Like_Reference extends TBT_Rewards_Model_Transfer_Reference_Abstract {

    const REFERENCE_TYPE_ID = 60; // TODO: does this conflict with anything else?
    const REFERENCE_KEY = 'facebook_like_id';
    
    public function clearReferences(&$transfer) {
        if ($transfer->hasData(self::REFERENCE_KEY)) {
            $transfer->unsetData(self::REFERENCE_KEY);
        }
        return $this;
    }

    public function getReferenceOptions() {
        $reference_options = array(self::REFERENCE_TYPE_ID => Mage::helper('rewardssocial')->__('Facebook Like'));
        return $reference_options;
    }

    /**
     * (non-PHPdoc)
     * @see TBT_Rewards_Model_Transfer_Reference_Abstract::loadReferenceInformation()
     */
    public function loadReferenceInformation(&$transfer) {
        $this->loadTransferId($transfer);
        return $this;
    }

    /**
     * 
     * @param TBT_Rewards_Model_Transfer $transfer
     * @param int $id
     */
    public function loadTransferId($transfer) {
        $id = $transfer->getReferenceId();
        $transfer->setReferenceType(TBT_Rewardssocial_Model_Facebook_Like_Reference::REFERENCE_TYPE_ID);
        $transfer->setReferenceId($id);
        $transfer->setData(self::REFERENCE_KEY, $id);
        
        return $this;
    }
    

}