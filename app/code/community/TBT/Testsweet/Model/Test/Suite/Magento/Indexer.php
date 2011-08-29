<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Indexer extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Indexer');
    }

    public function getDescription() {
        return $this->__('List module information.');
    }

    protected function generateSummary() {

        if (!version_compare(Mage::getVersion(), '1.4', '>=')) {
            $this->addNotice($this->__("Test skipped. Test requires Magento 1.4+ , you have : %s", Mage::getVersion()));
            return;
        }

        foreach (Mage::getSingleton('index/indexer')->getProcessesCollection() as $processes) {
            if ($processes->status != Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)
                $this->addPass($this->__("Indexer: %s -- %s",$processes->status, $processes->indexer_code));
            else
                $this->addWarning($this->__("Indexer: %s -- %s",$processes->status, $processes->indexer_code), $this->__("You should reindex %s", $processes->indexer_code));
        }
    }

}
