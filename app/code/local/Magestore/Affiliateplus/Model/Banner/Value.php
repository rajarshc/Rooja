<?php

class Magestore_Affiliateplus_Model_Banner_Value extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplus/banner_value');
    }
    
    public function loadAttributeValue($bannerId, $storeId, $attributeCode){
    	$attributeValue = $this->getCollection()
    		->addFieldToFilter('banner_id',$bannerId)
    		->addFieldToFilter('store_id',$storeId)
    		->addFieldToFilter('attribute_code',$attributeCode)
    		->getFirstItem();
		$this->setData('banner_id',$bannerId)
			->setData('store_id',$storeId)
			->setData('attribute_code',$attributeCode);
    	if ($attributeValue)
    		$this->addData($attributeValue->getData())
    			->setId($attributeValue->getId());
		return $this;
    }
}