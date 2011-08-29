<?php

class TBT_Testsweet_Model_Test_Suite_Php_Core extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('PHP - Core');
    }

    public function getDescription() {
        return $this->__('Check PHP is a supported version and settings will not cause issues.');
    }

    protected function generateSummary() {

        if (version_compare(phpversion(), '5.2.13', '<') === false) {
            $this->addPass($this->__("PHP version >= 5.2.13, you have: %s", phpversion()));
        } else {
            $this->addFail($this->__("Magento supports PHP 5.2.13 or newer. You have: %s", phpversion()));
        }

        if (!ini_get('safe_mode')) {
            $this->addPass($this->__("Safe mode is off"));
        } else {
            $this->addFail($this->__("Safe mode is on", "When safe mode is on many users have reported file permission errors."));
        }
        
        $mem_in_mb = (int)ini_get('memory_limit');        
        $string = ini_get('memory_limit');

        // if memory_limit is in gigibyte format
        if (strpos(strtoupper(ini_get('memory_limit')), 'G') > 0 ){
            $mem_in_mb = ((int)ini_get('memory_limit')) * 1024;
        }

        if ($mem_in_mb >= 256) {
            $this->addPass($this->__("Memory limit looks to be >= 256MB, PHP reports: %s.", ini_get('memory_limit')));
        } else {
            $this->addWarning($this->__("Memory limit might be less than 256MB. PHP reports: %s.", ini_get('memory_limit')), $this->__("When the memory limit is to low the PHP process can crash and prevent pages from loading."));
        }
        
        // time is in seconds
        if (ini_get('max_execution_time') >= 3600) {
            $this->addPass($this->__("Max execution time is >= 3600 seconds (1 hour) PHP reports: %s seconds.", ini_get('max_execution_time')));
        } elseif (ini_get('max_execution_time') >= 600) {
            $this->addWarning($this->__("Max execution time is less than 3600 seconds (1 hour) PHP reports: %s seconds.", ini_get('max_execution_time')), $this->__("Some tasks might require more time to complete and will not finish.  This is offen seen as a timeout message."));
        } else {
            $this->addFail($this->__("Max execution time is less than 600 seconds (10 min) PHP reports: %s seconds.", ini_get('max_execution_time')), $this->__("Some tasks can require more time to complete and will not finish.  This is offen seen as a timeout message."));
        }
    }

}
