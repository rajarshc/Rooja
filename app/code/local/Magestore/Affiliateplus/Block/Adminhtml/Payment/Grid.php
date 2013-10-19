<?php

	class Magestore_Affiliateplus_Block_Adminhtml_Payment_Grid extends Mage_Adminhtml_Block_Widget_Grid
	{
		public function __construct()
		{
			parent::__construct();
			$this->setId('paymentGrid');
			$this->setDefaultSort('payment_id');
			$this->setDefaultDir('DESC');
			$this->setUseAjax(true);
			$this->setSaveParametersInSession(true);
		}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('affiliateplus/payment')->getCollection();
		/*$paypalTable = Mage::getModel('core/resource')->getTableName('affiliateplus_payment_paypal'); 
		
		$collection->getSelect()
			->joinLeft($paypalTable, "$paypalTable.payment_id = main_table.payment_id", 
				array('paypal_email'=>'email', 'transaction_id' => 'transaction_id', 'paypal_description' => 'description'));
		*/
		//event to join other table
		Mage::dispatchEvent('affiliateplus_adminhtml_join_payment_other_table', array('collection' => $collection));
		
		$storeId = $this->getRequest()->getParam('store');
		if($storeId)
			$collection->addFieldToFilter('store_ids', array('finset' => $storeId));
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
		
		$this->addColumn('payment_id', array(
			'header'    => Mage::helper('affiliateplus')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'payment_id',
			'type'		=> 'number'
		));
		
		$this->addColumn('account_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Affiliate Account'),
			'index'     => 'account_email',
			'renderer'  => 'affiliateplus/adminhtml_transaction_renderer_account',
		));	
		
		// $this->addColumn('account_email', array(
			// 'header'    => Mage::helper('affiliateplus')->__('Account Email'),
			// 'index'     => 'account_email',
			// 'renderer'  => 'affiliateplus/adminhtml_payment_renderer_account',
		// ));	
	
		$this->addColumn('amount', array(
			'header'    => Mage::helper('affiliateplus')->__('Amount'),
			'width'     => '80px',
			'align'     =>'right',
			'index'     => 'amount',
			'type'  	=> 'price',
			'currency_code' => $currencyCode,
		));
        
        $this->addColumn('tax_amount', array(
			'header'    => Mage::helper('affiliateplus')->__('Tax'),
			'width'     => '80px',
			'align'     =>'right',
			'index'     => 'tax_amount',
			'type'  	=> 'price',
			'currency_code' => $currencyCode,
		));
	
		$this->addColumn('fee', array(
			'header'    => Mage::helper('affiliateplus')->__('Fee'),
			'width'     => '80px',
			'align'     =>'right',
			'index'     => 'fee',
			'type'  	=> 'price',
			'currency_code' => $currencyCode,
		));
		
		$this->addColumn('payment_method', array(
			'header'    => Mage::helper('affiliateplus')->__('Withdrawal Method'),
			'index'     => 'payment_method',
			'renderer'  => 'affiliateplus/adminhtml_payment_renderer_info',
            'type'      => 'options',
            'options'   => Mage::helper('affiliateplus/payment')->getAllPaymentOptionArray()
			// 'filter'    => false,
			// 'sortable'  => false,
		));
		
		/*$this->addColumn('paypal_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Paypal Email'),
			'width'     => '150px',
			'align'     =>'right',
			'index'     => 'paypal_email',
		));

		
		$this->addColumn('transaction_id', array(
			'header'    => Mage::helper('affiliateplus')->__('Transaction ID'),
			'width'     => '120px',
			'index'		=> 'transaction_id',
		));*/
	
		//add event to add more column
		//$this->removeColumn('transaction_id');
	  	Mage::dispatchEvent('affiliateplus_adminhtml_change_column_payment_grid', array('grid' => $this));
		
		
		$this->addColumn('request_time', array(
			'header'    => Mage::helper('affiliateplus')->__('Time'),
			'width'     => '180px',
			'align'     =>'right',
			'index'     => 'request_time',
			'type'		=> 'date'
		));
	
		$this->addColumn('status', array(
			'header'    => Mage::helper('affiliateplus')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				1 =>  Mage::helper('affiliateplus')->__('Pending'),
				2 =>  Mage::helper('affiliateplus')->__('Processing'),
				3 =>  Mage::helper('affiliateplus')->__('Completed'),
                                4 =>  Mage::helper('affiliateplus')->__('Canceled')
			),
		));
	
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('affiliateplus')->__('Action'),
				'width'     => '80px',
				'type'      => 'action',
				'getter'    => 'getId',
//                                'align'     =>'center',
//                                'renderer'  => 'affiliateplus/adminhtml_payment_renderer_actions',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('affiliateplus')->__('Edit'),
						'url'       => array('base'=> '*/*/edit'),
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
        Mage::dispatchEvent('affiliateplus_adminhtml_payment_massaction', array('grid' => $this));
        return $this;
    }
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
	
	public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}