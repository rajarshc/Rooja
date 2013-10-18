<?php

class Magestore_Affiliateplus_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('accountGrid');
      $this->setDefaultSort('account_id');
      $this->setDefaultDir('DESC');
	  $this->setUseAjax(true);
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
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('affiliateplus')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
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
		
		$this->addExportType('*/*/exportCsv', Mage::helper('affiliateplus')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('affiliateplus')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
		$storeId = $this->getRequest()->getParam('store');
        $this->setMassactionIdField('account_id');
        $this->getMassactionBlock()->setFormFieldName('account');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('affiliateplus')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('affiliateplus')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('affiliateplus/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('affiliateplus')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true, 'store'=>$storeId)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('affiliateplus')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'store' => $this->getRequest()->getParam('store')));
	}
  
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}

}