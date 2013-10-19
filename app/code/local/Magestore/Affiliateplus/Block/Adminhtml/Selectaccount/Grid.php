<?php

class Magestore_Affiliateplus_Block_Adminhtml_Selectaccount_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('accountGrid');
      $this->setDefaultSort('account_id');
      $this->setDefaultDir('DESC');
	//  $this->setUseAjax(true);
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
  	  
      $collection = Mage::getModel('affiliateplus/account')->getCollection();
	  //add event to add more column 
	  Mage::dispatchEvent('affiliateplus_adminhtml_join_account_other_table', array('collection' => $collection));
	  
	  $storeId = $this->getRequest()->getParam('store');
	  $collection->setStoreId($storeId);
	
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
  	  $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
      $this->addColumn('account_id', array(
          'header'    => Mage::helper('affiliateplus')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'account_id',
		  'type'	  => 'number',
		  'filter_index'	=> 'main_table.account_id'
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('affiliateplus')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
		  'filter_index'	=> 'main_table.name'
      ));

	  
      $this->addColumn('email', array(
			'header'    => Mage::helper('affiliateplus')->__('Email'),
			'index'     => 'email',
			'filter_index'	=> 'main_table.email'
      ));
	  
	  $this->addColumn('balance', array(
			'header'    => Mage::helper('affiliateplus')->__('Balance'),
			'width'     => '100px',
			'align'     =>'right',
			'index'     => 'balance',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
			'filter_index'	=> 'main_table.balance'
      ));
	  
	  $this->addColumn('total_commission_received', array(
			'header'    => Mage::helper('affiliateplus')->__('Total Received'),
			'width'     => '100px',
			'align'     =>'right',
			'index'     => 'total_commission_received',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
			'filter_index'	=> 'main_table.total_commission_received'
      ));
	  
	  $this->addColumn('total_paid', array(
			'header'    => Mage::helper('affiliateplus')->__('Total Paid'),
			'width'     => '100px',
			'align'     =>'right',
			'index'     => 'total_paid',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
			'filter_index'	=> 'main_table.total_paid'
      ));
		
	  /* $this->addColumn('store_id', array(
			'header'    => Mage::helper('affiliate')->__('Store view'),
			'align'     =>'left',
			'index'     =>'store_id',
			'type'      =>'store',
			'width'     => '150px',
			'store_all' =>true,
			'store_view'=>true,
	  )); */
	  
	  //add event to add more column 
	  Mage::dispatchEvent('affiliateplus_adminhtml_add_column_account_grid', array('grid' => $this));
	  
      $this->addColumn('status', array(
          'header'    => Mage::helper('affiliateplus')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
		  'filter_index'	=> 'main_table.status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
		  
      ));
	  
	  $this->addColumn('approved', array(
          'header'    => Mage::helper('affiliateplus')->__('Approved'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'approved',
		  'filter_index'	=> 'main_table.approved',
          'type'      => 'options',
          'options'   => array(
              1 => 'Yes',
              2 => 'No',
          ),
      ));
	  
      return parent::_prepareColumns();
  }


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/new', array('account_id' => $row->getId(), 'store' => $this->getRequest()->getParam('store')));
	}
  
	public function getGridUrl()
	{
		return $this->getUrl('*/*/selectaccountgrid', array('_current'=>true));
	}
}
