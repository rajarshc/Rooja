<?php

class TBT_Rewards_Model_Review_Edit_Tabs extends Varien_Object {
	
	public function addTab($observer) {
		$block = $observer->getEvent ()->getBlock ();
		$transfer = Mage::registry ( 'transfer_data' );
		if (! $transfer) {
			return;
		}
		$types = array (TBT_Rewards_Model_Review_Reference::REFERENCE_TYPE_ID, TBT_Rewards_Model_Transfer_Reference::REFERENCE_RATING );
		$referenceData = $transfer->getReferenceData ();
		if ($block instanceof TBT_Rewards_Block_Manage_Transfer_Edit_Tabs && in_array ( $referenceData ['reference_type'], $types )) {
			$block->addTab ( 'reviews_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Reference Review/Rating' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Reference Review/Rating' ), 'content' => $block->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_reviews' )->toHtml () ) );
		}
		return $this;
	}

}