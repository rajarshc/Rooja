<?php

class Aurigait_Banner_Block_Adminhtml_Bannermultiple_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('bannerGrid');
      $this->setDefaultSort('banner_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('banner/banner')->getCollection()->addFieldToFilter("banner_type",3);
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

	 $this->addColumn('filethumbgrid', array(
          'header'    => Mage::helper('banner')->__('Thumbnail'),
          'align'     =>'center',
          'index'     => 'filethumbgrid',
		  'type'      => 'text',
		  'width'     => '150px',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('banner')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
	  
	  	  $this->addColumn('link', array(
          'header'    => Mage::helper('banner')->__('Link'),
          'align'     =>'left',
          'width'     => '350px',
          'index'     => 'link',
      ));
	  
      $this->addColumn('gender', array(
          'header'    => Mage::helper('banner')->__('Gender'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'gender',
          'type'      => 'options',
          'options'   => array(
              2 => 'Men',
              3 => 'Women',
              1 => 'both'
          ),
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

        $statuses = Mage::getSingleton('banner/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('banner')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('banner')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
