<?php
class Magestore_Affiliateplusstatistic_Block_Grids_Affiliates extends Mage_Adminhtml_Block_Dashboard_Grid
{
	public function __construct(){
        parent::__construct();
        $this->setId('gridAffiliates');
    }
    
    protected function _prepareCollection(){
    	$collection = Mage::getResourceModel('affiliateplus/account_collection');
    	
    	$transactionTable = $collection->getTable('affiliateplus/transaction');
    	$collection->getSelect()->joinLeft(
    		array('ts' => $transactionTable),
    		'main_table.account_id = ts.account_id AND ts.type = 3',
    		array(
                'amount' => 'SUM(ts.total_amount)',
                'num_order_placed'  => 'COUNT(ts.transaction_id)'
            )
    	)->where('ts.status = 1')
    	->group('ts.account_id')
    	->order('amount DESC');
    	
    	if ($storeId = $this->getRequest()->getParam('store')){
			$collection->setStoreId($storeId);
			$collection->getSelect()->where("ts.store_id = $storeId");
    	}
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
      $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
      $this->addColumn('account_id', array(
          'header'    => Mage::helper('affiliateplus')->__('ID'),
          'width'     => '50px',
          'align'     =>'right',
          'index'     => 'account_id',
		  'type'	  => 'number',
		  'sortable'  => false,
      ));
      
      $this->addColumn('name', array(
          'header'    => Mage::helper('affiliateplus')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
          'sortable'  => false,
      ));
      
      $this->addColumn('email', array(
			'header'    => Mage::helper('affiliateplus')->__('Email'),
			'index'     => 'email',
			'sortable'  => false,
      ));
      
      $this->addColumn('amount', array(
			'header'    => Mage::helper('affiliateplus')->__('Sales'),
			'align'     =>'right',
			'index'     => 'amount',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
			'sortable'  => false,
      ));
      
      $this->addColumn('num_order_placed', array(
          'header'    => Mage::helper('affiliateplus')->__('Number of Orders'),
          'align'     => 'right',
          'width'     => '80px',
          'index'     => 'num_order_placed',
		  'sortable'  => false,
      ));
      
      $this->addColumn('balance', array(
			'header'    => Mage::helper('affiliateplus')->__('Balance'),
			'align'     =>'right',
			'index'     => 'balance',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
			'sortable'  => false,
      ));
      
      $this->addColumn('total_commission_received', array(
			'header'    => Mage::helper('affiliateplus')->__('Commission'),
			'align'     =>'right',
			'index'     => 'total_commission_received',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
			'sortable'  => false,
      ));
      
      $this->addColumn('status', array(
          'header'    => Mage::helper('affiliateplus')->__('Status'),
          'align'     => 'left',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
		  'sortable'  => false,
      ));
    }
    
    public function getRowUrl($row){
    	return $this->getUrl('affiliateplusadmin/adminhtml_account/edit',array(
    		'id' => $row->getId(),
    		'store' => $this->getRequest()->getParam('store')
    	));
    }
}