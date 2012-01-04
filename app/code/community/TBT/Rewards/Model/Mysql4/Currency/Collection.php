<?php

class TBT_Rewards_Model_Mysql4_Currency_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	public function _construct() {
		$this->_init ( 'rewards/currency' );
	}
	
	/**
	 * Add Filter by store
	 *
	 * @param int|Mage_Core_Model_Store $store
	 * @return Mage_Cms_Model_Mysql4_Page_Collection
	 */
	public function addStoreFilter($store) {
		if (! Mage::app ()->isSingleStoreMode ()) {
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array ($store->getId () );
			}
			$this->getSelect ()->join ( array ('store_currency_table' => $this->getTable ( 'store_currency' ) ), 'main_table.rewards_currency_id = store_currency_table.currency_id', array () )->where ( 'store_currency_table.store_id in (?)', array (0, $store ) );
			
			return $this;
		}
		return $this;
	}

}