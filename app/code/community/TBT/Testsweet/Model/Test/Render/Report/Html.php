<?php

class TBT_Testsweet_Model_Test_Render_Report_Html extends TBT_Testsweet_Model_Test_Render_Report_Abstract {

    public function render() {
        $report = $this->getReport();
        echo "<ol>";
        echo "<li>";
        echo "<b class='{$report->getStatusString()}'>{$report->getStatusString()}</b>";
        echo "<pre>{$report->getSubject()}</pre>";
        echo "<pre>{$report->getDescription()}</pre>";
        echo "</li>";
        echo "</ol>";
    }

}