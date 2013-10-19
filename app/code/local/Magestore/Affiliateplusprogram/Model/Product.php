<?php

class Magestore_Affiliateplusprogram_Model_Product extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplusprogram/product');
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
    	$newProductIds = array();
    	if ($this->getProductIds() && is_array($this->getProductIds()))
    		$newProductIds = array_combine($this->getProductIds(),$this->getProductIds());
    	
    	$collection = $this->getCollection()
    		->addFieldToFilter('program_id',$this->getProgramId())
    		->addFieldToFilter('store_id',$this->getStoreId());
    	foreach ($collection as $item){
    		$productId = $item->getProductId();
    		if (in_array($productId,$newProductIds))
    			unset($newProductIds[$productId]);
    		else 
    			$this->setId($item->getId())->delete();
    	}
    	return $this->addProduct($newProductIds);
    }
    
    public function addProduct($productIds){
    	foreach ($productIds as $productId)
    		if (is_numeric($productId)){
  				try {
    				$this->setProductId($productId)->setId(null)->save();
  				} catch (Exception $e){}
   			}
    	return $this;
    }
    
    public function saveAllProducts(){
    	$this->setStoreId(Mage::app()->getDefaultStoreView()->getId());
    	$newProductIds = array();
    	if ($this->getProductIds() && is_array($this->getProductIds()))
    		$newProductIds = array_combine($this->getProductIds(),$this->getProductIds());
    	
    	$collection = $this->getCollection()
    		->addFieldToFilter('program_id',$this->getProgramId());
    	foreach ($collection as $item){
    		$productId = $item->getProductId();
    		if (in_array($productId,$newProductIds)){
    			unset($newProductIds[$productId]);
    		} else {
    			try{
    				$this->setId($item->getId())->delete();
   				} catch (Exception $e){}
   			}
    	}
    	return $this->addProduct($newProductIds);
    }
}