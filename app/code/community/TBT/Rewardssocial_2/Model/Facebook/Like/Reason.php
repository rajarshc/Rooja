<?php

class TBT_Rewardssocial_Model_Facebook_Like_Reason extends TBT_Rewards_Model_Transfer_Reason_Abstract {
    
	const REASON_TYPE_ID = 30;
	
	/**
	 * passes the $available_reasons array of existing available reasons so that other modules
	 * can remove reasons as well.  This is bad however because the dependencies 
	 * are left unmanaged.  The module creator should keep this in mind when developing add-on extensions.
	 */
	public function getAvailReasons($current_reason, &$availR) {
		return $availR;
	}
	
	public function getOtherReasons() {
		return array ();
	}
	
	public function getManualReasons() {
		return array ();
	}
	
	public function getDistributionReasons() {
        return array(self::REASON_TYPE_ID => Mage::helper('rewardssocial')->__('Facebook Like'));
	}
	public function getRedemptionReasons() {
		return array ();
	}
	public function getAllReasons() {
        return array(self::REASON_TYPE_ID => Mage::helper('rewardssocial')->__('Facebook Like'));
	}

}