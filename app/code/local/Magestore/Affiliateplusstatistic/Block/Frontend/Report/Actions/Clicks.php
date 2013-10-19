<?php

class Magestore_Affiliateplusstatistic_Block_Frontend_Report_Actions_Clicks extends Magestore_Affiliateplusstatistic_Block_Frontend_Report_Grid {
    protected $_totalCommission = 0;

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
        $collection = $this->getClickCollection();
        $this->setCollection($collection);
    }
    
    public function getClickCollection()
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
                ->addFieldToFilter('type', '2')
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
        $grid->setCollection($this->getClickCollection());
        $grid->setActionType(2);
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
        Mage::dispatchEvent('affiliateplusstatistic_add_column_click_report', array(
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
                ->addFieldToFilter('type', '2')
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
    public function getCommission($row){
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
        return Mage::helper('core')->currency($totals);
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
        $period = $this->getRequest()->getParam('period');
        
        if($group_by == 1){
            $this->addColumn('Period',array('index' => 'created_date'));
            $this->addColumn('banner_title',array('index' => 'banner_title'));
        }else if($group_by == 2){
            $this->addColumn('banner_title',array('index' => 'banner_title'));
            $this->addColumn('Period',array('index' => 'created_date'));
        }else if($group_by == 3){
            $this->addColumn('referer',array('index' => 'referer'));
            $this->addColumn('Period',array('index' => 'created_date'));
            $this->addColumn('banner_title',array('index' => 'banner_title'));
        }
		$this->addColumn('landing_page',array('index' => 'landing_page'));
        if($group_by != 3)
            $this->addColumn('referer',array('index' => 'referer'));
        $this->addColumn('uniques',array('index' => 'is_unique'));
        $this->addColumn('raws',array('index' => 'totals'));
        
        Mage::dispatchEvent('affiliateplusstatistic_export_clicks_addrow_tocsv',array(
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
                $value = $item->getData($column['index']);
                if($column['index'] == 'created_date'){
                    $date = new DateTime($item->getData($column['index']));
                    if($period == 'month')
                        $value = $date->format ("Y-m");
                    else if($period == 'year')
                        $value = $date->format ("Y");
                }
                $data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $value).'"';
			}
			$csv .= implode(',', $data)."\n";
		}
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
        
        $totalObject['is_unique'] = $totals['uniques'];
        $totalObject['totals'] = $totals['raws'];
        
        $obj = new Varien_Object(array(
            'total'=>$totalObject
        ));
        Mage::dispatchEvent('affiliateplusstatistic_export_clicks_addtotal_tocsv',array(
            'object' =>  $obj
        ));
        $totalObject = $obj->getTotal();
        
        foreach ($this->_columns as $id=>$column){
            $data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $totalObject[$column['index']]).'"';
        }
        $csv .= implode(',', $data)."\n";
		return $csv;
        
	}
    /**
     * get xml file
     * @return string
     */
    public function getXml($sheetName)
    {
        $parser = new Varien_Convert_Parser_Xml_Excel();
        $io     = new Varien_Io_File();
        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.xml';
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWrite($parser->getHeaderXml($sheetName));
        $io->streamWrite($parser->getRowXml($this->_getExportHeaders()));
        $this->_exportIterateCollection('_exportExcelItem', array($io, $parser));
        if ($this->getCountTotals()) {
            $io->streamWrite($parser->getRowXml($this->_getExportTotals()));
        }
        $io->streamWrite($parser->getFooterXml());
        $io->streamUnlock();
        $io->streamClose();
        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
    }
    
    public function _exportIterateCollection($callback, array $args)
    {
        $originalCollection = $this->getCollection();
        $count = null;
        $page  = 1;
        $lPage = null;
        $break = false;
        while ($break !== true) {
            $collection = clone $originalCollection;
            $collection->setPageSize(1000);
            $collection->setCurPage($page);
            $collection->load();
            if (is_null($count)) {
                $count = $collection->getSize();
                $lPage = $collection->getLastPageNumber();
            }
            if ($lPage == $page) {
                $break = true;
            }
            $page ++;

            foreach ($collection as $item) {
                call_user_func_array(array($this, $callback), array_merge(array($item), $args));
            }
        }
    }
    
    /**
    *  Retrieve Headers row array for Export
    *
    * @return array
    */
    protected function _getExportHeaders()
    {
       $row = array();
       foreach ($this->_columns as $column) {
           if (!$column->getIsSystem()) {
               $row[] = $column->getExportHeader();
           }
       }
       return $row;
    }

    /**
   * Retrieve Totals row array for Export
   *
   * @return array
   */
   protected function _getExportTotals()
   {
        $totals = $this->getTotals();
        $row    = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = ($column->hasTotalsLabel()) ? $column->getTotalsLabel() : $column->getRowFieldExport($totals);
            }
        }
        return $row;
   }
   
    protected function _exportExcelItem(Varien_Object $item, Varien_Io_File $adapter, $parser = null)
    {
        if (is_null($parser)) {
            $parser = new Varien_Convert_Parser_Xml_Excel();
        }

        $row = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($item);
            }
        }
        $data = $parser->getRowXml($row);
        $adapter->streamWrite($data);
    }

}