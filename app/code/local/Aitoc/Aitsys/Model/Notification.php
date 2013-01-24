<?php

class Aitoc_Aitsys_Model_Notification extends Aitoc_Aitsys_Abstract_Model
{
    
    protected $_notifyAdmin = false;
    
    protected function _construct()
    {
        $this->_init('aitsys/notification','entity_id');
    }
    
    /**
     * 
     * @param $assignedTo
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setAssigned( $assignedTo )
    {
        return $this->setData('assigned',$assignedTo);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function assignToPlatform()
    {
        return $this->setAssigned(':platform');
    }    
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setSeverityCritical()
    {
        return $this->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL);
    }
    
    public function isCritical()
    {
        return Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL == $this->getSeverity();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setSeverityNotice()
    {
        return $this->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
    }
    
    /**
     *
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setSeverity( $severity )
    {
        return $this->setData('severity',$severity);
    }
    
    /**
     * 
     * @param $date
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setDateAdded( $date )
    {
        return $this->setData('date_added',$date);
    }
    
    /**
     *
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setTitle( $title )
    {
        return $this->setData('title',$title);
    }
    
    /**
     * 
     * @param $description
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setDescription( $description )
    {
        return $this->setData('description',$description);
    }
    
    /**
     * 
     * @param $url
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setUrl( $url )
    {
        return $this->setData('url',$url);
    }
    
    /**
     *
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setType( $type )
    {
        return $this->setData('type',$type);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setRequireNotifyAdmin( $require = true )
    {
        $this->_notifyAdmin = $require;
        return $this;
    }
    
    public function isAdminNotificationRequired()
    {
        return $this->_notifyAdmin;
    }
    
    /**
     * 
     * @param $source
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setSource( $source )
    {
        return $this->setData('source',$source);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setLocalSource()
    {
        return $this->setSource(':local');
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification
     */
    public function setCurrentDateAsAdded()
    {
        try
        {
            $time = Mage::app()->getLocale()->storeTimeStamp(0);
        }
        catch (Mage_Core_Model_Store_Exception $exc)
        {
            $time = time();
        }
        return $this->setDateAdded($this->getResource()->formatDate($time));
    }
    
    protected function _beforeSave()
    {
        if (!$this->getDateAdded())
        {
            $this->setCurrentDateAsAdded();
        }
        return $this; // no need to dispatch 'model_save_before'
        return parent::_beforeSave();
    }
    
    protected function _afterSave()
    {
        #$result = parent::_afterSave();
        if (!$this->getUrl())
        {
            $url = new Aitoc_Aitsys_Model_Custom_Core_Url();
            $url->setStore(Mage::app()->getStore(0));
            $url->setType(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK);
            $fakeUrl = $url->getBaseUrl()."?_notid=".$this->getId();
            $this->setUrl($fakeUrl);
            $this->save();
        }
        return $this;
    }
    
}