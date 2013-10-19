<?php

class Magestore_Affiliateplus_Model_Banner extends Mage_Core_Model_Abstract
{
	protected $_store_id = null;
	
	protected $_eventPrefix = 'affiliateplus_banner';
    protected $_eventObject = 'affiliateplus_banner';
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('affiliateplus/banner');
    }
    
    public function setStoreId($value){
    	$this->_store_id = $value;
    	return $this;
    }
    
    public function getStoreId(){
    	return $this->_store_id;
    }
    
    public function getStoreAttributes(){
    	$storeAttribute = new Varien_Object(array(
    		'store_attribute'	=> array(
    			'title',
				//'width',
				//'height',
				'status'
    		)
    	));
    	
    	Mage::dispatchEvent($this->_eventPrefix.'_get_store_attributes',array(
    		$this->_eventObject	=> $this,
    		'attributes'		=> $storeAttribute,
    	));
    	
    	return $storeAttribute->getStoreAttribute();
    }
    
    public function load($id, $field=null){
    	parent::load($id,$field);
    	
    	Mage::dispatchEvent($this->_eventPrefix.'_load_store_value_before', $this->_getEventData());
    	
    	if ($this->getStoreId())
    		$this->loadStoreValue();
    	
    	Mage::dispatchEvent($this->_eventPrefix.'_load_store_value_after', $this->_getEventData());
    	
    	return $this;
    }
    
    public function loadStoreValue($storeId = null){
    	if (!$storeId)
    		$storeId = $this->getStoreId();
   		if (!$storeId)
   			return $this;
    	$storeValues = Mage::getModel('affiliateplus/banner_value')->getCollection()
			->addFieldToFilter('banner_id',$this->getId())
			->addFieldToFilter('store_id',$storeId);
    	
    	foreach ($storeValues as $value){
    		$this->setData($value->getAttributeCode().'_in_store',true);
    		$this->setData($value->getAttributeCode(),$value->getValue());
    	}
    	
    	return $this;
    }
    
    protected function _beforeSave(){
		if ($storeId = $this->getStoreId()){
    		$defaultBanner = Mage::getModel('affiliateplus/banner')->load($this->getId());
			$storeAttributes = $this->getStoreAttributes();
	    	foreach ($storeAttributes as $attribute){
	    		if ($this->getData($attribute.'_default')){
	    			$this->setData($attribute.'_in_store',false);
	    		}else{
	    			$this->setData($attribute.'_in_store',true);
	    			$this->setData($attribute.'_value',$this->getData($attribute));
	    		}
	    		$this->setData($attribute,$defaultBanner->getData($attribute));
	    	}
    	}
    	return parent::_beforeSave();
    }
    
    protected function _afterSave(){
    	if ($storeId = $this->getStoreId()){	
	    	$storeAttributes = $this->getStoreAttributes();
	    	foreach ($storeAttributes as $attribute){
	    		$attributeValue = Mage::getModel('affiliateplus/banner_value')
	    			->loadAttributeValue($this->getId(),$storeId,$attribute);
	    		if ($this->getData($attribute.'_in_store')){
	    			try{
	    				$attributeValue->setValue($this->getData($attribute.'_value'))->save();
	    			}catch(Exception $e){
	    				
	    			}
	    		}elseif($attributeValue && $attributeValue->getId()){
	    			try{
	    				$attributeValue->delete();
	    			}catch(Exception $e){
	    				
	    			}
	    		}
	    	}
    	}
    	return parent::_afterSave();
    }
    
    public function getAllIdsByStatus($status, $storeId){
    	$ids = array();
    	$collection = $this->getCollection()->setStoreId($storeId);
    	foreach ($collection as $item)
    		if ($item->getStatus() == $status)
    			$ids[] = $item->getId();
    	return $ids;
    }
}