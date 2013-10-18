<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getProgram() && $this->getProgram()->getId()){
        	$this->setDefaultFilter(array('in_products' => 1));
        }
    }
    
    protected function _addColumnFilterToCollection($column){
    	if ($column->getId() == 'in_products'){
    		$productIds = $this->_getSelectedProducts();
    		if (empty($productIds)) $productIds = 0;
    		if ($column->getFilter()->getValue())
    			$this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
    		elseif ($productIds)
    			$this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
    		return $this;
    	}
    	return parent::_addColumnFilterToCollection($column);
    }
    
    protected function _prepareCollection(){
    	$collection = Mage::getModel('catalog/product')->getCollection()
    		->addAttributeToSelect('*');
    	
    	if ($storeId = $this->getRequest()->getParam('store', 0))
    		$collection->addStoreFilter($storeId);
    	
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
    	$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
    	
    	$this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
			'type'              => 'checkbox',
			'name'              => 'in_products',
			'values'            => $this->_getSelectedProducts(),
			'align'             => 'center',
			'index'             => 'entity_id',
			'use_index'			=> true,
        ));
    	
		$this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        
        $this->addColumn('product_name', array(
			'header'    => Mage::helper('catalog')->__('Name'),
			'align'     => 'left',
			'index'     => 'name',
		));
		
		$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
			->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
			->load()
			->toOptionHash();
		$this->addColumn('product_set_name', array(
			'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
			'align'     => 'left',
			'index'     => 'attribute_set_id',
			'type'		=> 'options',
			'options'	=> $sets,
		));
		
		$this->addColumn('product_status',array(
            'header'=> Mage::helper('catalog')->__('Status'),
            'width' => '90px',
            'index' => 'status',
            'type'  => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));
        
        $this->addColumn('product_visibility',array(
            'header'=> Mage::helper('catalog')->__('Visibility'),
            'width' => '90px',
            'index' => 'visibility',
            'type'  => 'options',
            'options' => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));
        
        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));
		
        $this->addColumn('product_price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'  	=> 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price'
        ));
        
		$this->addColumn('position', array(
			'header'    => Mage::helper('affiliateplus')->__('Position'),
			'name'		=> 'position',
			'type'		=> 'number',
			'index'     => 'position',
			'editable'	=> true,
			'edit_only'	=> true,
		));
    }
    
    public function getRowUrl($row){
		return $this->getUrl('adminhtml/catalog_product/edit', array(
			'id' 	=> $row->getId(),
			'store'	=>$this->getRequest()->getParam('store')
		));
	}
	
	public function getGridUrl(){
        return $this->getUrl('*/*/productGrid',array(
        	'_current'	=>true,
        	'id'		=>$this->getRequest()->getParam('id'),
        	'store'		=>$this->getRequest()->getParam('store')
    	));
    }
    
    protected function _getSelectedProducts(){
    	$products = $this->getProducts();
    	if (!is_array($products))
    		$products = array_keys($this->getSelectedRelatedProducts());
    	return $products;
    }
    
    public function getSelectedRelatedProducts(){
    	$products = array();
    	$program = $this->getProgram();
    	$productCollection = Mage::getResourceModel('affiliateplusprogram/product_collection')
    		->addFieldToFilter('program_id',$program->getId());
    	
    	$storeId = $this->getRequest()->getParam('store', 0);
    	$productCollection->addFieldToFilter('store_id',$storeId);
    	
    	foreach ($productCollection as $product)
    		$products[$product->getProductId()] = array('position' => 0);
    	return $products;
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