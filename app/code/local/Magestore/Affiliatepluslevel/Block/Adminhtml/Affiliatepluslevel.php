<?php
class Magestore_Affiliatepluslevel_Block_Adminhtml_Affiliatepluslevel extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_affiliatepluslevel';
    $this->_blockGroup = 'affiliatepluslevel';
    $this->_headerText = Mage::helper('affiliatepluslevel')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('affiliatepluslevel')->__('Add Item');
    parent::__construct();
  }
}