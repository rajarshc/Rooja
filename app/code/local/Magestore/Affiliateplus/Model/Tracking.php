<?php

class Magestore_Affiliateplus_Model_Tracking extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'affiliateplus_tracking';
    protected $_eventObject = 'affiliateplus_tracking';

    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/tracking');
    }

    /**
     * get Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    public function _getHelper() {
        return Mage::helper('affiliateplus/config');
    }
}
