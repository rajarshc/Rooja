<?php
class TBT_Rewards_Model_Tag_Behavior extends TBT_Rewards_Model_Special_Configabstract {
	
	const ACTION_CODE = 'customer_tag';
	
	public function _construct() {
		$this->setCaption ( "Tagging A Product" );
		$this->setDescription ( "Customer will get points when they tag a product." );
		$this->setCode ( "customer_tag" );
		return parent::_construct ();
	}
	
	public function getNewCustomerConditions() {
		return array (self::ACTION_CODE => Mage::helper ( 'rewards' )->__ ( 'Tags a product' ) );
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