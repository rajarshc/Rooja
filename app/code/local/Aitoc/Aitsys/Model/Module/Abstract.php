<?php

abstract class Aitoc_Aitsys_Model_Module_Abstract extends Aitoc_Aitsys_Abstract_Model
{
    
    const STATUS_UNKNOWN = 'unknown';
    
    const STATUS_INSTALLED = 'installed';
    
    const STATUS_UNINSTALLED = 'uninstalled';
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_Abstract
     */
    public function init()
    {
        $this->setStatusUnknown();
        return $this;
    }
    
    /**
     * 
     * @param $module
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    public function setModule( $module )
    {
        return $this->setData('module',$module);
    }
    
    /**
     * 
     * @param array $errors
     * @return Aitoc_Aitsys_Model_Module_Abstract
     */
    public function addErrors( array $errors )
    {
        $this->getModule()->addErrors($errors);
        return $this;
    }
    
    /**
     * 
     * @param $error
     * @return Aitoc_Aitsys_Model_Module_Abstract
     */
    public function addError( $error )
    {
        $this->getModule()->addError($error);
        return $this;
    }
    
    public function getErrors( $clear = false )
    {
        return $this->getModule()->getErrors($clear);
    }
    

    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function getPlatform()
    {
        return $this->tool()->platform();
    }
    
    public function getPlatformId()
    {
        return $this->tool()->platform()->getPlatformId();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return $this->getData('module');
    }
    
    public function getStatus()
    {
        return $this->getData('status');
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    public function setStatusInstalled()
    {
        return $this->setStatus(self::STATUS_INSTALLED);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    public function setStatusUnknown()
    {
        return $this->setStatus(self::STATUS_UNKNOWN);
    }
    
    public function setPath( $path )
    {
        return $this->setData('path',$path);
    }
    
    public function getPath()
    {
        return $this->getData('path');
    }
    
    public function getKey()
    {
        return $this->getModule()->getKey();
    }
    
    public function getLinkId()
    {
        return $this->getModule()->getLinkId();
    }
    
    public function isInstalled()
    {
        return $this->getStatus() == self::STATUS_INSTALLED;
    }
    
    public function isUnknown()
    {
        return $this->getStatus() == self::STATUS_UNKNOWN;
    }
    
    public function isUninstalled()
    {
        return $this->getStatus() == self::STATUS_UNINSTALLED;
    }
    
    public function setStatusUninstalled()
    {
        return $this->setStatus(self::STATUS_UNINSTALLED);
    }
    
    public function setStatus( $status )
    {
        return $this->setData('status',$status);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_Abstract
     */
    abstract public function checkStatus();
    
}
