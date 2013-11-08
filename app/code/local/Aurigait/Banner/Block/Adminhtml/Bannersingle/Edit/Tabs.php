<?php

class Aurigait_Banner_Block_Adminhtml_Bannersingle_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('banner_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('banner')->__('Banner Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('banner')->__('Banner Information'),
          'title'     => Mage::helper('banner')->__('Banner Information'),
          'content'   => $this->getLayout()->createBlock('banner/adminhtml_bannersingle_edit_tab_form')->toHtml(),
		  'content'   => $this->getLayout()->createBlock('banner/adminhtml_bannersingle_edit_tab_form')->toHtml(),
      ));
	  
	  
	  
     
      return parent::_beforeToHtml();
  }
}
