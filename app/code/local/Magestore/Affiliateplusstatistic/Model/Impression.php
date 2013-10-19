<?php

class Magestore_Affiliateplusstatistic_Model_Impression extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplusstatistic/impression');
    }
}