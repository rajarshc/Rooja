<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct(){
      parent::__construct();
      $this->setId('affiliateplusprogram_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('affiliateplusprogram')->__('Program Information'));
  }

  protected function _beforeToHtml(){
      $this->addTab('form_section', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Program Detail'),
          'title'     => Mage::helper('affiliateplusprogram')->__('Program Detail'),
          'content'   => $this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_edit_tab_form')->toHtml(),
      ));
      
//      $this->addTab('category_section',array(
//      	  'label'     => Mage::helper('affiliateplusprogram')->__('Applied Categories'),
//          'title'     => Mage::helper('affiliateplusprogram')->__('Applied Categories'),
//          'url'       => $this->getUrl('*/*/categories',array(
//        	  '_current'	=> true,
//        	  'id'			=> $this->getRequest()->getParam('id'),
//        	  'store'		=> $this->getRequest()->getParam('store')
//    	  )),
//          'class'     => 'ajax',
//      ));
      
      $this->addTab('condition', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Conditions'),
          'title'     => Mage::helper('affiliateplusprogram')->__('Conditions'),
          'content'   => $this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_edit_tab_conditions')->toHtml(),
      ));
      
      $this->addTab('action', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Commission & Discount'),
          'title'     => Mage::helper('affiliateplusprogram')->__('Commission & Discount'),
          'content'   => $this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_edit_tab_actions')->toHtml(),
      ));
      
      if ($this->getRequest()->getParam('id'))
	      $this->addTab('transaction_section',array(
	      	  'label'     => Mage::helper('affiliateplusprogram')->__('View Transactions'),
	          'title'     => Mage::helper('affiliateplusprogram')->__('View Transactions'),
	          'url'       => $this->getUrl('*/*/transaction',array(
	        	  '_current'	=> true,
	        	  'id'			=> $this->getRequest()->getParam('id'),
	        	  'store'		=> $this->getRequest()->getParam('store')
	    	  )),
	          'class'     => 'ajax',
	      ));
      
      $this->addTab('account_section',array(
      	  'label'     => Mage::helper('affiliateplusprogram')->__('Affiliate Accounts'),
          'title'     => Mage::helper('affiliateplusprogram')->__('Affiliate Accounts'),
          'url'       => $this->getUrl('*/*/account',array(
        	  '_current'	=> true,
        	  'id'			=> $this->getRequest()->getParam('id'),
        	  'store'		=> $this->getRequest()->getParam('store')
    	  )),
          'class'     => 'ajax',
      ));
      
//      $this->addTab('category_section',array(
//      	  'label'     => Mage::helper('affiliateplusprogram')->__('Categories'),
//          'title'     => Mage::helper('affiliateplusprogram')->__('Categories'),
//          'url'       => $this->getUrl('*/*/categories',array(
//        	  '_current'	=> true,
//        	  'id'			=> $this->getRequest()->getParam('id'),
//        	  'store'		=> $this->getRequest()->getParam('store')
//    	  )),
//          'class'     => 'ajax',
//      ));
//      
//      $this->addTab('product_section',array(
//      	  'label'     => Mage::helper('affiliateplusprogram')->__('Products'),
//          'title'     => Mage::helper('affiliateplusprogram')->__('Products'),
//          'url'       => $this->getUrl('*/*/product',array(
//        	  '_current'	=> true,
//        	  'id'			=> $this->getRequest()->getParam('id'),
//        	  'store'		=> $this->getRequest()->getParam('store')
//    	  )),
//          'class'     => 'ajax',
//      ));
      
      return parent::_beforeToHtml();
  }
}