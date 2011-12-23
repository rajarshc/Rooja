<?php

abstract class TBT_Testsweet_Model_Test_Report_Abstract extends TBT_Testsweet_Model_Abstract {

    public static $STATUS_LEVELS = array(0 => 'null', 1 => 'Pass', 2 => 'Note', 4 => 'Warn', 8 => 'Fail');
    const STATUS_PASS = 1;
    const STATUS_NOTICE = 2;
    const STATUS_WARNING = 4;
    const STATUS_FAIL = 8;

    protected $_status = 1;
    protected $_exception = null;
    
    /**
     * @return string
     */
    abstract public function getSubject();

    /**
     * @return string
     */
    abstract public function getDescription();
        
    /**
     * Get status
     * 
     * should be one of:
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_PASS = 1;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_NOTICE = 2;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_WARNING = 4;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_FAIL = 8;
     *
     * @return int
     */
    public function getStatus() {
        return $this->_status;
    }

    /**
     * Set status 
     * 
     * should be one of:
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_PASS = 1;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_NOTICE = 2;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_WARNING = 4;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_FAIL = 8;
     * 
     * @param int TBT_Testsweet_Model_Test_Case_Report_Abstract::STATUS_PASS $status
     */
    public function setStatus($status) {
        if ($status == null)
            $status = TBT_Testsweet_Model_Test_Report_Abstract::STATUS_PASS;
        $this->_status = $status;
    }

    /**
     * Return the status as a string.
     * 
     * @return int 0 => 'Pass', 1 => 'Notice' , 2 => 'Warning' , 4 => 'Fail'
     */
    public function getStatusString() {
        $sl = TBT_Testsweet_Model_Test_Report_Abstract::$STATUS_LEVELS;
        if (!isset($sl[$this->_status]))
            throw new Exception($this->__("Invalid status: %s", $this->_status));
        return $sl[$this->_status];
    }

    /**
     * Return the exception related to the report, this value is often null.
     * 
     * @return Exception exception 
     */
    public function getException() {
        return $this->_exception;
    }

    /**
     * Set an exception that occurred during the creation of the report
     * 
     * @param Exception $exception 
     */
    public function setException($exception) {
        $this->_exception = $exception;
    }

}