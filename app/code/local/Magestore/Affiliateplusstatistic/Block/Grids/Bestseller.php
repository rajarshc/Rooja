<?php
class Magestore_Affiliateplusstatistic_Block_Grids_Bestseller extends Mage_Adminhtml_Block_Dashboard_Grid
{
	public function __construct(){
        parent::__construct();
        $this->setId('gridBestseller');
    }
    
    protected function _prepareCollection(){
        $collection = Mage::getResourceModel('affiliateplus/transaction_collection');
        $collection->setModel('adminhtml/report_item');
        $itemTable = $collection->getTable('sales/order_item');
        $collection->getSelect()->join(array('i' => $itemTable),
            'main_table.order_id = i.order_id AND FIND_IN_SET(i.product_id, main_table.order_item_ids)',
            array('name', 'product_id', 'product_type', 'sku', 'base_price',
                'num_order_placed'  => 'SUM(i.qty_ordered)'
            )
        )->where('type = 3')
        ->group('product_id')
        ->order('num_order_placed DESC')
        ->order('base_price DESC');
        
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
      $this->addColumn('product_id', array(
          'header'    => Mage::helper('affiliateplus')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'product_id',
		  'type'	  => 'number',
		  'sortable'  => false,
      ));
      
      $storeId = $this->getRequest()->getParam('store');
      $store = Mage::app()->getStore($storeId);
      
      	$this->addColumn('name', array(
          'header'    => Mage::helper('affiliateplus')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
          'sortable'  => false,
      	));
      
        $this->addColumn('sku', array(
			'header'    => Mage::helper('affiliateplus')->__('SKU'),
			'index'     => 'sku',
			'sortable'  => false,
        ));
      
        $this->addColumn('base_price', array(
			'header'    => Mage::helper('affiliateplus')->__('Price'),
			'index'     => 'base_price',
			'type'		=> 'price',
			'currency_code'	=> $store->getBaseCurrencyCode(),
			'sortable'  => false,
        ));
      
        $this->addColumn('num_order_placed', array(
            'header'    => Mage::helper('affiliateplus')->__('Quantity Ordered'),
            'align'     => 'right',
            'width'     => '80px',
            'index'     => 'num_order_placed',
            'type'      => 'number',
            'sortable'  => false,
        ));
    }
    
    public function getRowUrl($row){
    	return $this->getUrl('adminhtml/catalog_product/edit',array(
    		'id' => $row->getId(),
    		'store' => $this->getRequest()->getParam('store')
    	));
    }
}