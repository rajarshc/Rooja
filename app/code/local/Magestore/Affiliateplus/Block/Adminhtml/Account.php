<?php
class Magestore_Affiliateplus_Block_Adminhtml_Account extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_account';
    $this->_blockGroup = 'affiliateplus';
    $this->_headerText = Mage::helper('affiliateplus')->__('Account Manager');
    $this->_addButtonLabel = Mage::helper('affiliateplus')->__('Add Account');
    parent::__construct();
  }
}