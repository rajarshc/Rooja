<?php
class GoldenSpiralStudio_OneClickCartCheckout_Block_Adminhtml_OneClickCheckout extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_oneclickcartcheckout';
    $this->_blockGroup = 'oneclickcartcheckout';
    $this->_headerText = Mage::helper('oneclickcartcheckout')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('oneclickcartcheckout')->__('Add Item');
    parent::__construct();
  }
}