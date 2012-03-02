<?php

class TBT_RewardsReferral_Model_Referral_Status extends Varien_Object {

    public function getAllOptionsArray() {
        $options = array(
            1 => Mage::helper('rewardsref')->__('Signed Up'),
            2 => Mage::helper('rewardsref')->__('Made First Order'),
            3 => Mage::helper('rewardsref')->__('Made Order'),
            0 => Mage::helper('rewardsref')->__('Message Sent'),
        );
        return $options;
    }

    public function getStatusCaption($status_id) {
        $options = $this->getAllOptionsArray();
        if (isset($options[$status_id])) {
            return $options[$status_id];
        } else {
            return $this->getUnknownCaption();
        }
    }

    public function getUnknownCaption() {
        return Mage::helper('rewardsref')->__('Unknown');
    }

}