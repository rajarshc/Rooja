<?php
class Magestore_Affiliateplusstatistic_Block_Frontend_Filters extends Mage_Core_Block_Template
{
	public function __construct(){
		parent::__construct();
        $this->setTemplate('affiliateplusstatistic/filters.phtml');
	}
	
}