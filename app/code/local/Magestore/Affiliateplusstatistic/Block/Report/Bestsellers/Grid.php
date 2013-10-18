<?php
class Magestore_Affiliateplusstatistic_Block_Report_Bestsellers_Grid extends Magestore_Affiliateplusstatistic_Block_Report_Grid_Abstract
{
	protected $_columnGroupBy = 'created_time';
	protected $_period_column = 'created_time';
	
	public function __construct(){
		parent::__construct();
		$this->_resourceCollectionName = 'affiliateplusstatistic/report_bestsellers_collection';
		$this->setCountTotals(true);
	}
	
	protected function _prepareColumns()
    {
        $this->addColumn('created_time',array(
            'header'	=> Mage::helper('affiliateplusstatistic')->__('Period'),
            'index'		=> 'created_time',
            'width'		=> 100,
            'sortable'	=> false,
            'period_type'	=> $this->getPeriodType(),
            'renderer'	=> 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'	=> Mage::helper('adminhtml')->__('Total'),
            'html_decorators'	=> array('nobr'),
        ));

        $this->addColumn('product_name',array(
            'header'	=> Mage::helper('affiliateplusstatistic')->__('Product Name'),
            'index'		=> 'product_name',
            'type'		=> 'string',
            'sortable'	=> false
        ));
        
        $this->addColumn('product_type', array(
           'header'    => Mage::helper('affiliateplus')->__('Type'),
           'align'     => 'left',
           'index'     => 'product_type',
           'type'      => 'options',
           'options'   =>  Mage::getSingleton('catalog/product_type')->getOptionArray(),
		   'sortable'  => false,
       ));
		
		$this->addColumn('sku',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('SKU'),
			'index'		=> 'sku',
			'type'		=> 'string',
			'sortable'	=> false
		));
		
		$currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		
		$this->addColumn('base_price',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Price'),
			'index'		=> 'base_price',
			'type'		=> 'currency',
			'currency_code'	=> $currencyCode,
			'sortable'	=> false
		));
		
		$this->addColumn('qty_ordered',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Quantity Ordered'),
			'index'		=> 'qty_ordered',
			'type'		=> 'number',
			'total'		=> 'sum',
			'sortable'	=> false
		));
        
		$this->addColumn('price',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Total Sales Amount'),
			'index'		=> 'price',
			'type'		=> 'currency',
			'currency_code'	=> $currencyCode,
			'total'		=> 'sum',
           'align'      => 'right',
			'sortable'	=> false
		));
        
		$this->addExportType('*/*/exportBestsellersCsv', Mage::helper('adminhtml')->__('CSV'));
		$this->addExportType('*/*/exportBestsellersExcel', Mage::helper('adminhtml')->__('Excel XML'));
		
		return parent::_prepareColumns();
	}
}
