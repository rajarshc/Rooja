<?php

class Magestore_Affiliateplus_Model_Mysql4_Action_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_customGroupSql = false;
    
	public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/action');
    }
    
    public function setCustomGroupSql($value) {
        $this->_customGroupSql = $value;
        return $this;
    }
    
    public function getSelectCountSql() {
        if ($this->_customGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->columns('COUNT(DISTINCT referer, landing_page, store_id)');
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }
}