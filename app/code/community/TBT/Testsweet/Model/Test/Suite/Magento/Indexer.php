<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Indexer extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento Indexer');
    }

    public function getDescription() {
        return $this->__('List module information');
    }

    protected function generateSummary() {

        foreach (Mage::getSingleton('index/indexer')->getProcessesCollection() as $processes) {
            if ($processes->status != Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)
                $this->addPass($this->__("{$processes->indexer_code} -- {$processes->status}"));
            else
                $this->addWarning($this->__("{$processes->indexer_code} -- {$processes->status}"), $this->__("You should reindex {$processes->indexer_code}"));
        }
    }

}
