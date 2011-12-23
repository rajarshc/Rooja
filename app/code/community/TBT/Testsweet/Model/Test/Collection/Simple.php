<?php

class TBT_Testsweet_Model_Test_Collection_Simple extends TBT_Testsweet_Model_Test_Collection_Abstract {

    public function outputSummary() {
        foreach ($this->getSuites() as $suite) {
            /* @var $suite TBT_Testsweet_Model_Test_Render_Suite_Abstract */
            $this->getRender()->setSuite($suite)->render();
        }
    }

    public function outputSummary_toString() {
        foreach ($this->getSuites() as $suite) {
            /* @var $suite TBT_Testsweet_Model_Test_Render_Suite_Abstract */
            $this->getRender()->setSuite($suite)->render_toString();
        }
    }

}