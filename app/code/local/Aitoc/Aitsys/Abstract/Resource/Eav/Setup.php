<?php

abstract class Aitoc_Aitsys_Abstract_Resource_Eav_Setup extends Mage_Eav_Model_Entity_Setup 
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