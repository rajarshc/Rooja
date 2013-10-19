<?php

class Magestore_Affiliateplus_Model_Mysql4_Action extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {
        $this->_init('affiliateplus/action', 'action_id');
    }
}