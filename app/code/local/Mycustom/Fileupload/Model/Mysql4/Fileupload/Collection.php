<?php

class Mycustom_Fileupload_Model_Mysql4_Fileupload_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        //parent::_construct();
        $this->_init('fileupload/fileupload');
    }
}