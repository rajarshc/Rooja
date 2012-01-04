<?php

class TBT_Rewards_Model_Mysql4_Transfer_Reference_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
    protected $_hasJoinedTransfers = false;
	
	public function _construct() {
		$this->_init ( 'rewards/transfer_reference' );
	}
	
    public function _initSelect () {
        return parent::_initSelect();
    } 
    
    public function filterByTransfer($transfer_id) {
        return $this->addFilter('rewards_transfer_id', $transfer_id);
    }
    
	/**
	 * Add points transfer information to the reference list
	 */
    public function addTransferInfo() {
        if($this->_hasJoinedTransfers) {
            return $this;
        }
        
        $transfers_table = $this->getResource()->getTable('rewards/transfer');
        
		$this->getSelect ()->joinLeft (
		    array ('transfers' => $transfers_table ), 
		    'main_table.rewards_transfer_id = transfers.rewards_transfer_id'
		);
        
		$this->_hasJoinedTransfers = true;
		
        return $this;
    }

}