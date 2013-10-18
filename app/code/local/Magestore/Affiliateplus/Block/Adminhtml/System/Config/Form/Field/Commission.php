<?php
class Magestore_Affiliateplus_Block_Adminhtml_System_Config_Form_Field_Commission extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	public function __construct(){
		$this->addColumn('sales',array(
			'label'	=> Mage::helper('affiliateplus')->__('#Sales/#Orders'),
			'style'	=> 'width:120px',
		));
		
		$this->addColumn('commission',array(
			'label'	=> Mage::helper('affiliateplus')->__('Commission'),
			'style'	=> 'width:120px',
		));
		
		$this->_addAfter = false;
		$this->_addButtonLabel = Mage::helper('affiliateplus')->__('Add Level');
		parent::__construct();
	}
}