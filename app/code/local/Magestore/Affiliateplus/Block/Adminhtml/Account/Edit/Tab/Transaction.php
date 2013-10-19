<?php
class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tab_Transaction
 extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('transactiongrid');
        $this->setDefaultSort('transaction_id');
        $this->setUseAjax(true);
    }
	
    protected function _addColumnFilterToCollection($column)
    {
		return parent::_addColumnFilterToCollection($column);
    }


    //return category collection filtered by store
	protected function _prepareCollection()
    {
		$accountId	= $this->getRequest()->getParam('id'); 
		$collection = Mage::getModel('affiliateplus/transaction')->getCollection();

		//event to join other transaction
		Mage::dispatchEvent('affiliateplus_adminhtml_join_transaction_other_table', array('collection' => $collection));

		$collection->addFieldToFilter('account_id', $accountId);
		
		if ($storeId = $this->getRequest()->getParam('store'))
			$collection->addFieldToFilter('store_id',$storeId);
		
        $collection ->getSelect()
                    ->columns(array('customer_email'=>'if (main_table.customer_email="", "N/A", main_table.customer_email)'))
                    ->columns(array('order_number'=>'if (main_table.order_number="", "N/A", main_table.order_number)'))
                    ->columns(array('order_item_names'=>'if (main_table.order_item_names IS NULL, "N/A", main_table.order_item_names)'))
                ;
        
		$this->setCollection($collection);
		
		//event to join other transaction
		Mage::dispatchEvent('affiliateplus_adminhtml_after_set_transaction_collection', array('grid' => $this, 'account_id' => $accountId, 'store' => $storeId));
		
		return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

		$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
		$prefix = 'transaction_grid_';
		$this->addColumn($prefix.'transaction_id', array(
            'header'    => Mage::helper('affiliateplus')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'transaction_id',
            'filter_index'	=> 'main_table.transaction_id',
        ));
	
		$this->addColumn($prefix.'customer_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Customer Email'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'customer_email',
            'filter_index'  =>  'if (main_table.customer_email="", "NA", main_table.customer_email)',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_customer',
		));
	
		$this->addColumn($prefix.'order_number', array(
			'header'    => Mage::helper('affiliateplus')->__('Order'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'order_number',
            'filter_index'  =>  'if (main_table.order_number="", "N/A", main_table.order_number)',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_order',
		));
	
		$this->addColumn($prefix.'order_item_names', array(
			'header'    => Mage::helper('affiliateplus')->__('Product Name'),
			'align'     =>'left',
			'index'     => 'order_item_names',
            'filter_index'  =>  'if (main_table.order_item_names IS NULL, "N/A", main_table.order_item_names)',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_product',
		));
	
		$this->addColumn($prefix.'total_amount', array(
			'header'    => Mage::helper('affiliateplus')->__('Total Amount'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'total_amount',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,	
		));
		
		$this->addColumn($prefix.'commission', array(
			'header'    => Mage::helper('affiliateplus')->__('Commission'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'commission',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,
		));
		
		$this->addColumn($prefix.'discount', array(
			'header'    => Mage::helper('affiliateplus')->__('Discount'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'discount',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,
		));
		
		
		//add event to add more column 
	  	Mage::dispatchEvent('affiliateplus_adminhtml_add_column_account_transaction_grid', array('grid' => $this));
		
		$this->addColumn($prefix.'created_time', array(
			'header'    => Mage::helper('affiliateplus')->__('Time'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'created_time',
			'type'		=> 'date'
		));
		
		$this->addColumn($prefix.'status', array(
			'header'    => Mage::helper('affiliateplus')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				1 => 'Completed',
				2 => 'Pending',
				3 => 'Canceled',
			),
		));
		
    }

    //return url
	public function getGridUrl(){
		return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/transactionGrid', array(
				'_current'=>true,
				'id'=>$this->getRequest()->getParam('id'),
				'store'	=> $this->getRequest()->getParam('store')
            ));
	
	}
	
	public function getRowUrl($row) {
		$id = $row->getTransactionId();
		return $this->getUrl('*/adminhtml_transaction/view', array(
			'id' => $id,
		));
	}
	
    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }	 	

}