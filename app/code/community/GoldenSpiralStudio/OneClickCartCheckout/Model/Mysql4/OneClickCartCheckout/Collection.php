<?php

class GoldenSpiralStudio_OneClickCartCheckout_Model_Mysql4_OneClickCartCheckout_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('oneclickcartcheckout/oneclickcartcheckout');
    }
}