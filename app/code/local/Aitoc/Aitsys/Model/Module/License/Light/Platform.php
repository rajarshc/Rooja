<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Model_Module_License_Light_Platform
extends Aitoc_Aitsys_Abstract_Model
{
    static protected $_instance;
    
    protected $_adminUrl;
    
    static public function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getAdminBaseUrl()
    {
        $model = new Mage_Core_Model_Url();
        $useSession = $model->getUseSession();
        $url = $model->setUseSession(0)->setStore('admin')->getUrl('adminhtml');
        $model->setUseSession($useSession);
        return $url;
    }
    
    public function getAdminUrl()
    {
        if(!$this->checkStore()) {
            return false;
        }
        if(!$this->_adminUrl)
        {
            $this->_adminUrl = $this->_aithelper('Light_Domain')->getAdminUrl($this->getAdminBaseUrl());
        }
        return $this->_adminUrl;
    }
    
    public function checkStore() {
        try {
            $storeId = Mage::app()->getStore();
            if(Mage::app()->getUpdateMode() || $storeId == null) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;        
    }
    
}