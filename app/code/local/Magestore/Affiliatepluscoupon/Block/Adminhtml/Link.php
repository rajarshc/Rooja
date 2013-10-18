<?php
class Magestore_Affiliatepluscoupon_Block_Adminhtml_Link extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_link';
    $this->_blockGroup = 'affiliatepluscoupon';
    $this->_headerText = Mage::helper('affiliatepluscoupon')->__('Transactions from Link Manager');
    parent::__construct();
	$this->_removeButton('add');
  }
}
