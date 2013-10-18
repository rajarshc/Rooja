<?php

class Magestore_Affiliateplus_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('bannerGrid');
		$this->setDefaultSort('banner_id');
		$this->setDefaultDir('DESC');
		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('affiliateplus/banner')->getCollection();
		$storeId = $this->getRequest()->getParam('store');
		$collection->setStoreId($storeId);
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

  protected function _prepareColumns()
  {
      $this->addColumn('banner_id', array(
          'header'    => Mage::helper('affiliateplus')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'banner_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('affiliateplus')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  
      $this->addColumn('link', array(
			'header'    => Mage::helper('affiliateplus')->__('Link'),
			'index'     => 'link',
      ));
	  
	  //add event to add more column 
	  Mage::dispatchEvent('affiliateplus_adminhtml_add_column_banner_grid', array('grid' => $this));
	  
		
	  $this->addColumn('type_id', array(
			'header'    => Mage::helper('affiliateplus')->__('Type'),
			'width'     => '100px',
			'index'   => 'type_id',
			'type'      => 'options',
			'options'   => array(
              1 => 'Image',
              2 => 'Flash',
			  3 => 'Text'
          ),
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
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('affiliateplus')->__('Action'),
                'width'     => '100',
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
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('affiliateplus')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('affiliateplus')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
		$storeId = $this->getRequest()->getParam('store');
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('banner');

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