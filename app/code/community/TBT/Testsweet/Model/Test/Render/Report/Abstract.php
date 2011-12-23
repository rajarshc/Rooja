<?php

abstract class TBT_Testsweet_Model_Test_Render_Report_Abstract extends TBT_Testsweet_Model_Test_Render_Abstract {
    /* @var TBT_Testsweet_Model_Test_Report_Abstract */

    protected $_report = null;

    /**
     * @param TBT_Testsweet_Model_Test_Report_Abstract $report 
     * @return TBT_Testsweet_Model_Test_Render_Report_Abstract 
     */
    public function setReport($report) {
        $this->_report = $report;
        return $this;
    }

    /**
     *
     * @return TBT_Testsweet_Model_Test_Report_Abstract 
     */
    public function getReport() {
        return $this->_report;
    }

}