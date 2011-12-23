<?php

abstract class TBT_Testsweet_Model_Test_Render_Suite_Abstract extends TBT_Testsweet_Model_Test_Render_Abstract {

    /* @var TBT_Testsweet_Model_Test_Suite_Abstract */
    protected $_suite = null;
    /* @var TBT_Testsweet_Model_Test_Render_Report_Abstract */
    protected $_report_render = null;
    

    /**
     *
     * @param TBT_Testsweet_Model_Test_Suite_Abstract $suite
     * @return TBT_Testsweet_Model_Test_Render_Suite_Abstract 
     */
    public function setSuite($suite) {
        $this->_suite = $suite;
        return $this;
    }

    /**
     *
     * @return TBT_Testsweet_Model_Test_Suite_Abstract 
     */
    public function getSuite() {
        return $this->_suite;
    }
    
    /**
     *
     * @param TBT_Testsweet_Model_Test_Render_Report_Abstract $report_render
     * @return TBT_Testsweet_Model_Test_Render_Report_Abstract 
     */
    public function setReportRender($report_render) {
        $this->_report_render = $report_render;
        return $this;
    }
    
    /**
     *
     * @return TBT_Testsweet_Model_Test_Render_Report_Abstract 
     */
    public function getReportRender() {
        return $this->_report_render;
    }
    

}