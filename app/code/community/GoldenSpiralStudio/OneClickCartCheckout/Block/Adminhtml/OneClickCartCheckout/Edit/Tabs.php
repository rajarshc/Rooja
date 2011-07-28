<?php

class GoldenSpiralStudio_OneClickCartCheckout_Block_Adminhtml_OneClickCartCheckout_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('oneclickcartcheckout_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('oneclickcartcheckout')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('oneclickcartcheckout')->__('Item Information'),
          'title'     => Mage::helper('oneclickcartcheckout')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('oneclickcartcheckout/adminhtml_oneclickcartcheckout_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}