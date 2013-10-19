<?php

class Magestore_Affiliateplusstatistic_Block_Frontend_Report_Sales_Grid extends Magestore_Affiliateplusstatistic_Block_Frontend_Report_Grid {

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
    
    public function getRowspan($collection,$field,$id)
    {
        return 'hehe';
        /*die('1');
        $clone = clone $collection;
        $clone->addFieldToFilter('transaction_id',array('gteq'=>$id))->getSelect()->group($field);
        Zend_Debug::dump($id);
        if($clone->getFirstItem()->getId()==$id)
            return count($clone);*/
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
        // $pager = $this->getLayout()->createBlock('page/html_pager', 'sales_pager')->setCollection($this->getCollection());
        // $this->setChild('sales_pager', $pager);

        $grid = $this->getLayout()->createBlock('affiliateplusstatistic/frontend_report_grid', 'sales_grid');
        $grid->setTemplate('affiliateplusstatistic/report/grid.phtml');
        // prepare column
        $totals = $this->getTotals();
       $grid->addColumn('created_time', array(
            'header' => $this->__('Period'),
            'index' => 'created_time',
            'width'		=> 100,
            'sortable'	=> false,
            'render'	=> 'getMonth',
            'total_label'	=> Mage::helper('adminhtml')->__('Total'),
            'align' => 'left',
        ));
       
       /*$this->addColumn('period', array(
            'header'        => Mage::helper('sales')->__('Period'),
            'index'         => 'period',
            'width'         => 100,
            'sortable'      => false,
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'  => Mage::helper('sales')->__('Total'),
            'html_decorators' => array('nobr'),
        ));*/

        $grid->addColumn('order_item_names', array(
            'header' => $this->__('Products Name'),
            'index' => 'order_item_names',
            'align' => 'left',
            'render' => 'getFrontendProductHtmls',
        ));
        
        $grid->addColumn('total_amount', array(
            'header' => $this->__('Total Amount'),
            'align' => 'left',
            'type' => 'baseprice',
            'total'		=> $totals['total_amount'],
            'index' => 'total_amount'
        ));

        $grid->addColumn('commission', array(
            'header' => $this->__('Commission'),
            'align' => 'left',
            'type' => 'baseprice',
            'total'		=> $totals['commission'],
            'index' => 'commission'
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
        $result = array(
            'total_amount'  => 0,
            'commission'    => 0,
        );
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
    
    public function getMonth($row)
    {
        if($this->getRequest()->getParam('period')=='month'){
            return sprintf('%s', date("M Y",strtotime($row->getCreatedTime())));
        }else if ($this->getRequest()->getParam('period')=='year') {
            return sprintf('%s', date("Y",strtotime($row->getCreatedTime())));
        }else{
            return sprintf('%s', date("M d Y",strtotime($row->getCreatedTime())));
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