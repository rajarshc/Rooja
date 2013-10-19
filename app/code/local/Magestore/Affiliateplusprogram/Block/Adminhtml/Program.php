<?php
class Magestore_Affiliateplusprogram_Block_Adminhtml_Program extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct(){
    $this->_controller = 'adminhtml_program';
    $this->_blockGroup = 'affiliateplusprogram';
    $this->_headerText = Mage::helper('affiliateplusprogram')->__('Program Manager');
    $this->_addButtonLabel = Mage::helper('affiliateplusprogram')->__('Add Program');
    parent::__construct();
  }
}