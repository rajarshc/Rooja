<?php

class TBT_Rewards_Model_Mysql4_Referral_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	public function _construct() {
		$this->_init ( 'rewards/referral' );
	}

}