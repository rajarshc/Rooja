<?php
class TBT_Rewards_Model_Tag_Tag extends TBT_Rewards_Model_Special_Configabstract {
	
	const ACTION_CODE = 'customer_tag';
	
	public function _construct() {
		$this->setCaption ( "Tag writing" );
		$this->setDescription ( "Customer will get points when they write a tag." );
		$this->setCode ( "customer_tag" );
		return parent::_construct ();
	}
	
	public function getNewCustomerConditions() {
		return array (self::ACTION_CODE => Mage::helper ( 'rewards' )->__ ( 'Writes a tag' ) );
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