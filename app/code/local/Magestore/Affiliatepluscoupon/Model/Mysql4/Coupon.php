<?php

class Magestore_Affiliatepluscoupon_Model_Mysql4_Coupon extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
        $this->_init('affiliatepluscoupon/coupon','coupon_id');
    }
}