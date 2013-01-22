<?php

class Aitoc_Aitsys_Model_Mysql4_News extends Aitoc_Aitsys_Abstract_Mysql4
{
    
    protected function _construct()
    {
        $this->_init('aitsys/news','entity_id');
    }
    
    /**
    * 
    * @return Aitoc_Aitsys_Model_Notification_News
    */
    public function getLatest( $type )
    {
        return $this->_getNewsCollection()->addTypeFilter($type)->addOrder('date_added')->getLastItem();
    }
    
    public function clear( $type )
    {
        $this->_getWriteAdapter()->query("DELETE FROM `{$this->getMainTable()}` WHERE `type` = ?",array($type));
        return $this;
    }
    
    /**
    * 
    * @return Aitoc_Aitsys_Model_Mysql4_News_Collection
    */
    protected function _getNewsCollection()
    {
        return Mage::getResourceModel('aitsys/news_collection');
    }
    
}