<?php

class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('affiliateplus/transaction')->getCollection();
		
		//event to join other table
		Mage::dispatchEvent('affiliateplus_adminhtml_join_transaction_other_table', array('collection' => $collection));
		$collection ->getSelect()
                    ->columns(array('customer_email'=>'if (main_table.customer_email="", "N/A", main_table.customer_email)'))
                    ->columns(array('order_number'=>'if (main_table.order_number IS NULL OR main_table.order_number="", "N/A", main_table.order_number)'))
                    ->columns(array('order_item_names'=>'if (main_table.order_item_names IS NULL, "N/A", main_table.order_item_names)'))
                ;
        //Zend_Debug::dump($collection->getSelect()->__toString());
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
		
		$this->addColumn('transaction_id', array(
			'header'    => Mage::helper('affiliateplus')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'transaction_id',
		));
	
		
		
		$this->addColumn('order_item_names', array(
			'header'    => Mage::helper('affiliateplus')->__('Product Name'),
			'align'     =>'left',
			'index'     => 'order_item_names',
            'filter_index'  =>  'if (main_table.order_item_names IS NULL, "N/A", main_table.order_item_names)',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_product',
		));
	
	
		$this->addColumn('account_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Affiliate Email'),
			'width'     => '150px',
			'index'     => 'account_email',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_account',
		));
	
		$this->addColumn('customer_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Customer Email'),
			'width'     => '150px',
			'align'     =>'left',
			'index'     => 'customer_email',
            'filter_index'  =>  'if (main_table.customer_email="", "NA", main_table.customer_email)',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_customer',
		));
	
		$this->addColumn('order_number', array(
			'header'    => Mage::helper('affiliateplus')->__('Order'),
			'width'     => '150px',
			'align'     =>'left',
			'index'     => 'order_number',
            'filter_index'  =>  'if (main_table.order_number="", "N/A", main_table.order_number)',
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
		
        //add event to add more column 
	  	Mage::dispatchEvent('affiliateplus_adminhtml_add_column_transaction_grid', array('grid' => $this));
        
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
	
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('affiliateplus')->__('Action'),
				'width'     => '100',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('affiliateplus')->__('View'),
						'url'       => array('base'=> '*/*/view'),
						'field'     => 'id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
		));
	
		//$this->addExportType('*/*/exportCsv', Mage::helper('affiliateplus')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('affiliateplus')->__('XML'));
		
		return parent::_prepareColumns();
	}
	
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('transaction_id');
        $this->getMassactionBlock()->setFormFieldName('transaction');
        
        $this->getMassactionBlock()->addItem('cancel', array(
            'label'     => Mage::helper('affiliateplus')->__('Cancel'),
            'url'       => $this->getUrl('*/*/massCancel'),
            'confirm'   => Mage::helper('affiliateplus')->__('This action cannot be restored. Are you sure?'),
        ));
        
        // add more mass action for transaction grid
        Mage::dispatchEvent('affiliateplus_adminhtml_add_massaction_transaction_grid', array('grid' => $this));
        
        return $this;
    }
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/view', array('id' => $row->getId()));
	}
	
	public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}