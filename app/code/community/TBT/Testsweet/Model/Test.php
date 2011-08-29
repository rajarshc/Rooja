<?php

class TBT_Testsweet_Model_Test {
    
    /**
     * All suites, output as plain text and filter passed cases.
     *
     * @return TBT_Testsweet_Model_Test_Collection_Simple default tester
     */
    protected function buildDefaultTester() {
        $builder = new TBT_Testsweet_Model_Test_Collection_Builder();
        $render = new TBT_Testsweet_Model_Test_Render_Suite_Plaintext();
        //$render->setFilter(TBT_Testsweet_Model_Test_Report_Abstract::STATUS_PASS);
        return $builder->createSimpleCollectionWithAllSuites($render); 
    }

    public function all() {
        $this->buildDefaultTester()->outputSummary();
    }
    
}