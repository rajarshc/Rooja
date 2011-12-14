<?php
class TBT_Rewards_Model_Review_Review extends TBT_Rewards_Model_Special_Configabstract {
	
	const ACTION_CODE = 'customer_writes_review';
	
	public function _construct() {
		$this->setCaption ( "Review writing" );
		$this->setDescription ( "Customer will get points when they write a review." );
		$this->setCode ( "customer_writes_review" );
		return parent::_construct ();
	}
	
	public function getNewCustomerConditions() {
		return array (self::ACTION_CODE => Mage::helper ( 'rewards' )->__ ( 'Writes a review' ) );
	}
	
	public function visitAdminConditions(&$fieldset) {
		return $this;
	}
	
	public function visitAdminActions(&$fieldset) {
		return $this;
	}
	
	public function getNewActions() {
		return array ();
	}
	
	public function getAdminFormScripts() {
		return array ();
	}
	public function getAdminFormInitScripts() {
		return array ();
	}
}