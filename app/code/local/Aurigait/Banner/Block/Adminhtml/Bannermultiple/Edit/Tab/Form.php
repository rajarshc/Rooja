<?php

class Aurigait_Banner_Block_Adminhtml_Bannermultiple_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('banners_form', array('legend'=>Mage::helper('banner')->__('Item information')));
     
	  $object = Mage::getModel('banner/banner')->load( $this->getRequest()->getParam('id') );
	  $imgPath = Mage::getBaseUrl('media')."Banners/images/thumb/".$object['bannerimage'];
	 
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('banner')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('bannerimage', 'file', array(
          'label'     => Mage::helper('banner')->__('Banner Image'),
          'required'  => false,
          'name'      => 'bannerimage',
	  ));
	  
	  if( $object->getId() ){
		  $tempArray = array(
				  'name'      => 'filethumbnail',
				  'style'     => 'display:none;',
			  );
		  $fieldset->addField($imgPath, 'thumbnail',$tempArray);
	  }
	  
	  $fieldset->addField('link', 'text', array(
          'label'     => Mage::helper('banner')->__('Link'),
          'required'  => false,
          'name'      => 'link',
	  ));
	  $fieldset->addField('gender', 'select', array(
          'label'     => Mage::helper('banner')->__('Gender'),
          'name'      => 'gender',
          'values'    => array(
              array(
                  'value'     => 2,
                  'label'     => Mage::helper('banner')->__('Men'),
              ),

              array(
                  'value'     => 3,
                  'label'     => Mage::helper('banner')->__('Women'),
              ),
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('banner')->__('Both'),
              )
          ),
      ));
	  
	/* $fieldset->addField('target', 'select', array(
          'label'     => Mage::helper('banner')->__('Target'),
          'name'      => 'target',
          'values'    => array(
              array(
                  'value'     => '_blank',
                  'label'     => Mage::helper('banner')->__('Open in new window'),
              ),

              array(
                  'value'     => '_self',
                  'label'     => Mage::helper('banner')->__('Open in same window'),
              ),
          ),
      ));*/
	 
/*	  $fieldset->addField('sort_order', 'text', array(
          'label'     => Mage::helper('banner')->__('Sort Order'),
          'required'  => false,
          'name'      => 'sort_order',
      ));

*/	 
	 	 
   /*   $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('banner')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('banner')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('banner')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content_heading', 'text', array(
          'name'      => 'content_heading',
          'label'     => Mage::helper('banner')->__('Content Heading'),
          'title'     => Mage::helper('banner')->__('Content'),
          'style'     => 'width:600px; height:30px;',
          'wysiwyg'   => false,
          //'required'  => true,
	  'maxlength' => 40,
      ));

      $fieldset->addField('content_text', 'text', array(
          'name'      => 'content_text',
          'label'     => Mage::helper('banner')->__('Content Text'),
          'title'     => Mage::helper('banner')->__('Content'),
          'style'     => 'width:600px; height:30px;',
          'wysiwyg'   => false,
          //'required'  => true,
	  'maxlength' => 80,
      ));

	  $fieldset->addField('buttontext', 'text', array(
          'label'     => Mage::helper('banner')->__('Button Text'),
          'required'  => false,
          'name'      => 'buttontext',
	  'maxlength' => 10,
	  ));
	  $fieldset->addField('fontcolor', 'text', array(
          'label'     => Mage::helper('banner')->__('Font Color'),
          'required'  => false,
          'name'      => 'fontcolor',
	  'maxlength' => 7,
       	  'after_element_html' => '<small><span style="color:#FB8F3C">Provide hexadecimal code of Font Color.Eg-#FAFAFA</span></small>',
	  ));
      $fieldset->addField('contentposition', 'select', array(
          'label'     => Mage::helper('banner')->__('Content Placement'),
          'name'      => 'contentposition',
          'values'    => array(
              array(
                  'value'     => 'TopLeft',
                  'label'     => Mage::helper('banner')->__('Top Left'),
              ),

              array(
                  'value'     => 'TopCenter',
                  'label'     => Mage::helper('banner')->__('Top Center'),
              ),
              
              array(
                  'value'     => 'TopRight',
                  'label'     => Mage::helper('banner')->__('Top Right'),
              ),

              array(
                  'value'     => 'CenterLeft',
                  'label'     => Mage::helper('banner')->__('Center Left'),
              ),
              array(
                  'value'     => 'CenterRight',
                  'label'     => Mage::helper('banner')->__('CenterRight'),
              ),

              array(
                  'value'     => 'Center',
                  'label'     => Mage::helper('banner')->__('Center'),
              ),
              array(
                  'value'     => 'BottomLeft',
                  'label'     => Mage::helper('banner')->__('Bottom Left'),
              ),

              array(
                  'value'     => 'BottomCenter',
                  'label'     => Mage::helper('banner')->__('Bottom Center'),
              ),

              array(
                  'value'     => 'BottomRight',
                  'label'     => Mage::helper('banner')->__('Bottom Right'),
              ),
          ),
      ));*/
      if ( Mage::getSingleton('adminhtml/session')->getBannersData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBannersData());
          Mage::getSingleton('adminhtml/session')->setBannersData(null);
      } elseif ( Mage::registry('banners_data') ) {
          $form->setValues(Mage::registry('banners_data')->getData());
      }
      return parent::_prepareForm();
  }
}
