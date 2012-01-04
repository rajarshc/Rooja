<?php

/**
 * @nelkaake 22/01/2010 3:54:41 AM : points expiry
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

require_once("AbstractController.php");
class TBT_Rewards_Debug_LicenseController extends TBT_Rewards_Debug_AbstractController
{
    
    public function indexAction()
    {
        echo "<h2>This tests installation things. </h2>";
        echo "<a href='". Mage::getUrl('rewards/debug_license/validate') ."'>Check License</a> - Checks license. <BR />";
      
        exit;
    }

    protected function _isCurlInstalled() {
    	if  (in_array  ('curl', get_loaded_extensions())) {
            if(function_exists("curl_exec")) {
    		  return true;
            }
    	}
    	return false;
    }


    protected function validateAction() {
        try {
            if(!$this->_isCurlInstalled()) {
                throw new Exception("Curl is not enabled on this server!");
            }
            $license = Mage::helper('rewards/loyalty_checker')->getLicenseKey();
            echo "License key being used is: " . $license . "<br />\n"; flush();
            if(empty($license)) {
                throw new Exception ("License could not be loaded for some reason.  You should enter it in the module configuration or in your Commercial Extension Manager console.");
            }
            
            echo "Checking with remote server..."; flush();
            $response = Mage::helper('rewards/loyalty_checker')->fetchValidationResponse($license);
            echo "Response was: ". $response . "<br />\n"; flush();
            
            echo "Done.";
        } catch(Exception $e) {
            die("<BR />\n ERROR: " . $e->getMessage());
        
        }
    	return $this;
    	
    }

    protected function clearCacheAction() {
        
        Mage::app()->getCacheInstance()->flush();
        echo "Cache has been cleared.";
    	return $this;
    	
    }
    
    
}