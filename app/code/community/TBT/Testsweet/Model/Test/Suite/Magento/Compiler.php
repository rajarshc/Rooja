<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Compiler extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento compiler');
    }

    public function getDescription() {
        return $this->__('Status of magento compiler');
    }

    protected function generateSummary() {

        // TODO: check if files are compiled and if the compiler is enabled...
        // This only checkes if files have been compiled but not if magento is using them or not
        $count = Mage::getModel('compiler/process')->getCollectedFilesCount();
        if ($count == 0)
            $this->addPass($this->__("Magento compiler has no compiled files"));
        else
            $this->addWarning($this->__("Magento compiler might be enabled, you have %s compiled files", $count), "you will need to remmember to rebuild compiled files after changes to files");
    }

}
