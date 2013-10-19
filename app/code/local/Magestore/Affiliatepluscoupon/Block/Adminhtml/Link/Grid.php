<?php

class Magestore_Affiliatepluscoupon_Block_Adminhtml_Link_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('linkGrid');
		$this->setDefaultSort('transaction_id');
		$this->setDefaultDir('DESC');
		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection(){
		$collection = Mage::getModel('affiliateplus/transaction')->getCollection();
        $collection->getSelect()->where('coupon_code = \'\' OR coupon_code IS NULL');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns(){
		$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
		
		$this->addColumn('transaction_id', array(
			'header'    => Mage::helper('affiliateplus')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'transaction_id',
		));
		
		$this->addColumn('account_name', array(
			'header'    => Mage::helper('affiliateplus')->__('Affiliate Email'),
			'width'     => '150px',
			'index'     => 'account_name',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_account',
		));
		
		//add event to add more column 
	  	Mage::dispatchEvent('affiliateplus_adminhtml_add_column_transaction_grid', array('grid' => $this));
		
		$this->addColumn('order_item_names', array(
			'header'    => Mage::helper('affiliateplus')->__('Product Name'),
			'align'     =>'left',
			'index'     => 'order_item_names',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_product',
		));
		
		$this->addColumn('customer_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Customer Email'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'customer_email',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_customer',
		));
	
		$this->addColumn('order_number', array(
			'header'    => Mage::helper('affiliateplus')->__('Order'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'order_number',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_order',
		));
	
		$this->addColumn('total_amount', array(
			'header'    => Mage::helper('affiliateplus')->__('Total Amount'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'total_amount',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,	
		));
		
		$this->addColumn('commission', array(
			'header'    => Mage::helper('affiliateplus')->__('Commission'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'commission',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,
		));
		
		$this->addColumn('discount', array(
			'header'    => Mage::helper('affiliateplus')->__('Discount'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'discount',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,
		));
		
		$this->addColumn('created_time', array(
			'header'    => Mage::helper('affiliateplus')->__('Time'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'created_time',
			'type'		=> 'date'
		));
	
		$this->addColumn('status', array(
			'header'    => Mage::helper('affiliateplus')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				1 => Mage::helper('affiliateplus')->__('Completed'),
				2 => Mage::helper('affiliateplus')->__('Pending'),
				3 => Mage::helper('affiliateplus')->__('Canceled'),
                4 => Mage::helper('affiliateplus')->__('On Hold'),
			),
		));
		
		$this->addColumn('store_id', array(
			'header'    => Mage::helper('affiliateplus')->__('Store view'),
			'align'     =>'left',
			'index'     =>'store_id',
			'type'      =>'store',
			'store_view'=>true,
		));
	
//		$this->addColumn('action',
//			array(
//				'header'    =>  Mage::helper('affiliateplus')->__('Action'),
//				'width'     => '100',
//				'type'      => 'action',
//				'getter'    => 'getId',
//				'actions'   => array(
//					array(
//						'caption'   => Mage::helper('affiliateplus')->__('View'),
//						'url'       => array('base'=> 'affiliateplusadmin/*/view'),
//						'field'     => 'id'
//					)
//				),
//				'filter'    => false,
//				'sortable'  => false,
//				'index'     => 'stores',
//				'is_system' => true,
//		));
		
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row){
		return $this->getUrl('affiliateplusadmin/adminhtml_transaction/view', array('id' => $row->getId()));
	}
	
	public function getGridUrl(){
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
