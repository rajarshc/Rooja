<?php

class Magestore_Affiliateplus_Model_Mysql4_Tracking extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {
        $this->_init('affiliateplus/tracking', 'tracking_id');
    }
}
