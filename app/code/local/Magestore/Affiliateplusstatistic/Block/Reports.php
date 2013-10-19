<?php
class Magestore_Affiliateplusstatistic_Block_Reports extends Mage_Core_Block_Template
{
	public function __construct(){
		parent::__construct();
		$this->setTemplate('affiliateplusstatistic/reports.phtml');
	}
}