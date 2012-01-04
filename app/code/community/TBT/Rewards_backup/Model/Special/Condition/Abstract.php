<?php
abstract class TBT_Rewards_Model_Special_Condition_Abstract extends Varien_Object {
	protected $needs_approval = false;
	
	public function _construct() {
		$this->setCaption ( "unnamed special points rule action" );
		$this->setDescription ( "unnamed special points rule action..." );
		return parent::_construct ();
	}
	
	public abstract function givePoints(&$customer);
	public abstract function revokePoints(&$customer);
	public abstract function holdPoints(&$customer);
	public abstract function cancelPoints(&$customer);
	public abstract function approvePoints(&$customer);

}