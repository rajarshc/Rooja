<?php
class Magestore_Affiliateplusstatistic_Block_Report_Affiliates_Grid extends Magestore_Affiliateplusstatistic_Block_Report_Grid_Abstract
{
	protected $_columnGroupBy = 'created_time';
	protected $_period_column = 'created_time';
	
	public function __construct(){
		parent::__construct();
		$this->_resourceCollectionName = 'affiliateplusstatistic/report_affiliates_collection';
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

        $this->addColumn('account_name',array(
            'header'	=> Mage::helper('affiliateplusstatistic')->__('Account Name'),
            'index'		=> 'account_name',
            'type'		=> 'string',
            'sortable'	=> false
        ));

        $this->addColumn('account_email',array(
            'header'	=> Mage::helper('affiliateplusstatistic')->__('Email'),
            'index'		=> 'account_email',
            'type'		=> 'string',
            'sortable'	=> false
        ));
		
		$currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        
		$this->addColumn('order_id',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Number of Orders'),
			'index'		=> 'order_id',
			'type'		=> 'number',
			'total'		=> 'count',
			'sortable'	=> false
		));
		
		$this->addColumn('total_amount',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Sales Amount'),
			'index'		=> 'total_amount',
			'type'		=> 'currency',
			'currency_code'	=> $currencyCode,
			'total'		=> 'sum',
            'align'     => 'right',
			'sortable'	=> false
		));
        
		$this->addColumn('commission',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Commission'),
			'index'		=> 'commission',
			'type'		=> 'currency',
			'currency_code'	=> $currencyCode,
			'total'		=> 'sum',
            'align'      => 'right',
			'sortable'	=> false
		));
        
		$this->addExportType('*/*/exportAffiliatesCsv', Mage::helper('adminhtml')->__('CSV'));
		$this->addExportType('*/*/exportAffiliatesExcel', Mage::helper('adminhtml')->__('Excel XML'));
		
		return parent::_prepareColumns();
	}
}
