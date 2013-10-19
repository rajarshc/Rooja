<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct(){
      parent::__construct();
      $this->setId('affiliateplusprogramGrid');
      $this->setDefaultSort('program_id');
      $this->setDefaultDir('DESC');
      $this->setUseAjax(true);
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection(){
      $collection = Mage::getModel('affiliateplusprogram/program')->getCollection();
      if ($storeId = $this->getStore()->getId())
      	$collection->setStoreId($storeId);
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns(){
      $this->addColumn('program_id', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'program_id',
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Program Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));
	  
	  $this->addColumn('commission', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Commission'),
          'align'     =>'left',
          'index'     => 'commission',
		  'renderer'  => 'affiliateplusprogram/adminhtml_program_renderer_commission',
      ));
	  
	  $this->addColumn('discount', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Discount'),
          'align'     =>'left',
          'index'     => 'discount',
		  'renderer'  => 'affiliateplusprogram/adminhtml_program_renderer_discount',
      ));

      $this->addColumn('num_account', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Number of Accounts'),
          'align'     =>'left',
          'index'     => 'num_account',
          'width'     => '70px',
      ));

      $this->addColumn('total_sales_amount', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Total Amount'),
          'align'     => 'left',
          'type'      => 'price',
          'index'     => 'total_sales_amount',
          'currency_code' => $this->getStore()->getBaseCurrencyCode(),
      ));
      
      $this->addColumn('created_date', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Created Date'),
          'align'     => 'right',
          'type'      => 'date',
          'index'     => 'created_date',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => Mage::getSingleton('affiliateplusprogram/status')->getOptionArray(),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('affiliateplusprogram')->__('Action'),
                'width'     => '70',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('affiliateplusprogram')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('affiliateplusprogram')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('affiliateplusprogram')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('program_id');
        $this->getMassactionBlock()->setFormFieldName('affiliateplusprogram');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('affiliateplusprogram')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('affiliateplusprogram')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('affiliateplusprogram/status')->getOptions();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('affiliateplusprogram')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('affiliateplusprogram')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
	
	public function getGridUrl(){
        return $this->getUrl('*/*/grid',array('store'=>$this->getRequest()->getParam('store')));
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