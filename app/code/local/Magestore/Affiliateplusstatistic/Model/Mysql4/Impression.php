<?php

class Magestore_Affiliateplusstatistic_Model_Mysql4_Impression extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('affiliateplusstatistic/impression', 'action_id');
    }
}