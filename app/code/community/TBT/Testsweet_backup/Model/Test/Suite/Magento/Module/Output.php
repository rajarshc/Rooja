<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Module_Output extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Module - Output information');
    }

    public function getDescription() {
        return $this->__('Module output information - Note: disabling output does not disable the module.');
    }

    protected function generateSummary() {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        foreach ($modules as $key => $modele) {
            $defaultModules = TBT_Testsweet_Model_Test_Suite_Magento_Module_Version::getDefaultModules();
            // mark none default modul es with a star
            $markModule = ' ';
            if (!in_array($key, $defaultModules)) {
                $markModule = '*';
            }
            $outputString = "$markModule{$key}";
            
            if (Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $key)) {
                $this->addNotice($this->__("Output: disabled\t Module:%s", $outputString), $this->__('Module output is disabled however the module is still active.'));
            } else {
                $this->addPass($this->__("Output: enabled\t Module:%s", $outputString));
            }
        }
    }

}
