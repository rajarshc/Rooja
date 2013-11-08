<?php

class Aurigait_Banner_Block_Adminhtml_Bannersingle_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('footerbannerGrid');
      $this->setDefaultSort('banner_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(false);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('banner/banner')->getCollection()->addFieldToFilter("banner_type",1);
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('banner_id', array(
          'header'    => Mage::helper('banner')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'banner_id',
      ));

	$this->addColumn('thumbgrid', array(
          'header'    => Mage::helper('banner')->__('Banner Thumbnail'),
          'align'     =>'left',
          'width'     => '350px',
          'index'     => 'filethumbgrid',
          'type' =>'text'  
      ));
        
      
	  
     $this->addColumn('link', array(
          'header'    => Mage::helper('banner')->__('Link'),
          'align'     =>'left',
          'width'     => '350px',
          'index'     => 'link',
      ));
     $this->addColumn('position', array(
          'header'    => Mage::helper('banner')->__('Position'),
          'align'     =>'left',
          'width'     => '350px',
          'index'     => 'position',
      ));

	  
      
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('banner')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('banner')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('banner')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('banner')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('banner');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('banner')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('banner')->__('Are you sure?')
        ));

       
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
