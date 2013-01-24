<?php

class Mycustom_Fileupload_Model_Mysql4_Fileupload extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the fileupload_id refers to the key field in your database table.
        $this->_init('fileupload/fileupload', 'fileupload_id');
    }
}