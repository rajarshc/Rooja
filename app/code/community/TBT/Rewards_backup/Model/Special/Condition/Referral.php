<?php

class TBT_Rewards_Model_Special_Action_Referral extends TBT_Rewards_Model_Special_Action_Abstract {
	
	public function _construct() {
		$this->setCaption ( "Customer Referral" );
		$this->setDescription ( "Customer will get points for every purchase made by a referred customer." );
		$this->setCode ( "referral" );
		return parent::_construct ();
	}
	
	public function givePoints(&$customer) {
	
	}
	
	public function revokePoints(&$customer) {
	
	}
	
	public function holdPoints(&$customer) {
	
	}
	
	public function cancelPoints(&$customer) {
	
	}
	
	public function approvePoints(&$customer) {
	
	}

}