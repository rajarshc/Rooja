<?php

class TBT_Testsweet_Model_Test_Render_Report_Plaintext extends TBT_Testsweet_Model_Test_Render_Report_Abstract {

    public function render() {
        $report = $this->getReport();
        echo "  |- {$report->getStatusString()} -- {$report->getSubject()}\n";
        if (sizeof($report->getDescription()) > 0)
            echo "  |   |-{$report->getDescription()}\n";
    }

}