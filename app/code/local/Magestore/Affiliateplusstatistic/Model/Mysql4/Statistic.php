<?php

class Magestore_Affiliateplusstatistic_Model_Mysql4_Statistic extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('affiliateplusstatistic/statistic', 'id');
    }
}