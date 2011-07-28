<?php

class GoldenSpiralStudio_OneClickCartCheckout_Model_Mysql4_OneClickCartCheckout extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the oneclickcartcheckout_id refers to the key field in your database table.
        $this->_init('oneclickcartcheckout/oneclickcartcheckout', 'oneclickcartcheckout_id');
    }
}