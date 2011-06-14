<?php
class TBT_Rewards_Model_Newsletter_Subscription extends TBT_Rewards_Model_Special_Configabstract {
	
	const ACTION_CODE = 'customer_newsletter';
	
	public function _construct() {
		$this->setCaption ( "Newsletter Subscription" );
		$this->setDescription ( "Customer will get points when they sign up to the newsletter." );
		$this->setCode ( "customer_newsletter" );
		return parent::_construct ();
	}
	
	public function getNewCustomerConditions() {
		return array (self::ACTION_CODE => Mage::helper ( 'rewards' )->__ ( 'Signs up for a newsletter' ) );
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