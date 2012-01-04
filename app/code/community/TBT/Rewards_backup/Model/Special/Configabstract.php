<?php

abstract class TBT_Rewards_Model_Special_Configabstract extends Varien_Object {
	
	public function visitAdminActions(&$fieldset) {
		return $this;
	}
	
	public function visitAdminConditions(&$fieldset) {
		return $this;
	}
	
	public abstract function getNewCustomerConditions();
	
	public abstract function getNewActions();
	
	public function getAdminFormScripts() {
		return array ();
	}
	
	public function getAdminFormInitScripts() {
		return array ();
	}

}
