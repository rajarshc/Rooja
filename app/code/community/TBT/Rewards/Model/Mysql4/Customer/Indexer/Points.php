<?php

class TBT_Rewards_Model_Mysql4_Customer_Indexer_Points extends Mage_Index_Model_Mysql4_Abstract {
	
	/**
	 * Class constructor
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	public function _construct() {
		$this->_init ( 'rewards/customer_indexer_points', 'customer_id' );
		$this->useIdxTable ( true );
	}
	
	/**
	 * Reindex all data
	 * 
	 * @name reindexAll
	 * @see Mage_Index_Model_Mysql4_Abstract::reindexAll()
	 * @return TBT_Rewards_Model_Mysql4_Customer_Indexer_Points
	 */
	public function reindexAll() {
		$this->_createTable ();
		$pointsBalances = $this->_getCustomerBalance ();
		if (count ( $pointsBalances )) {
			$this->_getWriteAdapter ()->insertMultiple ( $this->getIdxTable (), $pointsBalances );
		}
		return $this;
	}
	
	/**
	 * Reindex one customer balance
	 * 
	 * @name reindexUpdate
	 * @return TBT_Rewards_Model_Mysql4_Customer_Indexer_Points
	 */
	public function reindexUpdate($customerId) {
		$this->_getWriteAdapter ()->insertOnDuplicate ( $this->getIdxTable (), $this->_getCustomerBalance ( $customerId ) );
		return $this;
	}
	
	/**
	 * Reindex one customer balance
	 * 
	 * @name reindexUpdate
	 * @return TBT_Rewards_Model_Mysql4_Customer_Indexer_Points
	 */
	public function reindexDelete($customerId) {
		$adapter = $this->_getWriteAdapter ();
		$where = $adapter->quoteInto ( "{$this->getIdFieldName()} = ?", $customerId );
		$select = $adapter->delete ( $this->getIdxTable (), $where );
		return $this;
	}
	
	/**
	 * Returns the index table name
	 * 
	 * @see Mage_Index_Model_Mysql4_Abstract::getIdxTable()
	 * @param mixed $table
	 * @return mixed
	 */
	public function getIdxTable($table = null) {
		return $this->getTable ( 'rewards/customer_indexer_points' );
	}
	
	/**
	 * Returns one customer balance
	 * 
	 * @access protected
	 * @param int $customer_id if null, retrieve all the customer balances
	 * @return array
	 */
	protected function _getCustomerBalance($customer_id = null) {
		$customerModel = Mage::getModel ( 'rewards/customer' );
		
		$customers = $customerModel->getCollection ();
		
		// If a customer id was specified, filter out list by that customer id.
		if(!empty($customer_id) ) {
            $customers->addFieldToFilter('entity_id', $customer_id);
        }
		
		$customerIdxData = array ();
		foreach ( $customers as $customer ) {
			$customer = Mage::getModel ( 'rewards/customer' )->load ( $customer->getId () );
			
			// If the customer id is not there it means that the customer is not complete and should not have a points balance
			if(!$customer->getId()) {
			    continue;
			}
			
			$customerIdxData [] = array ('customer_id' => $customer->getId (), 
				'customer_points_usable' => array_sum ( $customer->getRealUsablePoints () ) 
			);
		}
		
		return $customerIdxData;
	}
	
	/**
	 * Creates the table for indexing
	 * 
	 * @access private
	 * @return TBT_Rewards_Model_Mysql4_Customer_Indexer_Points
	 */
	private function _createTable() {
		// Create the table if not exists
		$createTableSql = "CREATE TABLE IF NOT EXISTS `{$this->getIdxTable()}` (
			`customer_id` INT( 11 ) NOT NULL ,
			`customer_points_usable` INT( 11 ) NOT NULL ,
			PRIMARY KEY (  `customer_id` )
			) ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
		$this->_getWriteAdapter ()->query ( $createTableSql );
		// Delete all records
		$this->_getWriteAdapter ()->delete ( $this->getIdxTable () );
		return $this;
	}

}