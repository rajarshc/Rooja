<?php
class Magestore_Affiliateplus_Block_Adminhtml_Banner extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_banner';
    $this->_blockGroup = 'affiliateplus';
    $this->_headerText = Mage::helper('affiliateplus')->__('Banner Manager');
    $this->_addButtonLabel = Mage::helper('affiliateplus')->__('Add Banner');
    parent::__construct();
  }
}