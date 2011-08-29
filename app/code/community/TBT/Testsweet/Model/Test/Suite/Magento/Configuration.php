<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Configuration extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Configuration defaults');
    }

    public function getDescription() {
        return $this->__('Check configuration defaults for settings that aid in reducing issues.');
    }

    protected function generateSummary() {

        /*
          if (Mage::getStoreConfigFlag('dev/debug/template_hints'))
          $this->addPass($this->__("Template hints defaults to off"));
          else
          $this->addWarning($this->__("template hints on"), $this->__("this should be set to disabled on live stors"));


          if (Mage::getStoreConfigFlag('dev/debug/template_hints_blocks'))
          $this->addPass($this->__("Template hints blocks defaults to off"));
          else
          $this->addWarning($this->__("template hints blocks defaults to on"), $this->__("this should be set to disabled on live stors"));
         */

        if (Mage::getStoreConfigFlag('dev/log/active'))
            $this->addPass($this->__("Logging defaults to on."));
        else
            $this->addWarning($this->__("Logging defaults to off."), $this->__("Logging should be enabled by default when testing."));


        if (version_compare(Mage::getVersion(), '1.4', '>=')) {
            if (Mage::getStoreConfigFlag('dev/js/merge_files'))
                $this->addWarning($this->__("Merge Javascript files defaults to on."), $this->__("This option is risky because one JavaScript error will cause the remaining scripts to be skipped."));
            else
                $this->addPass($this->__("Merge Javascript files defaults to off."));


            if (Mage::getStoreConfigFlag('dev/css/merge_css_files'))
                $this->addWarning($this->__("Merge CSS files defaults to on."), $this->__("This option is risky because one CSS error can cause the remaining CSS to be skipped."));
            else
                $this->addPass($this->__("Merge CSS files defaults to off."));
        }
    }

}
