<?php

class TBT_Rewards_Model_Redemption_Instance_Collection extends Varien_Data_Collection {
	
	protected $item = null;
	
	//	const POINTS_CURRENCY_ID = 'points_currency_id';
	//	const POINTS_AMT = 'points_amt';
	//	const POINTS_EFFECT = 'effect';
	//	const POINTS_RULE_ID = 'rule_id';
	//	const POINTS_APPLICABLE_QTY = 'applicable_qty';
	//  const POINTS_USES = 'uses';
	//  const POINTS_INST_ID = 'redemption_inst_id';
	

	public function setQuoteItem($item) {
		$this->item = $item;
		return $this;
	}
	
	public function getQuoteItem() {
		return $this->item;
	}
	
	public function hasQuoteItem() {
		return $this->item != null;
	}
	
	public function loadData($printQuery = false, $logQuery = false) {
		if ($this->isLoaded ()) {
			return $this;
		}
		
		if (! $this->getQuoteItem ())
			return $this;
		
		$this->_renderFilters ()->_renderOrders ()->_renderLimit ();
		
		$redeemed_points = Mage::helper ( 'rewards' )->unhashIt ( $this->item->getRedeemedPointsHash () );
		$this->clear ();
		$num_items = 0;
		foreach ( $redeemed_points as $key => &$redemption_instance ) {
			$ri = Mage::getModel ( 'rewards/redemption_instance' )->setData ( ( array ) $redemption_instance )->setItem ( $this->getQuoteItem () );
			$this->addItem ( $ri );
			$num_items ++;
		}
		
		$this->_totalRecords = $num_items;
		$this->_setIsLoaded ();
		
		return $this;
	}
	
	/**
	 * Adding item to item array
	 *
	 * @param   Varien_Object $item
	 * @return  Varien_Data_Collection
	 */
	public function addItem(Varien_Object $item) {
		$this->_totalRecords ++;
		$this->_setIsLoaded ();
		return parent::addItem ( $item );
	}
	
	public function getTotalDiscounts() {
		if (! $this->getQuoteItem () || $this->getSize () == 0)
			return 0;
		
		// Loop through and apply all our rules.
		$discount = 0;
		foreach ( $this->getItems () as $ri ) {
			$discount += $ri->getItemDiscount ();
		}
		
		return $discount;
	}
	
	public function toHash($calculate_effect = true) {
		$data = $this->getStorableData ( $calculate_effect );
		return $this->_help ()->hashIt ( $data );
	}
	
	public function getStorableData($calculate_effect = true) {
		$data = array ();
		foreach ( $this->getItems () as $ri ) {
			if ($calculate_effect) {
				$ri->calcEffect ();
			}
			$data [] = $ri->getStorableData ();
		}
		return $data;
	}
	
	public function saveToItem($calculate_effect = false) {
		if (! $this->hasQuoteItem ())
			return $this;
		$hash = $this->toHash ( $calculate_effect );
		$this->getQuoteItem ()->setRedeemedPointsHash ( $hash );
		return $this;
	}
	
	protected function _help() {
		return Mage::helper ( 'rewards' );
	}

}
