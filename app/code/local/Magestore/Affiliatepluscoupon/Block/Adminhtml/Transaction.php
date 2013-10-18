<?php
class Magestore_Affiliatepluscoupon_Block_Adminhtml_Transaction extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_transaction';
    $this->_blockGroup = 'affiliatepluscoupon';
    $this->_headerText = Mage::helper('affiliatepluscoupon')->__('Transactions from Coupon Manager');
    parent::__construct();
	$this->_removeButton('add');
  }
}