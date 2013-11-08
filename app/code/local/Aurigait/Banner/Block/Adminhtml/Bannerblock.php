<?php


class Aurigait_Banner_Block_Adminhtml_Bannerblock extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_bannerblock";
	$this->_blockGroup = "banner";
	$this->_headerText = Mage::helper("banner")->__("Bannerblock Manager");
	$this->_addButtonLabel = Mage::helper("banner")->__("Add New Block");
	parent::__construct();
	
	}

}