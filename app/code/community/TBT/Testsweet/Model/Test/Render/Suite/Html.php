<?php

class TBT_Testsweet_Model_Test_Render_Suite_Html extends TBT_Testsweet_Model_Test_Render_Suite_Abstract {

    public function __construct() {
        $render = new TBT_Testsweet_Model_Test_Render_Report_Html();
        $this->setReportRender($render);
    }

    public function render() {
        $suite = $this->getSuite();
        $suite->getSummary();

        if (!$this->isFiltered($suite->getStatus())) {

            echo "<h2>{$suite->getSubject()}</h2>";
            echo "<p>{$suite->getDescription()}</p>";

            echo "<ol>";
            foreach ($suite->getSummary() as $report) {
                /* @var $report TBT_Testsweet_Model_Test_Report */
                if (!$this->isFiltered($report->getStatus())) {
                    $this->getReportRender()->setReport($report)->render();
                }
            }
            echo "</ol>";

            echo "<li><b>{$suite->getStatusString()}</b></li>";
            if ($suite->getException())
                echo "<b>{$this->__("Error")}</b><br/><pre>{$suite->getException()->getMessage()}</pre>";
        }
    }

}