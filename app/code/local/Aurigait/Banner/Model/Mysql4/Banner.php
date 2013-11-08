<?php
 
class Aurigait_Banner_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('banner/banner', 'banner_id');
    }
}
