<?php

abstract class TBT_Testsweet_Model_Test_Case_Abstract extends TBT_Testsweet_Model_Test_Report_Abstract {
    
    public function setCase($status=0, $subject = null, $description = null, $exception = null) {       
        $this->setStatus($status);
        $this->setSubject($subject);
        $this->setDescription($description);
        $this->setException($exception);
    }    
    
    protected $_subject = '';
    protected $_description = '';

    public function getSubject() {
        return $this->_subject;
    }

    public function setSubject($subject) {
        $this->_subject = $subject;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function setDescription($description) {
        $this->_description = $description;
    }

}