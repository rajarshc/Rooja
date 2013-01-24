<?php
class Aitoc_Aitsys_Model_Mysql4_Module_Status extends Aitoc_Aitsys_Abstract_Mysql4
{
//    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('aitsys/status', 'id');
    }
}