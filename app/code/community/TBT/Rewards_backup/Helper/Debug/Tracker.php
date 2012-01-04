<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Helper for Debugging
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Debug_Tracker extends TBT_Rewards_Helper_Debug
{

    protected $_trackedEvents = array();
    protected $_trackerEnabled = false;
    protected $_isLoggingEnabled = true;
    protected $_isPrintingEnabled = true;
    
    public function trackEvent($msg, $data=null) {
    	if(!$this->_trackerEnabled) return $this;
    	
    	if(is_object($data)) {
    		$data = clone $data;
    	}
    	               
        $entry = array ('msg' => $msg, 'trace' => $this->getSimpleBacktrace ( 1 ), 'data' => $data, 'time' => time());
		
        $this->_trackedEvents[] = $entry;
		
		if($this->_getIsLoggingEnabled()) {
			Mage::helper('rewards')->log("===================== TRACKER: {$entry['msg']} @ {$entry['time']} ========================");
			Mage::helper('rewards')->log("		 : Trace (2-up): ". trim($this->getSimpleBacktrace ( 2 ))   );
			Mage::helper('rewards')->log("		 : Data: ". print_r($this->getPrintableData($data), true));
			Mage::helper('rewards')->log("===================== END TRACKER: {$entry['msg']} @ {$entry['time']} ========================\n\n");
		}
		
		return $this;
	}
	
	public function getTrackedEventsAsString($show_trace = true, $show_data = true) {
		$str = "";
		foreach($this->_trackedEvents as $i => $te) {
			$index = $i + 1;
			$str .= "=============================BEGIN TRACE = #{$index}: {$te['msg']} ====================================\n ";
			if($show_trace) $str .= "Trace: {$te['trace']} \n================================================\n " ;
			if($show_data) $str .= "Data: ". print_r($this->getPrintableData($te['data']), true) . " \n===========================================END TRACE #{$index}=========================================\n \n" ;
		}
		return $str;
	}
	
	protected function _getIsLoggingEnabled() {
		return $this->_isLoggingEnabled;
	}
	
	public function isTrackerEnabled() {
        return $this->_trackerEnabled;
    } 
	public function isPrintingEnabled() {
        return $this->_isPrintingEnabled;
    }
}

