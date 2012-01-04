<?php

class TBT_Rewards_Model_Mysql4_Customer_Indexer_Points_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	public function _construct() {
		$this->_init ( 'rewards/customer_indexer_points' );
	}

}