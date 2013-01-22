<?php

class Aitoc_Aitsys_Model_Mysql4_Notification_Collection extends Aitoc_Aitsys_Abstract_Mysql4_Collection
{
    
    protected function _construct()
    {
        $this->_init('aitsys/notification');
    }
    
    public function addNotViewedFilter()
    {
        return $this->addFilter('viewed',0);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Mysql4_Notification_Collection
     */
    public function prepareLatestSelect()
    {
        return $this->addOrder('date_added')
        ->setPageSize(5);
    }
    
}