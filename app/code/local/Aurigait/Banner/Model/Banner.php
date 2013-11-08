<?php
 
class Aurigait_Banner_Model_Banner extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('banner/banner');
    }
public function loadByField($field,$value){
        $id = $this->getResource()->loadByField($field,$value);
        $this->load($id);
    }
}
