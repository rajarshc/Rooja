<?php

class TBT_RewardsReferral_Model_Mysql4_Referral_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        $this->_init('rewardsref/referral');
    }

    protected function _initSelect() {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->join(
                array('cust' => $this->getTable('customer/entity')), 'referral_parent_id = cust.entity_id'
        );

        return $this;
    }

    public function addEmailFilter($email) {
        $this->getSelect()->where('referral_email = ?', $email);
        return $this;
    }

    public function addFlagFilter($status) {
        $this->getSelect()->where('referral_status = ?', $status);
        return $this;
    }

    public function addClientFilter($id) {
        $this->getSelect()->where('referral_parent_id = ?', $id);
        return $this;
    }

}