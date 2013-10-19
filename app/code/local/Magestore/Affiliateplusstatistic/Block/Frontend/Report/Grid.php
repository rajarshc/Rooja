<?php
class Magestore_Affiliateplusstatistic_Block_Frontend_Report_Grid extends Mage_Core_Block_Template
{
	/**
	 * Columns of Grid
	 *
	 * @var array
	 * 
	 * Example of element:
	 * $_columns['id'] = array(
	 * 	'header'	=> 'ID',
	 * 	'align'		=> 'right',
	 * 	'width'		=> '50px',
	 * 	'index'		=> 'account_id',
	 * 	'type'		=> 'date' | 'options' | 'action' | 'datetime' | 'price' | 'baseprice'
	 *	'format'	=> 'medium',
	 * 	'options'	=> array( 'value' => 'label'),
	 * 	'action'	=> array(
	 * 					'label' => 'Edit',
	 * 					'url' 	=> 'affiliateplus/index/index',
	 * 					'name'	=> 'id',
	 * 					'field'	=> 'account_id',
	 * 					),
	 * 	'render'	=> 'function_name_of_parent_block',
	 * );
	 */
	protected $_columns = array();
	
	/**
	 * Grid's Collection
	 */
	protected $_collection;
	
	public function getColumns(){
		return $this->_columns;
	}
	
	public function setCollection($collection){
		$this->_collection = $collection;
		return $this;
	}
	
	public function getCollection(){
		return $this->_collection;
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		//$this->setTemplate('affiliateplusstatistic/report/grid.phtml');
		return $this;
    }
    
    /**
     * Add new Column to Grid
     *
     * @param string $columnId
     * @param array $params
     * @return Magestore_Affiliateplus_Block_Grid
     */
    public function addColumn($columnId, $params){
    	$this->_columns[$columnId] = $params;
    	return $this;
    }
    
    /**
     * Call Render Function
     *
     * @param string $parentFunction
     * @param mixed $params
     * @return string
     */
    public function fetchRender($parentFunction, $row){
    	$parentBlock = $this->getParentBlock();
    	
    	$fetchObj = new Varien_Object(array(
    		'function'	=> $parentFunction,
    		'html'		=> false,
    	));
    	Mage::dispatchEvent("affiliateplus_grid_fetch_render_$parentFunction",array(
    		'block'	=> $parentBlock,
    		'row'	=> $row,
    		'fetch'	=> $fetchObj,
    	));
    	
    	if ($fetchObj->getHtml()) return $fetchObj->getHtml();
    	
    	return $parentBlock->$parentFunction($row);
    }
    
    public function _getHelper() {
        return Mage::helper('affiliateplus/config');
    }
    public function formatData($date) {
        $intPos = strrpos($date, "-");
        $str1 = substr($date, 0, $intPos);
        $str2 = substr($date, $intPos + 1);
        if (strlen($str2) == 4) {
            $date = $str2 . "-" . $str1;
        }
        return $date;
    }
    public function getRowspans()
    {
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
        $collection = Mage::getModel('affiliateplus/transaction')->getCollection();
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
            $collection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
        $collection->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('type', '3')
                ->setOrder('created_time', 'ASC');
        Mage::dispatchEvent('affiliateplus_prepare_sales_collection', array(
            'collection' => $collection,
        ));

        if ($fromDate = $this->getRequest()->getParam('date_from'))
            $collection->addFieldToFilter('date(created_time)', array('from' => $this->formatData($fromDate)));
        if ($toDate = $this->getRequest()->getParam('date_to'))
            $collection->addFieldToFilter('date(created_time)', array('to' => $this->formatData($toDate)));
        if ($status_list = $this->getRequest()->getParam('status')){
            $status_list = explode('-', $status_list);
            $collection->addFieldToFilter('status', array('in'=>$status_list));
        }
        $result = array();
        if($this->getRequest()->getParam('period')=='month'){
            $collection->getSelect()->group('DATE_FORMAT(created_time, "%Y-%m")');
        }  else if($this->getRequest()->getParam('period')=='year') {
            $collection->getSelect()->group('year(created_time)');
        }else{
            $collection->getSelect()->group('date(created_time)');
        }
        if ($this->tierCommissionIsEnable()) {
            $collection->getSelect()->columns(array(
                'total_amount'=>'SUM(total_amount)',
                'commission'=>'SUM(IF (ts.commission IS NULL, main_table.commission, ts.commission))',
                'rowspan'=>'COUNT(main_table.transaction_id)'
            ));
        } else {
            $collection->getSelect()->columns(array(
                'total_amount'=>'SUM(total_amount)',
                'commission'=>'SUM(commission)',
                'rowspan'=>'COUNT(transaction_id)'
            ));
        }
        foreach ($collection as $tran) {
            $result[$tran->getId()] = $tran->getRowspan();
        }
        return $result;
    }
    
    public function tierCommissionIsEnable()
    {
        if (!Mage::getConfig()->getNode('modules/Magestore_Affiliatepluslevel')) {
            return false;
        }
        $isActive = Mage::getConfig()->getNode('modules/Magestore_Affiliatepluslevel/active');
        if ($isActive && in_array((string)$isActive, array('true', '1'))) {
            return true;
        }
        return false;
    }
    
    /**
     * check group by period
     * return boolean
     */
    public function isGroupBy($columnId)
    {
        $group_by = $this->getRequest()->getParam('group_by');
        $perid = $this->getRequest()->getParam('period');
        if($group_by == 1 && ($columnId == 'created_time' || $columnId == 'created_date')){
            return true;
        }
        if($group_by == 2 && $columnId == 'banner_title')
            return true;
        return false;
    }
}