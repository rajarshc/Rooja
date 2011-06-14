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
class AW_Rma_Model_Mysql4_Entitytypes_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('awrma/entitytypes');
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

    protected function _afterLoad() {
        foreach($this->getItems() as $_item) {
            $_item->setStore(explode(',', $_item->getStore()));
        }
    }

    public function setStoreFilter($stores = null) {
        $_stores = array(Mage::app()->getStore()->getId());
        if(is_string($stores)) $_stores = explode(',', $stores);
        if(is_array($stores)) $_stores = $stores;
        array_push($_stores, 0);

        $i = 0;
        foreach($_stores as $_store)
            if($i++ == 0)
                $this->getSelect()->where('find_in_set(?, store)', $_store);
            else
                $this->getSelect()->orWhere('find_in_set(?, store)', $_store);

        return $this;
    }

    public function getOptions() {
        $_options = array();
        $this->load();
        foreach($this->getItems() as $_item)
            $_options[$_item->getId()] = $_item->getName();

        return $_options;
    }

    public function setDefaultSort() {
        $this->getSelect()->order('sort ASC');
        return $this;
    }

    public function setActiveFilter($active = TRUE) {
        $this->getSelect()->where('enabled = ?', intval($active));
        return $this;
    }
}
