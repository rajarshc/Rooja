<?php
class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tab_Payment
 extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('paymentgrid');
        $this->setDefaultSort('payment_grid_payment_id');
        $this->setDefaultDir('DESC');
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
		$collection = Mage::getModel('affiliateplus/payment')->getCollection()
						->addFieldToFilter('account_id', $accountId);
		
		/*$paypalTable = Mage::getModel('core/resource')->getTableName('affiliateplus_payment_paypal'); 
		
		$collection->getSelect()
			->join($paypalTable, "$paypalTable.payment_id = main_table.payment_id", 
				array('paypal_email'=>'email', 'transaction_id' => 'transaction_id', 'paypal_description' => 'description'));*/
		
		//event to join other table
		Mage::dispatchEvent('affiliateplus_adminhtml_join_payment_other_table', array('collection' => $collection));
		
		if ($storeId = $this->getRequest()->getParam('store'))
			$collection->addFieldToFilter('store_ids', array('finset' => $storeId));
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
	    $store = $this->_getStore();
		$prefix = 'payment_grid_';
		$this->addColumn($prefix.'payment_id', array(
			'header'	=> Mage::helper('catalog')->__('ID'),
			'index'		=> 'payment_id',
			'type'      => 'number',
		));
		
		/*$this->addColumn($prefix.'paypal_email', array(
			'header'	=> Mage::helper('catalog')->__('Payment Email'),
			'index'		=> 'paypal_email',
		));	*/
		
		$this->addColumn('account_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Account Email'),
			'index'     => 'account_email',
			'renderer'  => 'affiliateplus/adminhtml_payment_renderer_account',
		));	
		
		$this->addColumn($prefix.'amount', array(
			'header'	=> Mage::helper('catalog')->__('Amount'),
			'index'		=> 'amount',
		    'type'  => 'price',
		    'currency_code' => $store->getBaseCurrency()->getCode(),				
		));
		
	    $this->addColumn($prefix.'fee', array(
			'header'	=> Mage::helper('catalog')->__('Fee'),
			'index'		=> 'fee',
		    'type'  => 'price',
		    'currency_code' => $store->getBaseCurrency()->getCode(),			
		));
		
		$this->addColumn($prefix.'request_time', array(
			'header'	=> Mage::helper('catalog')->__('Request Date'),
			'type'		=> 'date',
			'index'		=> 'request_time',
			'align'		=> 'right',
		));
		
		//add event to add more column 
	  	Mage::dispatchEvent('affiliateplus_adminhtml_add_column_account_payment_grid', array('block' => $this));
		
		 // Status
	  $this->addColumn($prefix.'status', array(
          'header'    => Mage::helper('catalog')->__('Status'),
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
		
    }

    //return url
	public function getGridUrl()
    {	
		return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/paymentGrid', array(
				'_current'=>true,
				'id'=>$this->getRequest()->getParam('id'),
				'store'	=> $this->getRequest()->getParam('store')
            ));
	}
	
	public function getRowUrl($row) {
		$id = $row->getId();
		return $this->getUrl('*/adminhtml_payment/edit', array(
			'id' => $id//,
			//'store'	=> $this->getRequest()->getParam('store')
		));
	}

	//return Magestore_Affiliate_Model_Referral
	public function getReferral()
	{
	
	}	
	
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }	
}