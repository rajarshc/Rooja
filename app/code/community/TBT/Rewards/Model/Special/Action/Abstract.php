<?php

abstract class TBT_Rewards_Model_Special_Action_Abstract extends Varien_Object {
	
	protected $needs_approval = false;
	
	public function __construct() {
		parent::__construct ();
		$this->setCaption ( "Unnamed customer behavior points rule action" ); // TODO transalte me?
		$this->setDescription ( "Unnamed customer behavior points rule action" ); // TODO translate me?
	}
	
	public abstract function givePoints($data);
	
	public abstract function revokePoints($data);
	
	public abstract function holdPoints($data);
	
	public abstract function cancelPoints($data);
	
	public abstract function approvePoints($data);
}