<?php

class Magestore_Affiliateplusprogram_Model_Value extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplusprogram/value');
    }
    
    public function loadAttributeValue($programId, $storeId, $attributeCode){
    	$attributeValue = $this->getCollection()
    		->addFieldToFilter('program_id',$programId)
    		->addFieldToFilter('store_id',$storeId)
    		->addFieldToFilter('attribute_code',$attributeCode)
    		->getFirstItem();
		$this->setData('program_id',$programId)
			->setData('store_id',$storeId)
			->setData('attribute_code',$attributeCode);
    	if ($attributeValue)
    		$this->addData($attributeValue->getData())
    			->setId($attributeValue->getId());
		return $this;
    }
}