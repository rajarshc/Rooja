<?php

abstract class TBT_Rewards_Model_Transfer_Edit_Tab_Observer extends Varien_Object {
    
	protected abstract function _addTab($transfer, $block);
	
	public function addTab($observer) {
		$block = $observer->getEvent ()->getBlock ();
		$transfer = Mage::registry ( 'transfer_data' );
		if (! $transfer) {
			return;
		}
		
		if ( !( $block instanceof TBT_Rewards_Block_Manage_Transfer_Edit_Tabs ) ) {
		    return $this;
		}
		
		$this->setBlock($block)->setTransfer($block);
		
		$this->_addTab($transfer, $block);
		
		return $this;
	}
	
}