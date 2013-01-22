<?php

class Aitoc_Aitsys_Model_Mysql4_Notification extends Aitoc_Aitsys_Abstract_Mysql4
{
    
    protected function _construct()
    {
        $this->_init('aitsys/notification','entity_id');
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $result = parent::_afterSave($object);
        /* @var $object Aitoc_Aitsys_Model_Notification */
        if ($object->isAdminNotificationRequired() && $object->getUrl())
        {
            $this->_storeAdminNotification($object)
            ->_sendAdminEmail($object);
            $object->setRequireNotifyAdmin(false);
        }
        return $result;
    }
    
    /**
     * 
     * @param Aitoc_Aitsys_Model_Notification $object
     * @return Aitoc_Aitsys_Model_Mysql4_Notification
     */
    protected function _storeAdminNotification( Aitoc_Aitsys_Model_Notification $object )
    {
        $data = $object->getData();
        Mage::getModel('adminnotification/inbox')->parse(array(array(
            'severity' => $object->getSeverity(),
            'date_added' => $object->getDateAdded(),
            'title' => $object->getTitle(),
        	'description' => $object->getDescription(),
            'url' => $object->getUrl(),
            'is_read' => 1
        )));
        return $this;
    }
    
    /**
     * 
     * @param Aitoc_Aitsys_Model_Mysql4_Notification_Collection $collection
     * @return Aitoc_Aitsys_Model_Mysql4_Notification
     */
    public function markAsViewed( Aitoc_Aitsys_Model_Mysql4_Notification_Collection $collection )
    { 
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),array('viewed'=>1),
            $this->_getWriteAdapter()->quoteInto('entity_id IN(?)', $collection->getAllIds())
        );
        return $this;
    }
    
    /**
     * 
     * @param Aitoc_Aitsys_Model_Notification $object
     * @return Aitoc_Aitsys_Model_Mysql4_Notification
     */
    protected function _sendAdminEmail( Aitoc_Aitsys_Model_Notification $object )
    {
        if (!$object->isCritical())
        {
            return $this; 
        }
        try
        {
            $email = Mage::getModel('core/email');
            /* @var $email Mage_Core_Model_Email */
            $name = Mage::getStoreConfig('trans_email/ident_general/name', 0);
            $address = Mage::getStoreConfig('trans_email/ident_general/email', 0);
            $email->setFromName($name);
            $email->setFromEmail($address);
            $email->setType('text');
            $email->setToName($name);
            $email->setToEmail($address);
            $email->setSubject($object->getTitle());
            $email->setBody(strip_tags($object->getDescription()));
            $email->send();
        }
        catch (Exception $exc)
        {
            $this->tool()->testMsg($exc);
        }
        return $this;
    }
    
}