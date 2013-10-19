<?php

class Magestore_Affiliateplus_Model_Account_Value extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplus/account_value');
    }
    
    public function loadAttributeValue($accountId, $storeId, $attributeCode){
    	$attributeValue = $this->getCollection()
    		->addFieldToFilter('account_id',$accountId)
    		->addFieldToFilter('store_id',$storeId)
    		->addFieldToFilter('attribute_code',$attributeCode)
    		->getFirstItem();
		$this->setData('account_id',$accountId)
			->setData('store_id',$storeId)
			->setData('attribute_code',$attributeCode);
    	if ($attributeValue)
    		$this->addData($attributeValue->getData())
    			->setId($attributeValue->getId());
		return $this;
    }
}