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
        
        
        if ($status_list = $this->getRequest()->getParam('status')){
            $status_list = explode('-', $status_list);
            $collection->addFieldToFilter('status', array('in'=>$status_list));
        }
        
        /*if($this->getRequest()->getParam('period')=='month'){
            $collection ->getSelect()
                        ->group('month(created_time)')
                        ->columns(array('total_amount'=>'SUM(total_amount)','commission'=>'SUM(commission)','rowspan'=>'COUNT(transaction_id)'))
                    ;
        }else if($this->getRequest()->getParam('period')=='year'){
            $collection ->getSelect()
                        ->group('year(created_time)')
                        ->columns(array('total_amount'=>'SUM(total_amount)','commission'=>'SUM(commission)','rowspan'=>'COUNT(transaction_id)'))
                    ;
        }
        Zend_Debug::dump($collection->toArray());*/
        $this->setCollection($collection);
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

    public function getRowspans(){
        return parent::getRowspans(1);
    }
    public function _prepareLayout() {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales_pager')->setCollection($this->getCollection());
        $this->setChild('sales_pager', $pager);

        $grid = $this->getLayout()->createBlock('affiliateplusstatistic/frontend_report_action', 'sales_grid');
        $grid->setActionType(1);
        $grid->setTemplate('affiliateplusstatistic/report/grid.phtml');
        // prepare column
        $totals = $this->getTotals();
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
        
        $grid->addColumn('landing_page', array(
            'header' => $this->__('Landing Page'),
            'align' => 'left',
            //'total'		=> $totals['total_amount'],
            'index' => 'landing_page'
        ));

        $grid->addColumn('referer', array(
            'header' => $this->__('Traffic Source'),
            'align' => 'left',
            //'total'		=> $totals['commission'],
            'index' => 'referer'
        ));
        $grid->addColumn('totals', array(
            'header' => $this->__('Impressions (Unique/ Raw)'),
            'align' => 'left',
            'render' => 'getImpressions',
            'index' => 'totals'
        ));

        /*$grid->addColumn('commission_plus', array(
            'header' => $this->__('Additional') . '<br />' . $this->__('Commission'),
            'align' => 'left',
            'type' => 'baseprice',
            'index' => 'commission_plus',
            'render' => 'getCommissionPlus'
        ));

        Mage::dispatchEvent('affiliateplus_prepare_sales_columns', array(
            'grid' => $grid,
        ));

        $grid->addColumn('status', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => $this->__('Complete'),
                2 => $this->__('Pending'),
                3 => $this->__('Cancel')
            )
        ));
        */
        $this->setChild('sales_grid', $grid);
        return $this;
    }
    
    public function getTotals()
    {
        $col = $this->getCollection();
        $result = array();
        foreach($col as $elem){
            $result['total_amount'] += $elem->getTotalAmount();
            $result['commission'] += $elem->getCommission();
        }
        return $result;
    }


    public function getNoNumber($row) {
        return sprintf('#%d', $row->getId());
    }

    public function getFrontendProductHtmls($row) {
        return Mage::helper('affiliateplus')->getFrontendProductHtmls($row->getData('order_item_ids'));
    }
    
    
    public function getImpressions($row){
        $date = new DateTime($row->getCreatedDate());
        
        $collection = Mage::getModel('affiliateplus/action')->getCollection()
                        ->addFieldToFilter('account_id',$row->getAccountId())
                        ->addFieldToFilter('banner_id',$row->getBannerId())
                        ->addFieldToFilter('created_date',array('eq'=>$date->format('Y-m-d')))
                        //->addFieldToFilter('store_id',$row->getStoreId())
                        ->addFieldToFilter('type',$row->getType())
                ;
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
			$collection->addFieldToFilter('store_id',Mage::app()->getStore()->getId());
        $collection->getSelect()
                    ->group('created_date')
                    ->columns(array('uniques'=>'SUM(is_unique)','raws'=>'SUM(totals)'))
                ;
        //Zend_Debug::dump($collection->getAllIds());
        $raw = 0;
        if($collection->getSize()) {
            $unique = $collection->getFirstItem()->getUniques();        
            $raw = $collection->getFirstItem()->getRaws();        
            
        }
        return $unique.'/'.$raw;
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

    public function getCommissionPlus($row) {
        $addCommission = $row->getPercentPlus() * $row->getCommission() / 100 + $row->getCommissionPlus();
        return Mage::helper('core')->currency($addCommission); //Mage::app()->getStore()->getBaseCurrency()->format($addCommission);
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

    public function getStatisticInfo() {
        $accountId = Mage::getSingleton('affiliateplus/session')->getAccount()->getId();
        $storeId = Mage::app()->getStore()->getId();
        $scope = Mage::getStoreConfig('affiliateplus/account/balance', $storeId);

        $collection = Mage::getModel('affiliateplus/transaction')->getCollection()
                ->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('type', '0');

        $transactionTable = Mage::getModel('core/resource')->getTableName('affiliatepluslevel_transaction');
        if (Mage::helper('affiliateplus')->multilevelIsActive())
            $collection->getSelect()
                    ->joinLeft(array('ts' => $transactionTable), "ts.transaction_id = main_table.transaction_id", array('level' => 'level', 'plus_commission' => 'commission_plus'))
                    ->columns("if (ts.commission IS NULL, main_table.commission, ts.commission) as commission")
                    ->where("ts.tier_id=$accountId OR (ts.tier_id IS NULL AND main_table.account_id = $accountId )");

        if ($storeId && $scope == 'store')
            $collection->addFieldToFilter('store_id', $storeId);

        $totalCommission = 0;
        foreach ($collection as $item) {
            if ($item->getStatus() == 1) {
                $totalCommission += $item->getCommission();
                if ($item->getPlusCommission())
                    $totalCommission += $item->getPlusCommission();
                else
                    $totalCommission += $item->getCommissionPlus() + $item->getCommission() * $item->getPercentPlus() / 100;
            }
        }

        return array(
            'number_commission' => count($collection),
            'transactions' => $this->__('Standard Transactions'),
            'commissions' => $totalCommission,
            'earning' => $this->__('Standard Earnings')
        );
    }
    
    public function getCsv(){
		$csv = '';
		$this->_isExport = true;
		/*$this->_prepareGrid();*/
		$this->getCollection()->getSelect()->limit();
		$this->getCollection()->setPageSize(0);
		$this->getCollection()->load();
		//$this->_afterLoadCollection();
		
		$this->addColumn('created_time',array('index' => 'created_time'));
		$this->addColumn('order_item_names',array('index' => 'order_item_names'));
		$this->addColumn('total_amount',array('index' => 'total_amount'));
		$this->addColumn('commission',array('index' => 'commission'));

		$data = array();
		foreach ($this->_columns as $column)
            $data[] = '"'.$column['index'].'"';
		
		$csv .= implode(',', $data)."\n";

		foreach ($this->getCollection() as $item) {
			$data = array();
			foreach ($this->_columns as $column){
					$data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $item->getData($column['index'])).'"';
			}
			$csv .= implode(',', $data)."\n";
		}
        $totals = $this->getTotals();       
        $data = array();
        $totalObject['created_time'] = 'Total';
        $totalObject['order_item_names'] = '';
        $totalObject['total_amount'] = $totals['total_amount'];
        $totalObject['commission'] = $totals['commission'];
        
        foreach ($this->_columns as $column){
            $data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $totalObject[$column['index']]).'"';
        }
        $csv .= implode(',', $data)."\n";

		return $csv;
        
	}

}