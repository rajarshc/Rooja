<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Model_Observer extends Aitoc_Aitsys_Abstract_Model
{
    protected $_debugerInited = false;
    
    /**
     * catalog_product_save_after
     * catalog_product_delete_after_done
     */
    public function clearCountCache(Varien_Event_Observer $observer)
    {
        foreach(array('product', 'store', 'admin') as $key)
        {
            $id = $this->tool()->getCountCacheId($key);
            Mage::app()->removeCache($id);
        }
    }
    
    /**
     * Inits debuger if debuger turned on and PHPUnit lib is available.
     * Performs reload of all licenses if there was a problem to load licenses because of early modules' init.
     * Corrects modules' status and launches install/uninstall scripts if some module was enabled/disabled through xml.
     */
    public function debugerInit()
    {
        if(!$this->_debugerInited)
        {
            $this->tool()->debuger();
            $this->tool()->platform()->reload();
            
            if($this->tool()->platform()->isNeedCorrection())
            {
                $aitsysModel = new Aitoc_Aitsys_Model_Aitsys(); 
                $aitsysModel->correction(); 
            }
            
            $this->_debugerInited = true;
        }
    }
    
    public function errorRender()
    {
        $this->tool()->platform()->renderAdminError(true);
    }
}