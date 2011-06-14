<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Module_Version extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - List module information');
    }

    public function getDescription() {
        return $this->__('List module information');
    }

    protected function generateSummary() {

        $this->addNotice($this->__("Magento version %s", Mage::getVersion()));

        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        foreach ($modules as $key => $modele) {
            $this->addNotice("$key - active: {$modele->active} - codePool:{$modele->codePool} - version:{$modele->version}");
        }
    }

}
