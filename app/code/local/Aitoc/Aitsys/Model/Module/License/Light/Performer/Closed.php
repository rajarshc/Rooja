<?php
class Aitoc_Aitsys_Model_Module_License_Light_Performer_Closed
extends Aitoc_Aitsys_Abstract_Model
{
    protected function _construct()
    {
        $this->_init('aitsys/module_license_light_performer_closed');
    }
    
    /**
     * @override
     */
    public function load($id, $field=null)
    {
        if(version_compare($this->tool()->db()->dbVersion(),'2.15.0','ge'))
        {
            return parent::load($id, $field);
        }
        return $this;
    }
}