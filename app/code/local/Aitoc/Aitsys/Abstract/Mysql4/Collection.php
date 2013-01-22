<?php

abstract class Aitoc_Aitsys_Abstract_Mysql4_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract  
implements Aitoc_Aitsys_Abstract_Model_Interface
{
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
    
}