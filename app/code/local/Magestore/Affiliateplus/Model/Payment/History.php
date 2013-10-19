<?php

class Magestore_Affiliateplus_Model_Payment_History extends Mage_Core_Model_Abstract
{
    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/payment_history');
    }
    
    public function getStatusLabel() {
        $statuses = array(
            1 =>  Mage::helper('affiliateplus')->__('Pending'),
            2 =>  Mage::helper('affiliateplus')->__('Processing'),
            3 =>  Mage::helper('affiliateplus')->__('Completed'),
            4 =>  Mage::helper('affiliateplus')->__('Canceled')
        );
        if (isset($statuses[$this->getStatus()])) return $statuses[$this->getStatus()];
        return '';
    }
}
