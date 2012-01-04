<?php

abstract class TBT_Testsweet_Model_Test_Collection_Abstract extends TBT_Testsweet_Model_Abstract {

    protected $_suites = array();
    protected $_cases = array();
    //TBT_Testsweet_Model_Test_Render_Suite_Abstract
    protected $_render = array();

    /**
     *
     * @param TBT_Testsweet_Model_Test_Suite_Abstract[] $suites
     * @return TBT_Testsweet_Model_Test_Collection_Abstract
     */
    public function addSuites($suites) {
        $this->_suites = array_merge($this->_suites, $suites);
        return $this;
    }
    
    /**
     *
     * @param TBT_Testsweet_Model_Test_Suite_Abstract $suite
     * @return TBT_Testsweet_Model_Test_Collection_Abstract 
     */
        public function addSuite($suite) {
        $this->_suites[] = $suite;
        return $this;
    }

    /**
     * @return TBT_Testsweet_Model_Test_Render_Suite_Abstract[]
     */
    public function getSuites() {
        return $this->_suites;
    }

    /**
     *
     * @param TBT_Testsweet_Model_Test_Render_Suite_Abstract[] $suites
     * @return TBT_Testsweet_Model_Test_Collection_Abstract
     */
    public function setSuites($suites) {
        $this->_suites = $suites;
        return $this;
    }

    /**
     *
     * @param TBT_Testsweet_Model_Test_Render_Case_Abstract $cases
     * @return TBT_Testsweet_Model_Test_Collection_Abstract
     */
    public function addCase($case) {
        $this->_cases[]= $case;
        return $this;
    }

        /**
     * @return TBT_Testsweet_Model_Test_Render_Case_Abstract[]
     */
    public function getCases() {
        return $this->_cases;
    }

    
        /**
     *
     * @param TBT_Testsweet_Model_Test_Render_Case_Abstract[] $cases
     * @return TBT_Testsweet_Model_Test_Collection_Abstract
     */
    public function setCases($cases) {
        $this->_cases = $cases;
        return $this;
    }    

    /**
     *
     * @param TBT_Testsweet_Model_Test_Render_Suite_Abstract $render
     * @return TBT_Testsweet_Model_Test_Collection_Abstract 
     */
    public function setRender($render) {
        $this->_render = $render;
        return $this;
    }

    /**
     *
     * @return TBT_Testsweet_Model_Test_Render_Suite_Abstract 
     */
    public function getRender() {
        return $this->_render;
    }

}