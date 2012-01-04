<?php

class TBT_Rewards_Model_Tag_Edit_Tabs extends TBT_Rewards_Model_Transfer_Edit_Tab_Observer {
	
	protected function _addTab($transfer, $block) {
	    
		$referenceData = $transfer->getReferenceData ();
		
		if ($referenceData ['reference_type'] == TBT_Rewards_Model_Tag_Reference::REFERENCE_TYPE_ID) {
			$block->addTab ( 'tags_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Reference Product Tag' ), 
				'title' => Mage::helper ( 'rewards' )->__ ( 'Reference Product Tag' ), 
				'content' => $block->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_tags' )->toHtml () ) );
		}
		
		return $this;
	}

}