<?php

/**
 * WARNING: THIS IS NOT A AN ORDER!!!!
 *
 */
class TBT_Rewards_Model_Transfer_Sales_Order extends Mage_Core_Model_Abstract {
	
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'rewards/transfer_sales_order' );
	}
	
	/**
	 * Loads a order model by the given transfer id and order id.
	 *
	 * @param int $transfer_id
	 * @param int $order_id
	 * @return TBT_Rewards_Model_Transfer_Sales_Order
	 */
	public function loadByTransferAndOrder($transfer_id, $order_id) {
		$read = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_read' );
		$select = $read->select ()->from ( $this->_getResource ()->getTable ( 'transfer_sales_order' ) )->where ( 'rewards_transfer_id = ?', $transfer_id )->where ( 'order_id = ?', $order_id );
		
		if ($data = $read->fetchAll ( $select )) {
			$id = $data [0] ['rewards_transfer_sales_order_id'];
		} else {
			$id = null;
		}
		
		$this->load ( $id );
		return $this;
	}
	
	/**
	 * Loads this model with the order that's associated with the 
	 * provided trasnfer id.
	 * @see This should be modified in the future if we ever decide
	 * to add the functionality to relate multiple orders to a single
	 * rewards transfer.
	 *
	 * @param int $transfer_id
	 * @return TBT_Rewards_Model_Transfer_Sales_Order
	 */
	public function loadOrderByTransferId($transfer_id) {
		$read = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_read' );
		$select = $read->select ()->from ( $this->_getResource ()->getTable ( 'transfer_sales_order' ) )->where ( 'rewards_transfer_id = ?', $transfer_id );
		
		if ($data = $read->fetchAll ( $select )) {
			$id = $data [0] ['rewards_transfer_sales_order_id'];
		} else {
			$id = null;
		}
		
		$this->load ( $id );
		return $this;
	}

}