<?php

class Magestore_Affiliateplusprogram_Model_Transaction extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplusprogram/transaction');
    }
}