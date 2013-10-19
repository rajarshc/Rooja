<?php
class Magestore_Affiliateplus_Block_Adminhtml_Selectaccount extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_selectaccount';
    $this->_blockGroup = 'affiliateplus';
    $this->_headerText = Mage::helper('affiliateplus')->__('Select Account to create Withdraw');
     
	parent::__construct();
        $this->_removeButton('add');
  }
}