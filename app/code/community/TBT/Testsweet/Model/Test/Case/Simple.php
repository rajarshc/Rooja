<?php

class TBT_Testsweet_Model_Test_Case_Simple extends TBT_Testsweet_Model_Test_Case_Abstract {
    
    function __construct($status=0, $subject = null, $description = null, $exception = null) {
        $this->setCase($status, $subject, $description, $exception);
    }
}