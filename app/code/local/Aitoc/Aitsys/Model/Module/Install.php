<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Model_Module_Install extends Aitoc_Aitsys_Model_Module_Abstract
{
    /**
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    public function init()
    {
        parent::init();
        $dir = $this->tool()->filesystem()->getLocalDir().str_replace('_',DS,$this->getKey()).DS;
        $this->setData('source_dir',$dir);
        return $this;
    }
    
    public function getSourceDir()
    {
        return $this->getData('source_dir');
    }

    public function getSourcePath( $suffix = '' )
    {
        $suffix = is_array($suffix) ? join(DS,$suffix) : $suffix;
        return $this->getSourceDir().$suffix;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    public function checkStatus()
    {
        $this->setInstallable(file_exists($this->getModule()->getFile()));
        $this->setStatusUninstalled();
        if ($this->isInstallable() && $this->getModule()->getValue())
        {
            $this->setStatusInstalled();
        }
        $this->tool()->testMsg('Install status set to: '.$this->getStatus());
        return $this;
    }
    
    public function isInstallable()
    {
        return $this->getInstallable();
    }

    /**
     *
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    public function uninstall( $kill = false )
    {
        $this->_uninstall($kill)->_kill();
        $this->tool()->clearCache($kill);
        if (!$kill)
        {
            $this->tool()->platform()->reset();
            $this->_resetNotificationDate()->getModule()->reset();
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    protected function _resetNotificationDate()
    {
        $store = $this->_getStore();
        $store->resetNotificationDate($this->getModule());
        $store->saveData();
        return $this;
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
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    protected function _uninstall($kill = false)
    {
        $key = $this->getKey();
        $this->tool()->testMsg('DELETE MODULE:'.$key);
        $data = array();
        
        foreach ($this->tool()->platform()->getModuleKeysForced() as $module => $value)
        {
            /* @var $module Aitoc_Aitsys_Model_Module */
            $isCurrent = $module === $this->getModule()->getKey();
            $data[$module] = $isCurrent ? false : $value;
        }
        
        $aitsysModel = new Aitoc_Aitsys_Model_Aitsys(); 
        if ($errors = $aitsysModel->saveData($data,array(),true,$kill))
        {
            $this->addErrors($errors);
        }
        return $this;
    }

    /**
     *
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    protected function _kill()
    {
        $configs = $this->getModuleConfigs();
        $this->tool()->testMsg($configs);
        foreach ($configs as $path)
        {
            if (file_exists($path))
            {
                $this->tool()->filesystem()->rmFile($path);
            }
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License
     */
    public function getLicense()
    {
        return $this->getModule()->getLicense();
    }

    public function getModuleConfigs()
    {
        $key = $this->getKey();
        $result = array();
        if ($license = $this->getLicense())
        {
            $result[] = $license->getPath();
        }
        foreach ($this->getPlatform()->getPlatformPathes() as $path)
        {
            $result[] = $path.$this->getModule()->getId().'.php';
        }
        return $result;
    }

    /**
     *
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    public function install()
    {
        $data = array();
        foreach ($this->tool()->platform()->getModuleKeysForced() as $module => $value)
        {
            /* @var $module Aitoc_Aitsys_Model_Module */
            $isCurrent = $module === $this->getModule()->getKey();
            $data[$module] = $isCurrent ? true : $value;
        }
        
        $aitsysModel = new Aitoc_Aitsys_Model_Aitsys();
        if ($errors = $aitsysModel->saveData($data,array(),true))
        {
            $this->addErrors($errors);
        }
        $this->_resetNotificationDate()->getModule()->reset();
        $this->tool()->clearCache();
        return $this;
    }
    
}