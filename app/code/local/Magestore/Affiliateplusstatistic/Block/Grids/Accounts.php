<?php
class Magestore_Affiliateplusstatistic_Block_Grids_Accounts extends Mage_Adminhtml_Block_Dashboard_Grid
{
	public function __construct(){
        parent::__construct();
        $this->setId('newAccounts');
        $this->setDefaultSort('created_time');
        $this->setDefaultDir('DESC');
    }
    
    protected function _prepareCollection(){
    	$collection = Mage::getResourceModel('affiliateplus/account_collection');
    	
    	if ($storeId = $this->getRequest()->getParam('store'))
			$collection->setStoreId($storeId);
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
      $this->addColumn('account_id', array(
          'header'    => Mage::helper('affiliateplus')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
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
			'filter_index'	=> 'main_table.email',
			'sortable'  => false,
      ));
      
      $this->addColumn('created_time', array(
			'header'    => Mage::helper('affiliateplus')->__('Registered On'),
			'index'     => 'created_time',
			'type'		=> 'datetime',
			'sortable'  => false,
      ));
      
      $this->addColumn('status', array(
          'header'    => Mage::helper('affiliateplus')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
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