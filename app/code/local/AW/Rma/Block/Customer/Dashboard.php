<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Rma_Block_Customer_Dashboard extends Mage_Core_Block_Template {
    public function __construct() {
        parent::__construct();
        switch(Mage::helper('awrma')->getMagentoVersionCode()) {
            case AW_Rma_Helper_Data::MAGENTO_VERSION_CE_13x:
                $_template = 'aw_rma/customer/dashboard13x.phtml';
                break;
            default:
                $_template='aw_rma/customer/dashboard.phtml';
        }
        $this->setTemplate($_template);
        return $this;
    }

    /**
     * Collection of RMA entities
     * @var AW_Rma_Model_Mysql4_Entity_Collection
     */
    private $_rmaEntitiesCollection = null;

    /**
     * Returns RMA entities collection with some filters. Filtered by current
     * user id or email, for sample.
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function getRmaEntitiesCollection() {

        if($this->_rmaEntitiesCollection instanceof AW_Rma_Model_Mysql4_Entity_Collection)
            return $this->_rmaEntitiesCollection;

        $this->_rmaEntitiesCollection = Mage::getModel('awrma/entity')->getCollection()
            ->setCustomerFilter(Mage::getModel('customer/session')->getId())
            ->joinStatusNames()
            ->setOrder('created_at', 'DESC');

        return $this->_rmaEntitiesCollection;
    }

    public function setRmaEntitiesCollection(AW_Rma_Model_Mysql4_Entity_Collection $collection) {
        $this->_rmaEntitiesCollection = $collection;

        return $this;
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'awrma.entity.list.pager')
            ->setCollection($this->getRmaEntitiesCollection());
        $this->setChild('pager', $pager);
        $this->getRmaEntitiesCollection()->load();

        return $this;
    }
}
