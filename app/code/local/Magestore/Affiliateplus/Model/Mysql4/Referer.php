<?php

class Magestore_Affiliateplus_Model_Mysql4_Referer extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('affiliateplus/referer', 'referer_id');
    }
}