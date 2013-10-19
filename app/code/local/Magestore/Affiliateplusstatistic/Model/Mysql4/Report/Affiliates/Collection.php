<?php

class Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Affiliates_Collection extends Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
{
	protected $_periodFormat;
	protected $_selectedColumns	= array();
	
	public function __construct(){
		parent::_construct();
		$this->setModel('adminhtml/report_item');
		$this->_resource = Mage::getResourceModel('sales/report')->init('affiliateplus/transaction','transaction_id');
		$this->setConnection($this->getResource()->getReadConnection());
		
		$this->_applyFilters = true;
		$this->_period_column = 'created_time';
		$this->_status_column = 'status';
        $this->_store_column = 'store_id';
	}
	
	/**
	 * Retrieve columns for select
	 *
	 * @return array
	 */
	protected function _getSelectedColumns(){
		//$adapter = $this->getConnection();

		if (!$this->_selectedColumns) {
			if ($this->isTotals())
				$this->_selectedColumns = $this->getAggregatedColumns();
			else {
				$this->_periodFormat = "DATE_FORMAT(created_time, '%Y-%m-%d')";
				if ('year' == $this->_period)
					$this->_periodFormat = "DATE_FORMAT(created_time, '%Y')";
				elseif ('month' == $this->_period)
					$this->_periodFormat = "DATE_FORMAT(created_time, '%Y-%m')";
				
				$this->_selectedColumns = array(
					'created_time'		=> $this->_periodFormat,
                    'account_name'      => 'account_name',
                    'account_email'     => 'account_email',
                    'total_amount'      => 'SUM(total_amount)',
                    'order_id'          => 'COUNT(DISTINCT order_id)',
                    'commission'        => 'SUM(commission)',
				);
			}
		}
		return $this->_selectedColumns;
	}
	
	/**
     * Add selected data
     *
     * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Sales_Collection
     */
	protected function _initSelect(){
		$this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());
		if (!$this->isTotals())
			$this->getSelect()
				->group(array('account_id',$this->_periodFormat))
				->order($this->_periodFormat.' ASC')
                ->order('SUM(total_amount) DESC')
                ->order('COUNT(DISTINCT order_id) ASC');
		return $this;
	}
}
