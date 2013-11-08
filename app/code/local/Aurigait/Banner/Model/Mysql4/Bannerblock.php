<?php
class Aurigait_Banner_Model_Mysql4_Bannerblock extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("banner/bannerblock", "id");
    }
}