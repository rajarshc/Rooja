<?php

class Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Bestsellers_Collection extends Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
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
        $this->_store_column = 'm.store_id';
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
					'product_name'			=> 'i.name',
					'sku'                   => 'i.sku',
					'base_price'			=> 'i.base_price',
					'qty_ordered'           => 'SUM(i.qty_ordered)',
                    'product_type'          => 'i.product_type',
                    'price'                 => 'SUM(i.qty_ordered * i.base_price)'
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
		$this->getSelect()->from(array('m' => $this->getResource()->getMainTable()), $this->_getSelectedColumns());
        $itemTable = $this->getResource()->getTable('sales/order_item');
        $this->getSelect()->join(array('i' => $itemTable),
            'm.order_id = i.order_id AND FIND_IN_SET(i.product_id, m.order_item_ids)',
            array()
        )->where('type = 3');
		if (!$this->isTotals())
			$this->getSelect()
				->group(array('product_id',$this->_periodFormat))
				->order($this->_periodFormat.' ASC')
                ->order('SUM(i.qty_ordered) DESC')
                ->order('base_price DESC');
		return $this;
	}
}