<?php

class Magestore_Affiliateplus_Model_Mysql4_Banner_Value_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('affiliateplus/banner_value');
    }
}