<?php

class TBT_Rewards_Model_Tag_Edit_Tabs extends Varien_Object {
	
	public function addTab($observer) {
		$block = $observer->getEvent ()->getBlock ();
		$transfer = Mage::registry ( 'transfer_data' );
		if (! $transfer) {
			return;
		}
		$referenceData = $transfer->getReferenceData ();
		if ($block instanceof TBT_Rewards_Block_Manage_Transfer_Edit_Tabs && $referenceData ['reference_type'] == TBT_Rewards_Model_Tag_Reference::REFERENCE_TYPE_ID) {
			$block->addTab ( 'tags_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Reference Product Tag' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Reference Product Tag' ), 'content' => $block->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_tags' )->toHtml () ) );
		}
		return $this;
	}

}