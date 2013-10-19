<?php

class Magestore_Affiliateplus_Helper_Config extends Mage_Core_Helper_Abstract
{
	public function getGeneralConfig($code, $store = null){
		return Mage::getStoreConfig('affiliateplus/general/'.$code,$store);
	}
    
    public function getCommissionConfig($code, $store = null){
        return Mage::getStoreConfig('affiliateplus/commission/'.$code,$store);
    }
	
    public function getDiscountConfig($code, $store = null){
        return Mage::getStoreConfig('affiliateplus/discount/'.$code,$store);
    }
    
	public function getPaymentConfig($code, $store = null){
		return Mage::getStoreConfig('affiliateplus/payment/'.$code,$store);
	}
	
	public function getEmailConfig($code, $store = null){
		return Mage::getStoreConfig('affiliateplus/email/'.$code,$store);
	}
	
    /**
     * get Account and Sharing Config
     * 
     * @param string $code
     * @param mixed $store
     * @return mixed
     */
	public function getSharingConfig($code, $store = null){
		return Mage::getStoreConfig('affiliateplus/account/'.$code,$store);
	}
	
	public function getMaterialConfig($code, $store = null){
		return Mage::getStoreConfig('affiliateplus/general/material_'.$code,$store);
	}
	
	public function disableMaterials(){
		return (Mage::helper('affiliateplus/account')->accountNotLogin() || !$this->getMaterialConfig('enable'));
	}
	
	public function getReferConfig($code, $store = null){
		return Mage::getStoreConfig('affiliateplus/refer/'.$code,$store);
	}
        public function getActionConfig($code, $store = null){
		return Mage::getStoreConfig('affiliateplus/action/'.$code,$store);
	}
}