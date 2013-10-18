<?php

class Magestore_Affiliateplusstatistic_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function disableMenu() {
        if (!Mage::getStoreConfig('affiliateplus/statistic/enable')) {
            return true;
        }
        return Mage::helper('affiliateplus/account')->accountNotLogin();
    }
}
