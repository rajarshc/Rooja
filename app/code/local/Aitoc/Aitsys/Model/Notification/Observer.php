<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Model_Notification_Observer extends Aitoc_Aitsys_Abstract_Model
{
    
    const MAX_NOTIFICATION_COUNT = 4;
    
    protected $_observedCount = null;
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification_Observer
     */
    protected function _initObservedCount()
    {
        if (null === $this->_observedCount)
        {
            $this->_observedCount = self::MAX_NOTIFICATION_COUNT;
        }
        return $this;
    }
    
    public function performPreDispatch( Varien_Event_Observer $observer )
    {
        $news = new Aitoc_Aitsys_Model_Notification_News();
        /* @var $news Aitoc_Aitsys_Model_Notification_News */
        $news->loadData();
        
        $important = new Aitoc_Aitsys_Model_Notification_Important();
        /* @var $important Aitoc_Aitsys_Model_Notification_Important */
        $important->loadData();
    }
    
    public function performPostDispatch( Varien_Event_Observer $observer )
    {
        if ($data = Mage::registry('aitsys_notification'))
        {
            $moduleKey = $data['module'];
            $type = $data['type'];
            $module = $this->tool()->platform()->getModule($moduleKey);
            $module->getLicense()->$type();
        }
    }
    
    public function performGenerateModuleListAfter( Varien_Event_Observer $observer )
    {
        if (!Mage::registry('aitsys_module_list_generated'))
        {
            $this->tool()->testMsg('Stop check module status');
            Mage::register('aitsys_module_list_generated',true);
        }
    }
    
    public function performCheckStatusAfter( Varien_Event_Observer $observer )
    {
        if (0 === $this->_observedCount)
        {
            $this->tool()->testMsg('All observation tries used');
            return;
        }
        $this->_initObservedCount();
        $module = $this->_castModule($observer);
        if (!$module->getInstall()->isInstalled())
        {
            return;
        }
        $currentNotificationDate = $this->_getStore()->getNotificationDate($module);
        if ($currentNotificationDate < time())
        {
            $store = $this->_getStore()->setNotificationDate($module)->saveData();
            $communicateParams = array('process' => 'checkStatus', 'state' => 'after');
            if ($license = $module->getLicense())
            {
                $service = $license->getService();
            }
            else
            {
                $service = $this->tool()->platform()->getService();
                $communicateParams['module_key'] = $module->getKey();
                $communicateParams['module_version'] = $module->getVersion();
            }
            try
            {
                if ($this->tool()->cleanDomain($service->getServiceUrl()) != 
                    $this->tool()->cleanDomain(
                        Mage::app()->getStore(0)->getBaseUrl()
                    ))
                {
                    $service->connect();
                    $service->communicate($communicateParams);
                    $service->disconnect();
                }
            } 
            catch (Exception $exc)
            {
                $this->tool()->testMsg($exc);
            }
            --$this->_observedCount;
        }
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification_Store
     */
    protected function _getStore()
    {
        return Mage::getSingleton('Aitoc_Aitsys_Model_Notification_Store');
    }
    
    /**
     * 
     * @param Varien_Event_Observer $observer
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _castModule( Varien_Event_Observer $observer )
    {
        return $observer->getModule();
    }
    
}