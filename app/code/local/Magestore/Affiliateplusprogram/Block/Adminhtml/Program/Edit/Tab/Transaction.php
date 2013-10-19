<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit_Tab_Transaction extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
        parent::__construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection(){
    	$programId = $this->getRequest()->getParam('id');
    	$collection = Mage::getModel('affiliateplusprogram/transaction')->getCollection()
    		->addFieldToFilter('main_table.program_id',$programId);
    	
    	$collection->getSelect()->join(
    		array('transaction' => $collection->getTable('affiliateplus/transaction')),
			'main_table.transaction_id = transaction.transaction_id',
			array(
				'customer_id',
				'customer_email',
				'created_time',
				'status',
			)
    	);
    	
		if ($storeId = $this->getStore()->getId())
			$collection->addFieldToFilter('transaction.store_id',$storeId);
		$collection	->getSelect()
					->columns(array('order_number'=>'if (transaction.order_number="", "N/A", transaction.order_number)'))
                    ->columns(array('order_item_names'=>'if (transaction.order_item_names IS NULL, "N/A", transaction.order_item_names)'))
					;
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
    	$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
		$this->addColumn('transaction_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'transaction_id',
            'filter_index'	=> 'main_table.transaction_id',
        ));
        
        $this->addColumn('transaction_account_name', array(
			'header'    => Mage::helper('affiliateplus')->__('Affiliate Account'),
			'width'     => '150px',
			'align'     => 'right',
			'index'     => 'account_name',
			'renderer'  => 'affiliateplusprogram/adminhtml_program_renderer_account',
            'filter_index'	=> 'main_table.account_name',
		));
		
		$this->addColumn('transaction_customer_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Customer Email'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'customer_email',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_customer',
			'filter_index'	=> 'if (transaction.customer_email="", "N/A", transaction.customer_email)'
		));
	
		$this->addColumn('transaction_order_number', array(
			'header'    => Mage::helper('affiliateplus')->__('Order'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'order_number',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_order',
            'filter_index'	=> 'if (transaction.order_number="", "N/A", transaction.order_number)',
		));
	
		$this->addColumn('transaction_order_item_names', array(
			'header'    => Mage::helper('affiliateplus')->__('Product Name'),
			'align'     =>'left',
			'index'     => 'order_item_names',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_product',
            'filter_index'	=> 'if (transaction.order_item_names="", "N/A", transaction.order_item_names)',
		));
	
		$this->addColumn('transaction_total_amount', array(
			'header'    => Mage::helper('affiliateplus')->__('Total Amount'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'total_amount',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,	
            'filter_index'	=> 'main_table.total_amount',
		));
		
		$this->addColumn('transaction_commission', array(
			'header'    => Mage::helper('affiliateplus')->__('Commission'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'commission',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,
            'filter_index'	=> 'main_table.commission',
		));
                /* thanhpv 18/10/2012 */
		//add event to add more column 
	  	Mage::dispatchEvent('affiliateplus_adminhtml_add_column_transaction_grid', array('grid' => $this));
		$this->addColumn('transaction_status', array(
			'header'    => Mage::helper('affiliateplus')->__('Status'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'status',
			'type'  	=> 'options',
		  	'options'   => array(
				1 => 'Completed',
				2 => 'Pending',
				3 => 'Canceled',
			),
			'filter_index'	=> 'transaction.status'
		));
		
		$this->addColumn('transaction_created_time', array(
			'header'    => Mage::helper('affiliateplus')->__('Created Date'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'created_time',
			'type'		=> 'date',
			'filter_index'	=> 'transaction.created_time'
		));
    }
    
    public function getRowUrl($row){
		return $this->getUrl('affiliateplusadmin/adminhtml_transaction/view', array('id' => $row->getTransactionId()));
	}
	
	public function getGridUrl(){
        return $this->getUrl('*/*/transactionGrid',array(
        	'_current'	=>true,
        	'id'		=>$this->getRequest()->getParam('id'),
        	'store'		=>$this->getRequest()->getParam('store')
    	));
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