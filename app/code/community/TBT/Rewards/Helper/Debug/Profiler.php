<?php
/**
 * Pretty much this is a wrapper for Varien_Profiler
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Debug_Profiler extends TBT_Rewards_Helper_Debug {
    
    /**
     * 
     * @param unknown_type $timerName
     */
	public function start($timerName) {
	    Varien_Profiler::start($timerName);
	    return $this;
	}
	
	/**
	 * 
	 * @param unknown_type $timerName
	 */
	public function stop($timerName) {
	    Varien_Profiler::stop($timerName);
	    
	    if($this->_profillerLoggingEnabled()) {
	        $this->notice("Profiler: " . $timerName . " total time: ". $this->fetch($timerName));
	    }
	    
	    return $this;
	}
	
	/**
	 * 
	 * @param string $timerName
	 * @param string $key			key from the timer entry.  default is 'sum' which will return an integer
	 * @return mixed depending on $key
	 */
	public function fetch($timerName, $key='sum') {
	    return Varien_Profiler::fetch($timerName, $key);
	}
	
	protected function _profillerLoggingEnabled() {
	    return true;
	}
}
