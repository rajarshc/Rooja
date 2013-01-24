<?php

class Aitoc_Aitsys_Block_Notification extends Aitoc_Aitsys_Abstract_Adminhtml_Block
{
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Notification_News
     */
    protected $_news;
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Mysql4_Notification_Collection
     */
    protected $_newCollection;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitsys/notification.phtml');
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification_News
     */
    public function getNews()
    {
        if (!$this->_news)
        {
            $this->_news = Mage::getModel('aitsys/notification_news');
            $this->_news->loadData();
        }
        return $this->_news->getNews();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Mysql4_Notification_Collection
     */
    public function getAllertsCollection()
    {
        return Mage::getResourceModel('aitsys/notification_collection')->prepareLatestSelect();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Mysql4_Notification_Collection
     */
    public function getNewCollection()
    {
        if (!$this->_newCollection)
        {
            $this->_newCollection = Mage::getResourceModel('aitsys/notification_collection');
            $this->_newCollection->addNotViewedFilter();
        }
        return $this->_newCollection;
    }
    
    public function isNewCollection()
    {
        return $this->getNewCollection()->getSize() ? true : false;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Mysql4_Notification
     */
    protected function _getNotificationResource()
    {
        return Mage::getResourceSingleton('aitsys/notification');
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Block_Notification
     */
    public function markNewCollection()
    {
        $this->_getNotificationResource()->markAsViewed($this->getNewCollection());
        return $this;
    }
    
}