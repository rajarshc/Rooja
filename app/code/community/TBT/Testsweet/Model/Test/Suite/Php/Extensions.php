<?php

class TBT_Testsweet_Model_Test_Suite_Php_Extensions extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('PHP - Extensions');
    }

    public function getDescription() {
        return $this->__('Check PHP has required extension.');
    }
    

    /**
     * Generate summary of test results
     * 
     * (overrides parent method)
     */
    protected function generateSummary() {
        $this->_checkCurl();        
        $this->_checkInstalledExtensions();
        $this->_checkApc();
        $this->_checkEaccelerator();
    }
    
    
    /**
     * Checks to see if cURL is installed and functional on the server. If it is not, this will error out.
     * @author nelkaake 05/02/2011
     */
    protected function _checkCurl() {
    	if  (in_array  ('curl', get_loaded_extensions())) {
            if(function_exists("curl_exec") && function_exists("curl_multi_exec")) {
                $this->addPass($this->__("cURL functions are enabled and cURL is installed."));
            } else {
                $msg = $this->__("cURL is installed on the server, however the curl_exec function is blocked or not available.");
                $desc = $this->__(
                "   Magento requires cURL to be installed and fully functional on the server/account.  
                    In order to eliminate this error message you need to do ONE of the following things: 
                    1. Remove the curl_exec string from the disable_functions at php.ini* file 
                    OR 2. Ask your hosting provider to remove the string above if you don't have an access to the php.ini* file 
                    OR 3. Change hosting provider which allows the running of the curl_exec function. 
				");
                
                $this->addFail($msg, $desc);
            }
    	} else {
            $msg = $this->__("cURL is NOT installed on the server.");
            $desc = $this->__(
            "	Magento requires cURL to be installed and fully functional on the server/account.  
                In order to eliminate this error message you need to do ONE of the following things: 
                1. Ask your host to enable cURL on your server. 
                OR 2. Uncomment the 'extension=curl.so' or 'extension=curl.dll' line in your php.ini file 
                Remember to restart your server after making the changes. 
			");
            
            $this->addFail($msg, $desc);
    	}
    	return $this;
    }
    
    /**
     * Checks basic installed extensions as required by the Magento-check script
     */
    protected function _checkInstalledExtensions() {
        $required_extension = array(
            'curl',
            'dom',
            'gd',
            'hash',
            'iconv',
            'mcrypt',
            'pcre',
            'pdo',
            'pdo_mysql',
            'simplexml'
        );
        foreach ($required_extension as $extension) {
            if (extension_loaded($extension)) {
                $this->addPass($this->__("PHP has required extension %s", $extension));
            } else {
                $this->addFail($this->__("Magento requires the PHP extension %s", $extension));
            }
        }
        
        return $this;
    }
    
    /**
     * If APC is intalled we need to check more settings to make sure that there are no problems with configuration settings
     */
    protected function _checkApc() {
        if (extension_loaded('apc')) {
            $this->addWarning($this->__("PHP has extension APC. This extension can cause cache issues when misconfigured."));
            if (ini_get('apc.stat') != '1') {
                $this->addFail($this->__("APC.STAT is set: %s", ini_get('apc.stat')), $this->__("When APC stat is set off, the system will not recheck files for changes and will continue to use cached values"));
            }
        }
        
        return $this;
    }
    
    protected function _checkEaccelerator() {
        if (extension_loaded('eaccelerator')) {
            $this->addWarning($this->__("PHP has extension eAccelerator. This extension can cause cache issues when misconfigured."));
            if (ini_get('eaccelerator.check_mtime') != '1') {
                $this->addFail($this->__("eAccelerator check_mtime is set: %s", ini_get('eaccelerator.check_mtime')), $this->__("When eAccelerator mtime is set off, the system will not check files for updates and will continue using outdated cached files."));
            }
        }
        return $this;
    }
}
