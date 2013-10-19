<?php

class Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Impressions_Collection extends Magestore_Affiliateplusstatistic_Model_Mysql4_Report_Collection_Abstract
{
	protected $_periodFormat;
	protected $_selectedColumns	= array();
	
	public function __construct(){
		parent::_construct();
		$this->setModel('adminhtml/report_item');
		$this->_resource = Mage::getResourceModel('sales/report')->init('affiliateplus/action','action_id');
		$this->setConnection($this->getResource()->getReadConnection());
		
		$this->_applyFilters = true;
		$this->_period_column = 'created_date';
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
				//$this->_periodFormat = $adapter->getDateFormatSql('visit_at', '%Y-%m-%d');
				$this->_periodFormat = "DATE_FORMAT(created_date, '%Y-%m-%d')";
				if ('year' == $this->_period)
					//$this->_periodFormat = $adapter->getDateFormatSql('visit_at', '%Y');
					$this->_periodFormat = "DATE_FORMAT(created_date, '%Y')";
				elseif ('month' == $this->_period)
					//$this->_periodFormat = $adapter->getDateFormatSql('visit_at', '%Y-%m');
					$this->_periodFormat = "DATE_FORMAT(created_date, '%Y-%m')";
				
				$this->_selectedColumns = array(
					'visit_at'			=> $this->_periodFormat,
                    'account_email'     => 'account_email',
					'referer'			=> 'referer',
                    'banner_id'         => 'banner_id',
                    'banner_title'      => 'banner_title',
					'url_path'			=> 'landing_page',
					'totals'            => 'SUM(totals)',
					'is_unique'         => 'SUM(is_unique)',
				);
			}
		}
		return $this->_selectedColumns;
	}
	
	/**
     * Add selected data
     *
     * @return Mage_Sales_Model_Resource_Report_Order_Collection
     */
	protected function _initSelect(){
		$this->getSelect()
            ->from($this->getResource()->getMainTable(), $this->_getSelectedColumns())
            ->where('type = 1');
		if (!$this->isTotals())
			$this->getSelect()
				->group(array('referer','landing_page',$this->_periodFormat,'account_email','banner_id'))
				->order($this->_periodFormat.' ASC');
		return $this;
	}
}