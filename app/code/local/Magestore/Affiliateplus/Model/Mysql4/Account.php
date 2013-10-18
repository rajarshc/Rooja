<?php

class Magestore_Affiliateplus_Model_Mysql4_Account extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {
        $this->_init('affiliateplus/account', 'account_id');
    }
}