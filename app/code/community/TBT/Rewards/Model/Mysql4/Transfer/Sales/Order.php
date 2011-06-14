<?php

class TBT_Rewards_Model_Mysql4_Transfer_Reference extends Mage_Core_Model_Mysql4_Abstract {
	
	public function _construct() {
		// Note that the blog_id refers to the key field in your database table.
		$this->_init ( 'rewards/transfer_reference', 'rewards_transfer_reference_id' );
	}

}