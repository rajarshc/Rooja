<?php
class Aitoc_Aitsys_Model_System_Config_Backend_Stores extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $path = $this->getPath();
        $path = explode('/',$path);
        $moduleKey = array_pop($path);
        $tool = Aitoc_Aitsys_Abstract_Service::get();
        $helper = $tool->getHelper();
        $module = $tool->platform()->getModule($moduleKey);
        if (!$module)
        {
            Mage::throwException($helper->__($helper->getErrorText('seg_config_stores_module_not_found'), (string)$moduleKey));
        }
        $performer = $module->getLicense()->getPerformer();
        if (!$performer)
        {
            Mage::throwException($helper->__($helper->getErrorText('seg_config_module_license_not_found'), (string)$moduleKey));
        }
        $performer->filterStoreConfigValue($this->getValue());
    }
}