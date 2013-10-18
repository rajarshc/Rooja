<?php
class Magestore_Affiliateplusstatistic_Block_Frontend_Report_Action extends Mage_Core_Block_Template
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
	
    public function getActionCollection()
    {
        $report_type = $this->getRequest()->getParam('report_type');
        if($report_type == 2) $type = 2;
        if($report_type == 3) $type = 1;
        $group_by = $this->getRequest()->getParam('group_by');
        $period = $this->getRequest()->getParam('period');
        $date_from = $this->getRequest()->getParam('date_from');
        $date_to = $this->getRequest()->getParam('date_to');
        $this->setTemplate('affiliateplusstatistic/grid.phtml');
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
        $collection = $this->getCollection();
        return $collection;
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
        $group_by = $this->getRequest()->getParam('group_by');
        $period = $this->getRequest()->getParam('period');
        $date_from = $this->getRequest()->getParam('date_from');
        $date_to = $this->getRequest()->getParam('date_to');
        $this->setTemplate('affiliateplusstatistic/grid.phtml');
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
        $collection = Mage::getModel('affiliateplus/action')->getCollection();
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
            $collection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
        $collection->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('type', $this->getActionType())
                ->setOrder('created_date', 'ASC');

        if ($fromDate = $this->getRequest()->getParam('date_from'))
            $collection->addFieldToFilter('created_date', array('from' => $this->formatData($fromDate)));
        if ($toDate = $this->getRequest()->getParam('date_to'))
            $collection->addFieldToFilter('created_date', array('to' => $this->formatData($toDate)));
        
        
        if ($status_list = $this->getRequest()->getParam('status')){
            $status_list = explode('-', $status_list);
            $collection->addFieldToFilter('status', array('in'=>$status_list));
        }
        
        $result = array();
        /* group by period */
        if($group_by==1){
            if($period == 'day')
                $collection->getSelect()->group(array('created_date'))
                                    ->columns(array('rowspan'=>'COUNT(distinct created_date)'));
            else if($period == 'month')
                $collection->getSelect()->group(array('DATE_FORMAT(created_date, "%Y-%m")'))
                                        ->columns(array('rowspan'=>'COUNT(distinct DATE_FORMAT(created_date, "%Y-%m"))'));
            else {
                $collection->getSelect()->group(array('year(created_date)'))
                                        ->columns(array('rowspan'=>'COUNT(distinct year(created_date))'));
            }
            /* period is month*/
            if($this->getRequest()->getParam('period')=='month'){
                $collection ->getSelect()
                            ->group(array('DATE_FORMAT(created_date, "%Y-%m")'))
                            ->columns(array('rowspan'=>'COUNT(action_id)'))
                        ;
                foreach($collection as $tran){
                    $result[$tran->getId()] = $tran->getRowspan();
                }
            }  else if($this->getRequest()->getParam('period')=='year') {
                /* period is year*/
                $collection ->getSelect()
                            ->group(array('year(created_date)'))
                            ->columns(array('rowspan'=>'COUNT(action_id)'))
                        ;
                foreach($collection as $tran){
                    $result[$tran->getId()] = $tran->getRowspan();
                }
            }else{
                $collection ->getSelect()
                           ->group(array('created_date'))
                           ->columns(array('rowspan'=>'COUNT(distinct banner_id)'))
                       ;
                foreach($collection as $tran){
                    $result[$tran->getId()] = $tran->getRowspan();
                }
            }
        }else if($group_by == 2){
            /* group by banner */
            
            $collection ->getSelect()
                ->group(array('banner_id'))
                
            ;
            if($period == 'day')
                $collection->getSelect()->columns(array('rowspan'=>'COUNT(distinct created_date)'));
            else if($period == 'month')
                $collection->getSelect()->columns(array('rowspan'=>'COUNT(distinct DATE_FORMAT(created_date, "%Y-%m"))'));
            else
                $collection->getSelect()->columns(array('rowspan'=>'COUNT(distinct year(created_date))'));
            foreach($collection as $tran){
                $result[$tran->getId()] = $tran->getRowspan();
            }
        }else{
            /* group by traffic source */
            $collection ->getSelect()
                ->group('referer')
                ->columns(array('rowspan'=>'COUNT(action_id)'))
            ;
            foreach($collection as $tran){
                $result[$tran->getId()] = $tran->getRowspan();
            }
        }
        return $result;
    }
    
    
    
    public function getGroupCollection()
    {
        $group_by = $this->getRequest()->getParam('group_by');
        $period = $this->getRequest()->getParam('period');
        $date_from = $this->getRequest()->getParam('date_from');
        $date_to = $this->getRequest()->getParam('date_to');
        $this->setTemplate('affiliateplusstatistic/grid.phtml');
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
        $collection = Mage::getModel('affiliateplus/action')->getCollection();
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
            $collection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
        $collection->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('type', $this->getActionType())
                ->setOrder('created_date', 'ASC');

        if ($fromDate = $this->getRequest()->getParam('date_from'))
            $collection->addFieldToFilter('created_date', array('from' => $this->formatData($fromDate)));
        if ($toDate = $this->getRequest()->getParam('date_to'))
            $collection->addFieldToFilter('created_date', array('to' => $this->formatData($toDate)))        ;
        
        if($group_by == 1){
            if($period == 'month'){
                $collection->getSelect()->group('DATE_FORMAT(created_date, "%Y-%m")');
            }else if($period == 'year'){
                $collection->getSelect()->group('year(created_date)');
            }else
                $collection->getSelect()->group('created_date');
            $collection->getSelect()->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        }else if($group_by == 2){
            $collection->getSelect()->group('banner_id')->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        }else if($group_by == 3){
            $collection->getSelect()->group('referer')->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        }
        return $collection;
    }
    
    public function isMultiRow($row)
    {
        $spans = $this->getRowspans();
        if(isset($spans[$row->getId()]) && $spans[$row->getId()]>1){
            return true;
        }
        return false;
    }
    
    public function getMultiRows($row)
    {
        $date = new DateTime($row->getCreatedDate());
        $group_by = $this->getRequest()->getParam('group_by');
        $period = $this->getRequest()->getParam('period');
        $date_from = $this->getRequest()->getParam('date_from');
        $date_to = $this->getRequest()->getParam('date_to');
        $this->setTemplate('affiliateplusstatistic/grid.phtml');
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
        $collection = Mage::getModel('affiliateplus/action')->getCollection();
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
            $collection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
        $collection->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('type', $this->getActionType())
                ->setOrder('created_date', 'ASC');

        if ($fromDate = $this->getRequest()->getParam('date_from'))
            $collection->addFieldToFilter('created_date', array('from' => $this->formatData($fromDate)));
        if ($toDate = $this->getRequest()->getParam('date_to'))
            $collection->addFieldToFilter('created_date', array('to' => $this->formatData($toDate)))        ;
        if($period == 'month')
            $collection->getSelect()->group(array('DATE_FORMAT(created_date, "%Y-%m")','banner_id','referer'))->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        else if($period == 'year')
            $collection->getSelect()->group(array('year(created_date)','banner_id','referer'))->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        else
            $collection->getSelect()->group(array('created_date','banner_id','referer'))->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        
        if($group_by == 1){
            if($period == 'day')
                $collection->addFieldToFilter('created_date',$row->getCreatedDate());
            else if($period == 'month')
                $collection->addFieldToFilter('DATE_FORMAT(created_date, "%Y-%m")',$date->format("Y-m"));
            else 
                $collection->addFieldToFilter('year(created_date)',$date->format("Y"));
        }else if($group_by == 2){
            $collection->addFieldToFilter('banner_id',$row->getBannerId());
        }else if($group_by == 3){
            $collection->addFieldToFilter('referer',$row->getReferer());
        }
        return $collection;
    }
    /**
     * check group by period
     * return boolean
     */
    public function isGroupBy($columnId)
    {
        $group_by = $this->getRequest()->getParam('group_by');
        $period = $this->getRequest()->getParam('period');
        if($group_by == 1 && ($columnId == 'created_time' || $columnId == 'created_date')){
            return true;
        }
        if($group_by == 2 && $columnId == 'banner_title')
            return true;
        if($group_by == 3 && $columnId == 'referer')
            return true;
        return false;
    }
}