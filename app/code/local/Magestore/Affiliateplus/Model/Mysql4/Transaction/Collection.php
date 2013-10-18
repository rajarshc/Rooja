<?php

class Magestore_Affiliateplus_Model_Mysql4_Transaction_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_isGroupSql = false;

    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/transaction');
    }

    public function setIsGroupCountSql($value) {
        $this->_isGroupSql = $value;
        return $this;
    }

    public function getSelectCountSql() {
        if ($this->_isGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
                $countSelect->reset(Zend_Db_Select::GROUP);
                $countSelect->distinct(true);
                $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
                $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
            } else {
                $countSelect->columns('COUNT(*)');
            }
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }
}
