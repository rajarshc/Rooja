<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Module_Cache extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Modules - Extra caching');
    }

    public function getDescription() {
        return $this->__('Modules that do extra caching, have been found to cause issues when configured too aggressive.');
    }

    protected function generateSummary() {

        $knowen_cacheing_modules = array(/*'Mage_Compiler', 'Mage_PageCache',*/ 'Fooman_Speedster');

        $modules = (array) Mage::getConfig()->getNode('modules')->children();
        
        foreach ($modules as $key => $modele) {
            if (in_array((string)$key, $knowen_cacheing_modules)) {
                $this->addNotice($this->__('Module: %s - is active and is knowen to cache data.', $key), $this->__('When caching is too aggressive you might find old data or errors in pages.'));
            }
        }
    }

}
