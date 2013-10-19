<?php

class Magestore_Affiliateplus_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the affiliateplus_id refers to the key field in your database table.
        $this->_init('affiliateplus/transaction', 'transaction_id');
    }
}