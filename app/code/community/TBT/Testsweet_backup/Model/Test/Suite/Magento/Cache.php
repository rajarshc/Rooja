<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Cache extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Cache');
    }

    public function getDescription() {
        return $this->__('Status of magento cache.');
    }

    protected function generateSummary() {

        if (!version_compare(Mage::getVersion(), '1.4', '>=')) {
            $this->addNotice($this->__("Test skipped. Test requires Magento 1.4+ , you have : %s.", Mage::getVersion()));
            return;
        }

        $cache = Mage::app()->getCacheInstance();

        foreach ($cache->getTypes() as $cachtype) {
            //$cachtype->cach_type
            if ($cachtype->status == 0)
                $this->addPass($this->__("Cache is disabled on: %s", $cachtype->tags));
            else
                $this->addWarning($this->__("Cache is enabled on: %s", $cachtype->tags), $this->__("Cache should be disabled while making changes to the system"));
        }
    }

}
