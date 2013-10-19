<?php

class Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Sales_Collection extends Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
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
				//$this->_periodFormat = $adapter->getDateFormatSql('created_time', '%Y-%m-%d');
				$this->_periodFormat = "DATE_FORMAT(created_time, '%Y-%m-%d')";
				if ('year' == $this->_period)
					//$this->_periodFormat = $adapter->getDateFormatSql('created_time', '%Y');
					$this->_periodFormat = "DATE_FORMAT(created_time, '%Y')";
				elseif ('month' == $this->_period)
					//$this->_periodFormat = $adapter->getDateFormatSql('created_time', '%Y-%m');
					$this->_periodFormat = "DATE_FORMAT(created_time, '%Y-%m')";
				
				$this->_selectedColumns = array(
					'created_time'			=>  $this->_periodFormat,
					'account_email'			=> 'account_email',
					'order_item_names'		=> 'order_item_names',
					'total_amount'			=> 'SUM(total_amount)',
					'commission'			=> 'SUM(commission)',
					'transaction_id'		=> 'COUNT(transaction_id)',
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
		$this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns())
            ->where('type = 3');
		if (!$this->isTotals())
			$this->getSelect()
				->group(array('order_item_ids',$this->_periodFormat,'account_email'))
				->order($this->_periodFormat.' ASC');
		return $this;
	}
}