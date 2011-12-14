<?php

class TBT_Rewards_Model_Review_Edit_Tabs extends TBT_Rewards_Model_Transfer_Edit_Tab_Observer {
	
	protected function _addTab($transfer, $block) {
		
		$types = array (
		    TBT_Rewards_Model_Review_Reference::REFERENCE_TYPE_ID, 
		    TBT_Rewards_Model_Transfer_Reference::REFERENCE_RATING
		);
		
		$transfer_references = $transfer->getAllReferences();
		$transfer_references->addFieldToFilter('reference_type', array('IN' => $types));
		$transfer_references->load();
		
		// TODO: Make this so that it can recognize multiple references.
		if ( $transfer_references->count() >= 1 ) {
			$block->addTab ( 'reviews_section', array (
				'label' => Mage::helper ( 'rewards' )->__ ( 'Reference Review/Rating' ), 
				'title' => Mage::helper ( 'rewards' )->__ ( 'Reference Review/Rating' ), 
				'content' => $block->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_reviews' )->toHtml () 
		    ) );
		}
		return $this;
	}
	
}