<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Abstract_Adminhtml_Controller extends Mage_Adminhtml_Controller_Action
implements Aitoc_Aitsys_Abstract_Model_Interface
{
    /**
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
    
    /**
     * @return Aitoc_Aitsys_Abstract_Helper
     */
    protected function _aithelper($type = 'Data')
    {
        return $this->tool()->getHelper($type);
    }
}