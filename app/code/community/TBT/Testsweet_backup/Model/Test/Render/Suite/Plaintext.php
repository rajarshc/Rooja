<?php

class TBT_Testsweet_Model_Test_Render_Suite_Plaintext extends TBT_Testsweet_Model_Test_Render_Suite_Abstract {

    public function __construct() {
        $render = new TBT_Testsweet_Model_Test_Render_Report_Plaintext();
        $this->setReportRender($render);
    }

    public function render() {
        $suite = $this->getSuite();
        $suite->getSummary();

        if (!$this->isFiltered($suite->getStatus())) {

            echo "\n\n{$suite->getSubject()}\n";
            echo "----------------------------------------------------------------\n";
            echo "{$suite->getDescription()}\n";

            foreach ($suite->getSummary() as $report) {
                /* @var $report TBT_Testsweet_Model_Test_Report */
                if (!$this->isFiltered($report->getStatus())) {
                    $this->getReportRender()->setReport($report)->render();
                }
            }

            echo "  |\n";
            echo "[{$suite->getStatusString()}]\n";
            if ($suite->getException())
                echo "== {$this->__("Error")} ==\n{$suite->getException()->getMessage()}";
        }
    }

}