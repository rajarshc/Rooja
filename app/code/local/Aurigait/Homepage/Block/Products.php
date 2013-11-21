<?php
class Aurigait_Homepage_Block_Products extends Mage_Core_Block_Template{
	
	public function getProducts(){
	
		$category_id=$this->getCategoryId();
		$category = Mage::getModel('catalog/category')->load($category_id);
		
		
		Mage::getModel('catalog/layer')->prepareProductCollection($collection);
		return $collection;
	}
}
