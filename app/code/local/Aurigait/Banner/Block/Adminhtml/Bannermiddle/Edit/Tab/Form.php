<?php

class Aurigait_Banner_Block_Adminhtml_Bannermiddle_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $bannerModel= Mage::getModel('banner/banner')->load( $this->getRequest()->getParam('id') );
//$object = Mage::getModel('banner/banner')->load( $this->getRequest()->getParam('id') );
	  $imgPath = Mage::getBaseUrl('media')."middle_banner/thumb/".$bannerModel->getBannerimage();
//	var_dump($bannerModel->getSize());die;
		
        $fieldset = $form->addFieldset('bannermiddle_form', array('legend'=>Mage::helper('banner')->__('Midlle Banner')));
	$fieldset->addField('bannerimage','file', array(
            'label'     => Mage::helper('banner')->__('Banner Image'),
            'required'  => false,
            'name'      => 'bannerimage',
            'after_element_html' => "<br/>Image will be resized to 500X500 if it is bigger than this.All images should be of equal size"
        )); 	
	if( $bannerModel->getBanner_id() ){
		  $tempArray = array(
				  'name'      => 'filethumbnail',
				  'style'     => 'display:none;',
                                  
			  );
		  $fieldset->addField($imgPath, 'thumbnail',$tempArray);
	  }
          $fieldset->addField('image_text', 'text', array(
            'label'     => Mage::helper('banner')->__('Banner Text'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'image_text',
	    'value'     => $bannerModel->getImageText(),
        ));
	$fieldset->addField('link', 'text', array(
            'label'     => Mage::helper('banner')->__('Banner Link'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'link',
	    'value'     => $bannerModel->getLink(),
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
        $fieldset->addField('position', 'select', array(
            'label'     => Mage::helper('banner')->__('Banner Position'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'position',
	    'values'    => array(
              array(
                  'value'     => 'top',
                  'label'     => Mage::helper('banner')->__('Top'),
              ),

              array(
                  'value'     => 'bottom',
                  'label'     => Mage::helper('banner')->__('Bottom'),
              ),
          ),
        ));
		$fieldset->addField('sort_order', 'text', array(
            'label'     => Mage::helper('banner')->__('Sort Order'),
            'name'      => 'sort_order',
	    	'value'     => $bannerModel->getSortOrder(),
        ));
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
