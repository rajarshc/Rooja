<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Compiler extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Compiler');
    }

    public function getDescription() {
        return $this->__('Status of magento compiler.');
    }

    protected function generateSummary() {

        if (!version_compare(Mage::getVersion(), '1.4', '>=')) {
            $this->addNotice($this->__("Test skipped. Test requires Magento 1.4+ , you have : %s.", Mage::getVersion()));
            return;
        }

        // TODO: check if files are compiled and if the compiler is enabled...
        // This only checkes if files have been compiled but not if magento is using them or not
        $compiler = Mage::getModel('compiler/process');
        if ($compiler) {
            $count = $compiler->getCollectedFilesCount();
            if ($count == 0)
                $this->addPass($this->__("Magento compiler has no compiled files."));
            else
                $this->addWarning($this->__("Magento compiler might be enabled, you have %s compiled files.", $count), $this->__(
                                "you will need to remmember to rebuild compiled files after changes to files."));
        } else {
            $this->addNotice($this->__("Magento compiler test skipped."));
        }
    }

}
