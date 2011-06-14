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
class AW_Rma_Model_Mysql4_Entity_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('awrma/entity');
    }

    /**
     * Adds filter to collection by customerId
     * @param int $customerId
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function setCustomerFilter($customerId) {
        return $this->addFieldToFilter('customer_id', $customerId);
    }

    /**
     * Adds filter to collection for status field
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function setActiveFilter($active = TRUE) {
        if($active)
            $this->addFieldToFilter('status', array('nin' => Mage::helper('awrma/status')->getResolvedStatuses()));
        else
            $this->addFieldToFilter('status', array('in' => Mage::helper('awrma/status')->getResolvedStatuses()));
        
        return $this;
    }

    /**
     * Filter collection by order increment id
     * @param string $incrementId
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function setOrderFilter($incrementId) {
        $this->addFieldToFilter('order_id', $incrementId);

        return $this;
    }

    /**
     * Filters collection by external_link field
     * @param string $link
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function setExternalLinkFilter($link) {
        $this->addFieldToFilter('external_link', $link);

        return $this;
    }

    /**
     * Filters collection by status
     * @param int $status
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function setStatusFilter($status) {
        $this->addFieldToFilter('main_table.status', $status);

        return $this;
    }

    /**
     * Covers bug in Magento function
     * @return Varien_Db_Select
     */
    public function getSelectCountSql() {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::HAVING);

        $countSelect->from('', 'COUNT(*)');
        return $countSelect;
    }

    /**
     * Adds some flags to each item
     */
    protected function _afterLoad() {
    }

    /**
     * Adds column with status names to collection
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function joinStatusNames() {
        $this->getSelect()
            ->joinLeft(array('s' => $this->getTable('awrma/entity_status')), 'main_table.status = s.id', array('status_name' => 's.name'));

        return $this;
    }

    /**
     * Adds column with request type names to collection
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function joinRequestNames() {
        $this->getSelect()
            ->joinLeft(array('r' => $this->getTable('awrma/entity_types')), 'main_table.request_type = r.id', array('request_name' => 'r.name'));

        return $this;
    }

    /**
     * Adds column with store id from order
     * @return AW_Rma_Model_Mysql4_Entity_Collection
     */
    public function joinOrderStore() {
        $this->getSelect()
            ->joinLeft(array('o' => $this->getTable('sales/order')), 'main_table.order_id = o.increment_id', array('store_id' => 'store_id'));

        return $this;
    }
}
