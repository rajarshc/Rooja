<?php

class Magestore_Affiliateplus_Block_Payment_List extends Mage_Core_Block_Template {

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
        $collection = Mage::getModel('affiliateplus/payment')->getCollection()
                ->addFieldToFilter('store_ids', array('finset' => Mage::app()->getStore()->getId()))
                ->addFieldToFilter('account_id', $account->getId())
                ->setOrder('request_time', 'DESC');

        Mage::dispatchEvent('affiliateplus_prepare_payments_collection', array(
            'collection' => $collection,
        ));

        $this->setCollection($collection);
    }

    public function _prepareLayout() {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'payments_pager')
                ->setTemplate('affiliateplus/html/pager.phtml')
                ->setCollection($this->getCollection());
        $this->setChild('payments_pager', $pager);

        $grid = $this->getLayout()->createBlock('affiliateplus/grid', 'payments_grid');

        // prepare column
        $grid->addColumn('id', array(
            'header' => $this->__('No.'),
            'align' => 'left',
            'render' => 'getNoNumber',
        ));

        $grid->addColumn('request_time', array(
            'header' => $this->__('Request Date'),
            'index' => 'request_time',
            'type' => 'date',
            'format' => 'medium',
            'align' => 'left',
            'width' => '121px',
            'searchable' => true,
        ));

        $grid->addColumn('amount', array(
            'header' => $this->__('Amount'),
            'align' => 'left',
            'type' => 'baseprice',
            'index' => 'amount',
            'searchable' => true,
        ));
        
        $grid->addColumn('tax_amount', array(
            'header' => $this->__('Tax'),
            'align' => 'left',
            'type' => 'baseprice',
            'index' => 'tax_amount',
            'searchable' => true,
        ));

        $grid->addColumn('fee', array(
            'header' => $this->__('Fee'),
            'align' => 'left',
            'type' => 'baseprice',
            'index' => 'fee',
            'render' => 'getFeeRow',
            'searchable' => true,
        ));

        Mage::dispatchEvent('affiliateplus_prepare_payments_columns', array(
            'grid' => $grid,
        ));

        $grid->addColumn('status', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => $this->__('Pending'),
                2 => $this->__('Processing'),
                3 => $this->__('Completed'),
                4 => $this->__('Canceled'),
            ),
            'width' => '81px',
            'searchable' => true,
        ));

        $grid->addColumn('action', array(
            'header' => $this->__('Action'),
            'align' => 'left',
            'type' => 'action',
            'render' => 'getPaymentAction',
            'action' => array(
                'label' => $this->__('View'),
                'url' => 'affiliateplus/index/viewPayment',
                'name' => 'id',
                'field' => 'payment_id',
            )
        ));

        $this->setChild('payments_grid', $grid);
        return $this;
    }

    public function getNoNumber($row) {
        return sprintf('#%d', $row->getId());
    }

    public function getFeeRow($row) {
        if ($row->getStatus() == 1)
            return $this->__('N/A');
        $fee = $row->getFee();
        if ($row->getIsPayerFee())
            $fee = 0;
        return Mage::helper('core')->currency($fee);
    }

    /* Magic 28/11/2012 */

    public function getPaymentAction($row) {
        $confirmText = Mage::helper('adminhtml')->__('Are you sure?');
        $cancelurl=$this->getUrl('affiliateplus/index/cancelPayment', array('id' => $row->getPaymentId()));
        $action = '<a href="' . $this->getUrl('affiliateplus/index/viewPayment', array('id' => $row->getPaymentId())) . '">' . $this->__('View') . '</a>';
        
        $limitDays = intval($this->_getHelper()->getPaymentConfig('cancel_days'));
        $canCancel = $limitDays ? (time() - strtotime($row->getRequestTime()) <= $limitDays * 86400) : true;
        if ($row->getStatus() <= 2 && $canCancel)
            $action .=' | <a href="javascript:void(0)" onclick="cancelPayment'.$row->getPaymentId().'()">' . $this->__('Cancel') . '</a>
                <script type="text/javascript">
                    //<![CDATA[
                        function cancelPayment'.$row->getPaymentId().'(){
                            if (confirm(\''.$confirmText.'\')){
                                setLocation(\''.$cancelurl.'\');
                            }
                        }
                    //]]>
                </script>';
        return $action;
    }

    /* End */

    public function getPagerHtml() {
        return $this->getChildHtml('payments_pager');
    }

    public function getGridHtml() {
        return $this->getChildHtml('payments_grid');
    }

    protected function _toHtml() {
        $this->getChild('payments_grid')->setCollection($this->getCollection());
        return parent::_toHtml();
    }

}