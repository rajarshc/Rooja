<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
	protected $_categoryIds;
    protected $_selectedNodes = null;
    
    public function __construct(){
    	parent::__construct();
    	$this->setTemplate('affiliateplusprogram/categories.phtml');
    }
    
    public function getCategoryCollection(){
    	$storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
    	$collection = $this->getData('category_collection');
    	if (is_null($collection)){
    		$collection = Mage::getResourceModel('catalog/category_collection');
    		$collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount($this->_withProductCount)
                ->setStoreId($storeId);
            $this->setData('category_collection',$collection);
    	}
    	return $collection;
    }
    
    public function isReadonly(){
    	return false;
    }
    
    protected function getCategoryIds(){
    	if (!Mage::registry('program_categories'))
    		return array();
    	return Mage::registry('program_categories');
    }
    
    public function getIdsString(){
    	$categoryIds = $this->getCategoryIds();
    	if (is_array($categoryIds))
    		return implode(',',$categoryIds);
    	return parent::getIdsString();
    }
    
    /**
     * get Current Program
     *
     * @return Magestore_Affiliateplusprogram_Model_Program
     */
    public function getProgram(){
    	return Mage::getModel('affiliateplusprogram/program')->load($this->getRequest()->getParam('id'));
    }
  
	/**
	 * get currrent store
	 *
	 * @return Mage_Core_Model_Store
	 */
	public function getStore(){
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}
}