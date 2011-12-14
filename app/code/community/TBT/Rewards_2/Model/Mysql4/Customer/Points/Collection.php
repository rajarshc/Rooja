<?php

class TBT_Rewards_Model_Mysql4_Customer_Points_Collection extends TBT_Rewards_Model_Mysql4_Transfer_Collection {
	
	public function _beforeLoad() {
		$this->selectCurrency ();
		
		$this->getSelect ()->group ( 'main_table.customer_id' );
		$this->getSelect ()->group ( 'main_table.currency_id' );
		$this->getSelect ()->from ( null, array ("points_count" => "SUM(main_table.quantity)" ) );
		$this->getSelect ()->from ( null, array ("points" => "CONCAT(SUM(main_table.quantity), ' ', currency_table.caption" ) );
		$this->getSelect ()->from ( null, array ("last_changed_ts" => "MAX(main_table.creation_ts)" ) );
		return parent::_beforeLoad ();
	}

}