<?php

class Mycustom_Fileupload_Model_Fileupload extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fileupload/fileupload');
    }
}