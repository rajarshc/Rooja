<?php

class Aitoc_Aitsys_Model_License_Service_light extends Aitoc_Aitsys_Model_License_Service
{
    
    protected $_prefix = 'aitseg_license_servicelight';
    
    /**
     * 
     * @param $args
     * @return Aitoc_Aitsys_Model_License_Service
     */
    protected function _updateArgs( &$args )
    {
        parent::_updateArgs($args);
        $args[0]['base_url'] = $this->_license->getPlatform()->getAdminBaseUrl();
        return $this;
    }    
}
