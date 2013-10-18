<?php

class Magestore_Affiliateplus_Model_Mysql4_Payment_Credit extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {
        $this->_init('affiliateplus/credit', 'id');
    }
}
