<?php

class Magestore_Affiliateplus_Block_Sales_Standard extends Mage_Core_Block_Template {

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
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
        $collection = Mage::getModel('affiliateplus/transaction')->getCollection();
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
            $collection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
        $collection->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('type', '3')
                ->setOrder('created_time', 'DESC');
        Mage::dispatchEvent('affiliateplus_prepare_sales_collection', array(
            'collection' => $collection,
        ));
        
        $this->setCollection($collection);
    }

    public function _prepareLayout() {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales_pager')
                ->setTemplate('affiliateplus/html/pager.phtml')
                ->setCollection($this->getCollection());
        $this->setChild('sales_pager', $pager);

        $grid = $this->getLayout()->createBlock('affiliateplus/grid', 'sales_grid');

        // prepare column
        $grid->addColumn('id', array(
            'header' => $this->__('No.'),
            'align' => 'left',
            'render' => 'getNoNumber',
        ));

        $grid->addColumn('created_time', array(
            'header' => $this->__('Date'),
            'index' => 'created_time',
            'type' => 'date',
            'format' => 'medium',
            'align' => 'left',
            'searchable'    => true,
        ));

        $grid->addColumn('order_item_names', array(
            'header' => $this->__('Products Name'),
            'index' => 'order_item_names',
            'align' => 'left',
            'render' => 'getFrontendProductHtmls',
            'searchable'    => true,
        ));

        $grid->addColumn('total_amount', array(
            'header' => $this->__('Total Amount'),
            'align' => 'left',
            'type' => 'baseprice',
            'index' => 'total_amount',
        ));

        $grid->addColumn('commission', array(
            'header' => $this->__('Commission'),
            'align' => 'left',
            'type' => 'baseprice',
            'index' => 'commission',
        ));

        Mage::dispatchEvent('affiliateplus_prepare_sales_columns_plus', array(
            'grid'  => $grid,
        ));

        Mage::dispatchEvent('affiliateplus_prepare_sales_columns', array(
            'grid' => $grid,
        ));

        $grid->addColumn('status', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'width' => '55px',
            'type' => 'options',
            'options' => array(
                1 => $this->__('Completed'),
                2 => $this->__('Pending'),
                3 => $this->__('Canceled'),
                4 => $this->__('On Hold'),
            ),
            'searchable'    => true,
        ));

        $this->setChild('sales_grid', $grid);
        return $this;
    }

    public function getNoNumber($row) {
        return sprintf('#%d', $row->getId());
    }

    public function getFrontendProductHtmls($row) {
        return Mage::helper('affiliateplus')->getFrontendProductHtmls($row->getData('order_item_ids'));
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
                ->addFieldToFilter('type', '3');

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
            'transactions' => $this->__('Sales Transactions'),
            'commissions' => $totalCommission,
            'earning' => $this->__('Sales Earnings')
        );
    }

}