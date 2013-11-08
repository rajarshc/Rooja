<?php
    class Aurigait_Banner_Block_Adminhtml_Bannermultiple extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
 	 public function __construct()
  {
    $this->_controller = 'adminhtml_bannermultiple';
    $this->_blockGroup = 'banner';
    $this->_headerText = Mage::helper('banner')->__('Header Banners Manager');
    $this->_addButtonLabel = Mage::helper('banner')->__('Add Banner');
	
    parent::__construct();
  }
}	
       				
    
