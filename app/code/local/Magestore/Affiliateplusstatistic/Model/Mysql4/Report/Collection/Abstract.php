<?php

class Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	/**
	 * from date
	 *
	 * @var string
	 */
	protected $_from = null;
	
	/**
	 * to date
	 *
	 * @var string
	 */
	protected $_to = null;
	
	/**
	 * Transaction status
	 *
	 * @var string
	 */
	protected $_orderStatus = null;
	
	/**
	 * Period
	 *
	 * @var string
	 */
	protected $_period = null;
	
	/**
	 * Store ids
	 *
	 * @var int|array
	 */
	protected $_storesIds = 0;
	
	/**
	 * Does filter should be applied
	 *
	 * @var bool
	 */
	protected $_applyFilters = true;
	
	/**
	 * Is totals
	 *
	 * @var bool
	 */
	protected $_isTotals = false;
	
	/**
	 * Is subtotals
	 *
	 * @var bool
	 */
	protected $_isSubTotals = false;
	
	/**
	 * Aggreted Columns
	 *
	 * @var array
	 */
	protected $_aggregatedColumns = array();
	
	/**
	 * Period column
	 *
	 * @var string
	 */
	protected $_period_column = null;
	
	/**
	 * Status column
	 *
	 * @var string
	 */
	protected $_status_column = null;
    
    /**
     * Store column
     * 
     * @var string 
     */
    protected $_store_column = 'store_id';
	
	/**
	 * set status column for collection
	 *
	 * @param string $column
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function setStatusColumn($column){
		$this->_status_column = $column;
		return $this;
	}
	
	/**
	 * set period column for collection
	 *
	 * @param string $column
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function setPeriodColumn($column){
		$this->_period_column = $column;
		return $this;
	}
	
	/**
	 * Set array of columns that should be aggregated
	 *
	 * @param array $columns
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function setAggregatedColumns(array $columns){
		$this->_aggregatedColumns = $columns;
		return $this;
	}
	
	/**
	 * Retrieve array of columns that should be aggregated
	 *
	 * @return array
	 */
	public function getAggregatedColumns(){
		return $this->_aggregatedColumns;
	}
	
	/**
	 * Set date range
	 *
	 * @param mixed $from
	 * @param mixed $to
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function setDateRange($from = null, $to = null){
		$this->_from = $from;
		$this->_to   = $to;
		return $this;
	}
	
	/**
	 * Set period
	 *
	 * @param string $period
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function setPeriod($period){
		$this->_period = $period;
		return $this;
	}
	
	/**
	 * Apply date range filter
	 *
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	protected function _applyDateRangeFilter(){
		$periodColumn = $this->_period_column;
		if (is_null($periodColumn)) return $this;
		if ($this->_from !== null)
			$this->getSelect()->where("DATE($periodColumn) >= ?", $this->_from);
		if ($this->_to !== null)
			$this->getSelect()->where("DATE($periodColumn) <= ?", $this->_to);
		
		return $this;
	}
	
	/**
	 * Set store ids
	 *
	 * @param mixed $storeIds (null, int|string, array, array may contain null)
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function addStoreFilter($storeIds){
		$this->_storesIds = $storeIds;
		return $this;
	}
	
	/**
	 * Apply stores filter to select object
	 *
	 * @param Zend_Db_Select $select
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	protected function _applyStoresFilterToSelect(Zend_Db_Select $select){
		$nullCheck = false;
		$storeIds  = $this->_storesIds;
		
		if (!is_array($storeIds))
			$storeIds = array($storeIds);
		
		$storeIds = array_unique($storeIds);

		if ($index = array_search(null, $storeIds)) {
			unset($storeIds[$index]);
			$nullCheck = true;
		}
		
		$storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];
		if ($nullCheck)
			$select->where("{$this->_store_column} IN(?) OR {$this->_store_column} IS NULL", $storeIds);
		else
			$select->where("{$this->_store_column} IN(?)", $storeIds);

		return $this;
	}
	
	/**
	 * Apply stores filter
	 *
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	protected function _applyStoresFilter(){
		return $this->_applyStoresFilterToSelect($this->getSelect());
	}
	
	/**
	 * Set status filter
	 *
	 * @param string $orderStatus
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function addOrderStatusFilter($orderStatus){
		$this->_orderStatus = $orderStatus;
		return $this;
	}
	
	/**
	 * Apply order status filter
	 *
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	protected function _applyOrderStatusFilter(){
		$statusColumn = $this->_status_column;
		if (is_null($statusColumn)) return $this;
		if (is_null($this->_orderStatus))
			return $this;
		
		$orderStatus = $this->_orderStatus;
		if (!is_array($orderStatus))
			$orderStatus = array($orderStatus);
		
		$this->getSelect()->where("$statusColumn IN(?)", $orderStatus);
		return $this;
	}
	
	/**
	 * Set apply filters flag
	 *
	 * @param boolean $flag
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function setApplyFilters($flag){
		$this->_applyFilters = $flag;
		return $this;
	}
	
	/**
	 * Getter/Setter for isTotals
	 *
	 * @param null|boolean $flag
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function isTotals($flag = null){
		if (is_null($flag))
			return $this->_isTotals;
		
		$this->_isTotals = $flag;
		return $this;
	}
	
	/**
	 * Getter/Setter for isSubTotals
	 *
	 * @param null|boolean $flag
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function isSubTotals($flag = null){
		if (is_null($flag))
			return $this->_isSubTotals;
		
		$this->_isSubTotals = $flag;
		return $this;
	}
	
	/**
	 * Load data
	 * Redeclare parent load method just for adding method _beforeLoad
	 *
	 * @param bool $printQuery
	 * @param bool $logQuery
	 * @return Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
	 */
	public function load($printQuery = false, $logQuery = false){
		if ($this->isLoaded())
			return $this;
		
		$this->_initSelect();
		if ($this->_applyFilters) {
			$this->_applyDateRangeFilter();
			$this->_applyStoresFilter();
			$this->_applyOrderStatusFilter();
		}
		return parent::load($printQuery, $logQuery);
	}
}