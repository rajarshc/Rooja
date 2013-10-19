<?php

class Magestore_Affiliateplusstatistic_Block_Frontend_Report_Actions_Impressions extends Magestore_Affiliateplusstatistic_Block_Frontend_Report_Grid {

    /**
     * get Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    public function _getHelper() {
        return Mage::helper('affiliateplus/config');
    }

    protected function _construct() {
       parent::_construct();
        $collection = $this->getImpressionCollection();
        $this->setCollection($collection);
    }
    
    public function getImpressionCollection()
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
                ->addFieldToFilter('type', '1')
                ->setOrder('created_date', 'ASC');

        if ($fromDate = $this->getRequest()->getParam('date_from'))
            $collection->addFieldToFilter('created_date', array('from' => $this->formatData($fromDate)));
        if ($toDate = $this->getRequest()->getParam('date_to'))
            $collection->addFieldToFilter('created_date', array('to' => $this->formatData($toDate)));
        if($period == 'day')
            $collection->getSelect()->group(array('created_date','banner_id'))->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        else if($period == 'month')
            $collection->getSelect()->group(array('DATE_FORMAT(created_date, "%Y-%m")','banner_id'))->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        else {
            $collection->getSelect()->group(array('year(created_date)','banner_id'))->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'));
        }
        
        if ($status_list = $this->getRequest()->getParam('status')){
            $status_list = explode('-', $status_list);
            $collection->addFieldToFilter('status', array('in'=>$status_list));
        }
        //Zend_Debug::dump($collection->getSelect()->__toString());
        return $collection;
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

    public function _prepareLayout() {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales_pager')->setCollection($this->getCollection());
        $this->setChild('sales_pager', $pager);

        $grid = $this->getLayout()->createBlock('affiliateplusstatistic/frontend_report_action', 'sales_grid');
        $grid->setActionType(1);
        $data = $this->getFilterData();
        $grid->setTemplate('affiliateplusstatistic/report/view.phtml');
        // prepare column
        $totals = $this->getTotals();
       $group_by = $this->getRequest()->getParam('group_by');
        if($group_by == 1){
            $grid->addColumn('created_date', array(
                 'header' => $this->__('Period'),
                 'index' => 'created_date',
                 'width'		=> 100,
                 'sortable'	=> false,
                 'render'	=> 'getPeriod',
                 'total_label'	=> Mage::helper('adminhtml')->__('Total'),
                 'align' => 'left',
             ));
            $grid->addColumn('banner_title', array(
                'header' => $this->__('Banner'),
                'index' => 'banner_title',
                'align' => 'left',
                //'render' => 'getFrontendProductHtmls',
            ));
        }else if($group_by == 2){
            $grid->addColumn('banner_title', array(
                'header' => $this->__('Banner'),
                'index' => 'banner_title',
                'align' => 'left',
                'total_label'	=> Mage::helper('adminhtml')->__('Total'),
            ));
            
            $grid->addColumn('created_date', array(
                 'header' => $this->__('Period'),
                 'index' => 'created_date',
                 'width'		=> 100,
                 'sortable'	=> false,
                 'render'	=> 'getPeriod',
                 'align' => 'left',
             ));
        }else if($group_by == 3){
            $grid->addColumn('referer', array(
                'header' => $this->__('Traffic Source'),
                'align' => 'left',
                //'total'		=> $totals['commission'],
                'index' => 'referer',
                'total_label'	=> Mage::helper('adminhtml')->__('Total'),
            ));
            
            
            $grid->addColumn('created_date', array(
                 'header' => $this->__('Period'),
                 'index' => 'created_date',
                 'width'		=> 100,
                 'sortable'	=> false,
                 'render'	=> 'getPeriod',
                 'align' => 'left',
             ));
            $grid->addColumn('banner_title', array(
                'header' => $this->__('Banner'),
                'index' => 'banner_title',
                'align' => 'left',
                
            ));
        }
        
        $grid->addColumn('landing_page', array(
            'header' => $this->__('Landing Page'),
            'align' => 'left',
            //'total'		=> $totals['total_amount'],
            'index' => 'landing_page'
        ));

        if($group_by == 1 || $group_by == 2)
            $grid->addColumn('referer', array(
                'header' => $this->__('Traffic Source'),
                'align' => 'left',
                //'total'		=> $totals['commission'],
                'index' => 'referer'
            ));
        $grid->addColumn('uniques', array(
            'header' => $this->__('Unique'),
            'align' => 'left',
            //'render' => 'getUniqueClicks',
            'total' => $totals['uniques'],
            'index' => 'uniques'
        ));
         $grid->addColumn('raws', array(
            'header' => $this->__('Raw'),
            'align' => 'left',
            //'render' => 'getRawClicks',
            'total' => $totals['raws'],
            'index' => 'raws'
        ));
        Mage::dispatchEvent('affiliateplusstatistic_add_column_impression_report', array(
            'grid' => $grid,
        ));
        
        $this->setChild('sales_grid', $grid);
        return $this;
    }
    
   
    
    public function getTotals()
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
                ->addFieldToFilter('type', '1')
                ->setOrder('created_date', 'ASC');

        if ($fromDate = $this->getRequest()->getParam('date_from'))
            $collection->addFieldToFilter('created_date', array('from' => $this->formatData($fromDate)));
        if ($toDate = $this->getRequest()->getParam('date_to'))
            $collection->addFieldToFilter('created_date', array('to' => $this->formatData($toDate)));
        $collection->getSelect()
                    ->group('type')
                    ->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'))
                ;
        
        $totals = array();
        if($collection->getSize()) {
            $unique = $collection->getFirstItem()->getUniques();        
            $raw = $collection->getFirstItem()->getRaws();        
            
        }
        if($unique) $totals['uniques'] = $unique;
        if($unique) $totals['raws'] = $raw;
        
        return $totals;
    }
    
   

    public function getRowspans(){
        return parent::getRowspans(1);
    }
    public function getNoNumber($row) {
        return sprintf('#%d', $row->getId());
    }

    public function getFrontendProductHtmls($row) {
        return Mage::helper('affiliateplus')->getFrontendProductHtmls($row->getData('order_item_ids'));
    }
    
    public function getPeriod($row)
    {
        if($this->getRequest()->getParam('period')=='month'){
            return sprintf('%s', date("M Y",strtotime($row->getCreatedDate())));
        }else if ($this->getRequest()->getParam('period')=='year') {
            return sprintf('%s', date("Y",strtotime($row->getCreatedDate())));
        }else{
            return sprintf('%s', date("M d Y",strtotime($row->getCreatedDate())));
        }
    }

    public function getPagerHtml() {
        return $this->getChildHtml('sales_pager');
    }

    public function getGridHtml() {
        return $this->getChildHtml('sales_grid');
    }

    protected function _toHtml() {
        $this->getChild('sales_grid')->setCollection($this->getCollection());
        return parent::_toHtml();
    }
    
    public function getCommission($row){
        $totals = $this->getCommissionOfRow($row);
        return Mage::helper('core')->currency($totals);
    }
    
    public function getCommissionOfRow($row)
    {
        $totals = 0;
        $accountId = $row->getAccountId();
        $period = $this->getRequest()->getParam('period');
        $bannerId = $row->getBannerId();
        $type = $row->getType();
        $storeId = $row->getStoreId();
        $createdDate = new DateTime($row->getCreatedDate());
        $collection = Mage::getModel('affiliateplus/transaction')
                        ->getCollection()
                        ->addFieldToFilter('account_id',$accountId)
                        ->addFieldToFilter('banner_id',$bannerId)
                        ->addFieldToFilter('type',$type)
                        //->addFieldToFilter('date(created_time)',array('eq'=>$createdDate))
                        ;
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
            $collection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
        if($period == 'day')
            $collection->addFieldToFilter('DATE_FORMAT(created_time, "%Y-%m-%d")',$createdDate->format("Y-m-d"));
        else if($period == 'month')
            $collection->addFieldToFilter('DATE_FORMAT(created_time, "%Y-%m")',$createdDate->format("Y-m"));
        else {
            $collection->addFieldToFilter('DATE_FORMAT(created_time, "%Y")',$createdDate->format("Y"));
        }
        $collection->getSelect()->group('type')->columns(array('total_commission'=>'SUM(commission)'));
        if($collection->getSize())
            $totals = $collection->getFirstItem()->getTotalCommission();
        return $totals;
    }


    /**
     * get csv data from grid
     * @return string
     * 
     */
    public function getCsv(){
		$csv = '';
		$this->_isExport = true;
		/*$this->_prepareGrid();*/
		$this->getCollection()->getSelect()->limit();
		$this->getCollection()->setPageSize(0);
		$this->getCollection()->load();
		//$this->_afterLoadCollection();
		$group_by = $this->getRequest()->getParam('group_by');
		if($group_by == 1){
            $this->addColumn('created_date',array('index' => 'created_date'));
            $this->addColumn('banner_title',array('index' => 'banner_title'));
        }else if($group_by == 2){
            $this->addColumn('banner_title',array('index' => 'banner_title'));
            $this->addColumn('created_date',array('index' => 'created_date'));
        }else if($group_by == 3){
            $this->addColumn('referer',array('index' => 'referer'));
            $this->addColumn('created_date',array('index' => 'created_date'));
            $this->addColumn('banner_title',array('index' => 'banner_title'));
        }
		$this->addColumn('landing_page',array('index' => 'landing_page'));
        if($group_by != 3)
            $this->addColumn('referer',array('index' => 'referer'));
        $this->addColumn('uniques',array('index' => 'is_unique'));
        $this->addColumn('raws',array('index' => 'totals'));
        
        Mage::dispatchEvent('affiliateplusstatistic_export_impression_addrow_tocsv',array(
            'block' =>  $this
        ));
        
		$data = array();
		foreach ($this->_columns as $id=>$column){
            $data[] = '"'.$id.'"';
        }
		$csv .= implode(',', $data)."\n";

		foreach ($this->getCollection() as $item) {
			$data = array();
			foreach ($this->_columns as $column){
                if($column['index'] == 'commission')
                    $data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $this->getCommissionOfRow($item)).'"';
                else
                    $data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $item->getData($column['index'])).'"';
			}
			$csv .= implode(',', $data)."\n";
		}
        //die('2');
        $totals = $this->getTotals();       
        $data = array();
       if($group_by == 1){
            $totalObject['created_date'] = 'Total';
            $totalObject['banner_title'] = '';
            $totalObject['referer'] = '';
        }else if($group_by == 2){
            $totalObject['created_date'] = '';
            $totalObject['banner_title'] = 'Total';
            $totalObject['referer'] = '';
        }else if($group_by == 3){
            $totalObject['created_date'] = '';
            $totalObject['banner_title'] = '';
            $totalObject['referer'] = 'Total';
        }
        $totalObject['landing_page'] = '';
        //$totalObject['referer'] = '';
        $totalObject['is_unique'] = $totals['uniques'];
        $totalObject['totals'] = $totals['raws'];
        
        $obj = new Varien_Object(array(
            'total'=>$totalObject
        ));
        Mage::dispatchEvent('affiliateplusstatistic_export_impression_addtotal_tocsv',array(
            'object' =>  $obj
        ));
        $totalObject = $obj->getTotal();
        foreach ($this->_columns as $id=>$column){
            if($column['index'] == 'commission')
                $data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $totalObject[$column['index']]).'"';
            else
                $data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $totalObject[$column['index']]).'"';
        }
        $csv .= implode(',', $data)."\n";
		return $csv;
        
	}

}