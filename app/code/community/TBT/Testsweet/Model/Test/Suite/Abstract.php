<?php

abstract class TBT_Testsweet_Model_Test_Suite_Abstract extends TBT_Testsweet_Model_Test_Report_Abstract {

    /**
     * This will return the version of the testsweet that the test case requires
     * 
     * This will aide in compatablity if there is changes to the structure of tests
     * the min requirement should be ='1.0.0.0'
     * 
     * @return string
     */
    abstract public function getRequireTestsweetVersion();

    /**
     * Generate Summary
     * Inside this function a summary of the test(s) compleated are compiled. 
     * 
     * Example code to be found in this function:
     * 
     * if ("test passed") 
     *    $this->addPass("the test passed", "this is the discription of the test");
     * else
     *    $this->addFail("the test failed", "somehow a php string was evaled as false");
     * 
     */
    abstract protected function generateSummary();

    protected $_cases = array();

    /**
     * generate report(s) then return them once completed
     * @return TBT_Testsweet_Model_Test_Report[]
     */
    final public function getSummary() {
        try {
            if (empty($this->_cases))
                $this->generateSummary();
        } catch (Exception $ex) {
            $this->addFail($this->__("Exception hit while building report"), $ex->getMessage(), $ex);
        }
        return $this->_cases; //clone if anal
    }

    final protected function addPass($subject = null, $description = null, $exception = null) {
        $status = TBT_Testsweet_Model_Test_Report_Abstract::STATUS_PASS;
        $this->addCase(new TBT_Testsweet_Model_Test_Case_Simple($status, $subject, $description, $exception));
    }

    final protected function addFail($subject = null, $description = null, $exception = null) {
        $status = TBT_Testsweet_Model_Test_Report_Abstract::STATUS_FAIL;
        $this->addCase(new TBT_Testsweet_Model_Test_Case_Simple($status, $subject, $description, $exception));
    }

    final protected function addWarning($subject = null, $description = null, $exception = null) {
        $status = TBT_Testsweet_Model_Test_Report_Abstract::STATUS_WARNING;
        $this->addCase(new TBT_Testsweet_Model_Test_Case_Simple($status, $subject, $description, $exception));
    }

    final protected function addNotice($subject = null, $description = null, $exception = null) {
        $status = TBT_Testsweet_Model_Test_Report_Abstract::STATUS_NOTICE;
        $this->addCase(new TBT_Testsweet_Model_Test_Case_Simple($status, $subject, $description, $exception));
    }

    /**
     * add a report case to the bundel that is returned by getSummary()
     * @param TBT_Testsweet_Model_Test_Report_Abstract $case 
     */
    final protected function addCase(TBT_Testsweet_Model_Test_Report_Abstract $case) {
        if ($case->getException() != null)
            $this->_exception = $case->getException();

        // keep track of the largest 'error' status
        if ($case->getStatus() > $this->getStatus())
            $this->_status = $case->getStatus();

        $this->_cases[] = $case;
    }

}

