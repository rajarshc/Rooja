<?php
class Magestore_Affiliateplusstatistic_Block_Report_Sales_Grid extends Magestore_Affiliateplusstatistic_Block_Report_Grid_Abstract
{
	protected $_columnGroupBy = 'created_time';
	protected $_period_column = 'created_time';
	
	public function __construct(){
        if ($this->_isGroupByAccount()) {
            $this->_columnGroupBy = 'account_email';
        }
		parent::__construct();
		$this->_resourceCollectionName = 'affiliateplusstatistic/report_sales_collection';
		$this->setCountTotals(true);
	}
    
    protected function _isGroupByAccount() {
        if (!$this->hasData('group_by_account')) {
            $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
            if (isset($requestData['filter_group_by']) && $requestData['filter_group_by'] == 2) {
                $this->setData('group_by_account',true);
            } else {
                $this->setData('group_by_account',false);
            }
        }
        return $this->getData('group_by_account');
    }
	
	protected function _prepareColumns(){
        
        if ($this->_isGroupByAccount()) {
            $this->addColumn('account_email',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Account Email'),
                'index'		=> 'account_email',
                'type'		=> 'string',
                'sortable'	=> false,
                'totals_label'	=> Mage::helper('adminhtml')->__('Total'),
                'html_decorators'	=> array('nobr'),
            ));
            
            $this->addColumn('created_time',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Period'),
                'index'		=> 'created_time',
                'width'		=> 100,
                'sortable'	=> false,
                'period_type'	=> $this->getPeriodType(),
                'renderer'	=> 'adminhtml/report_sales_grid_column_renderer_date',
            ));
        } else {
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

            $this->addColumn('account_email',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Account Email'),
                'index'		=> 'account_email',
                'type'		=> 'string',
                'sortable'	=> false
            ));
        }
		
		$this->addColumn('order_item_names',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Product(s)'),
			'index'		=> 'order_item_names',
			'type'		=> 'string',
			'sortable'	=> false
		));
		
		$currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		
		$this->addColumn('total_amount',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Total Amount'),
			'index'		=> 'total_amount',
			'type'		=> 'currency',
			'currency_code'	=> $currencyCode,
			'total'		=> 'sum',
			'sortable'	=> false
		));
		
		$this->addColumn('commission',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Commissions'),
			'index'		=> 'commission',
			'type'		=> 'currency',
			'currency_code'	=> $currencyCode,
			'total'		=> 'sum',
			'sortable'	=> false
		));
		
/* 		$this->addColumn('transaction_id',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Transactions'),
			'index'		=> 'transaction_id',
			'type'		=> 'number',
			'total'		=> 'count',
			'sortable'	=> false
		)); */
		
		$this->addExportType('*/*/exportSalesCsv', Mage::helper('adminhtml')->__('CSV'));
		$this->addExportType('*/*/exportSalesExcel', Mage::helper('adminhtml')->__('Excel XML'));
		
		return parent::_prepareColumns();
	}
}