<?php

class Magestore_Affiliateplusprogram_Model_Category extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplusprogram/category');
    }
    
    public function saveAll(){
    	if ($this->getProgramId()){
    		if ($this->getStoreId()){
    			$this->saveAllInStore();
    		}else {
    			$stores = Mage::app()->getStores(true);
    			foreach ($stores as $store)
    				$this->setStoreId($store->getId())->saveAllInStore();
    		}
    	}
    	return $this;
    }
    
    public function saveAllInStore(){
    	if (is_array($this->getCategoryIds()))
    		$newCategoryIds = array_combine($this->getCategoryIds(),$this->getCategoryIds());
    	
    	$collection = $this->getCollection()
    		->addFieldToFilter('program_id',$this->getProgramId())
    		->addFieldToFilter('store_id',$this->getStoreId());
    	foreach ($collection as $item){
    		$categoryId = $item->getCategoryId();
    		if (in_array($categoryId,$newCategoryIds))
    			unset($newCategoryIds[$categoryId]);
    		else 
    			$this->setId($item->getId())->delete();
    	}
    	return $this->addCategory($newCategoryIds);
    }
    
    public function addCategory($categoryIds){
    	foreach ($categoryIds as $categoryId)
    		if (is_numeric($categoryId))
    			$this->setCategoryId($categoryId)->setId(null)->save();
    	return $this;
    }
}