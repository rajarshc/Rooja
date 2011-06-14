<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Module_Output extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Module output information');
    }

    public function getDescription() {
        return $this->__('Module output information - Note disabeling output does not disable the module');
    }

    protected function generateSummary() {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        foreach ($modules as $key => $modele) {
            if (Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $key)) {
                $this->addNotice($this->__('Module output is disabled on: %s', $key), $this->__('Module output is set to disabled however the module might still be active'));
            } else {
                $this->addPass($this->__('Module output is enabled on: %s', $key));
            }
        }
    }

}
