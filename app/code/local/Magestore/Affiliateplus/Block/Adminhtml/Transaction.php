<?php
class Magestore_Affiliateplus_Block_Adminhtml_Transaction extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_transaction';
    $this->_blockGroup = 'affiliateplus';
    $this->_headerText = Mage::helper('affiliateplus')->__('Transaction Manager');
    parent::__construct();
	$this->_removeButton('add');
  }
}