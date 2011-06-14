<?php

class TBT_Testsweet_Model_Test_Suite_Php_Core extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Check PHP core');
    }

    public function getDescription() {
        return $this->__('Check PHP is a supported version and settings will not cause issues');
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
            $this->addFail($this->__("Safe mode is on", "When safe mode is on many users have reported file permission errors"));
        }

        // 268435456 bytes
        // offen this value is in the form ###MB ###mb Mb Gb GB not in byte
        // since php will just use the numbers and ignore the class when comparing a string to number
        // I will assume the value is in MB
        // PHP often defaults to 64MB so this should be a correct test in > 90% of cases
        if (ini_get('memory_limit') >= 256) {
            $this->addPass($this->__("Memory limit looks to be >= 256MB, PHP reports: %s", ini_get('memory_limit')));
        } else {
            $this->addWarning($this->__("Memory limit might be less then 256MB. PHP reports: %s", ini_get('memory_limit')), $this->__("When the memory limit is to low the PHP process can crash and prevent pages from loading"));
        }

        // time is in seconds
        if (ini_get('max_execution_time') >= 3600) {
            $this->addPass($this->__("Max execution time is >= 3600 seconds (1hour) PHP reports: %s seconds", ini_get('max_execution_time')));
        } else {
            $this->addFail($this->__("Max execution time is less then 3600 seconds (1hour) PHP reports: %s seconds", ini_get('max_execution_time')), $this->__("Some tasks might require more time to complete and will not finish.  This is offen seen as a timeout message"));
        }
    }

}
