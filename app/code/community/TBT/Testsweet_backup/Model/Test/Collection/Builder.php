<?php

class TBT_Testsweet_Model_Test_Collection_Builder extends TBT_Testsweet_Model_Abstract {

    /**
     * get all the test suits compatible for this version of testsweet
     *
     * @return TBT_Testsweet_Model_Test_Suite_Abstract[] 
     */
    protected function getSuites() {
        $suite_collection = array();
        
        $testsweet_version = (string) Mage::getConfig()->getModuleConfig('TBT_Testsweet')->version;

        //$suites_nodes = Mage::getConfig()->getNode("testsweet/tests");
        //$suites_nodes = $suites_nodes->asArray();
        $suites_nodes = Mage::getConfig()->getXpath("//testsweet//tests");
        $suites_nodes = $suites_nodes[0];


        foreach ($suites_nodes as $key => $tests) {
            if ($key) {
                if (isset($tests->suites)) {
                    foreach ($tests->suites as $suites_key => $suites) {
                        foreach ($suites as $suite_key => $suite) {
                            try {
                                $suites_key = (string) $suites_key;
                                $suite = (string) $suite;

                                $r = new $suite;
                                if (version_compare($r->getRequireTestsweetVersion(), $testsweet_version, '<=')) {
                                    $suite_collection[] = $r;
                                } else {
                                    //TODO: warning test is skipped because testsweet needs an upgrade to version $r->getRequireTestsweetVersion()
                                }
                            } catch (Exception $ex) {
                                //TODO : deal with this possible error some other way?
                                echo $ex->getMessage();
                            }
                        }
                    }
                }
            }
        }
        return $suite_collection;
    }

    /**
     *
     * @param TBT_Testsweet_Model_Test_Render_Suite_Plaintext $render
     * @return TBT_Testsweet_Model_Test_Collection_Simple
     */
    public function createSimpleCollectionWithAllSuites($render = null) {
        $simple = new TBT_Testsweet_Model_Test_Collection_Simple();
        $simple->addSuites($this->getSuites());

        if ($render == null)
            $render = new TBT_Testsweet_Model_Test_Render_Suite_Plaintext();

        $simple->setRender($render);

        return $simple;
    }

}