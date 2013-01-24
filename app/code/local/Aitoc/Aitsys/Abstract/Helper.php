<?php
abstract class Aitoc_Aitsys_Abstract_Helper extends Mage_Core_Helper_Abstract 
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
     * @return Mage_Adminhtml_Helper_Data
     */
    public function getAdminhtmlHelper()
    {
        return Mage::helper('adminhtml');
    }
}