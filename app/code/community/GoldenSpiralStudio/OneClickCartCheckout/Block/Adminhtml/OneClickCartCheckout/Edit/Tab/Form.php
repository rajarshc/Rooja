<?php

class GoldenSpiralStudio_OneClickCartCheckout_Block_Adminhtml_OneClickCartCheckout_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('oneclickcartcheckout_form', array('legend'=>Mage::helper('oneclickcartcheckout')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('oneclickcartcheckout')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('oneclickcartcheckout')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('oneclickcartcheckout')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('oneclickcartcheckout')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('oneclickcartcheckout')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('oneclickcartcheckout')->__('Content'),
          'title'     => Mage::helper('oneclickcartcheckout')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getOneClickCartCheckoutData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getOneClickCartCheckoutData());
          Mage::getSingleton('adminhtml/session')->setOneClickCartCheckoutData(null);
      } elseif ( Mage::registry('oneclickcartcheckout_data') ) {
          $form->setValues(Mage::registry('oneclickcartcheckout_data')->getData());
      }
      return parent::_prepareForm();
  }
}