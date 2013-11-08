<?php

class Aurigait_Banner_Block_Adminhtml_Bannerfooter_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $bannerModel= Mage::getModel('banner/banner')->load( $this->getRequest()->getParam('id') );
//$object = Mage::getModel('banner/banner')->load( $this->getRequest()->getParam('id') );
	  $imgPath = Mage::getBaseUrl('media')."footer_banner/thumb/".$bannerModel->getBannerimage();
//	var_dump($bannerModel->getSize());die;
		
        $fieldset = $form->addFieldset('bannerfooter_form', array('legend'=>Mage::helper('banner')->__('Footer Banner')));
		
		$fieldset->addField('block_id','select', array(
		            'label'     => Mage::helper('banner')->__('Banner Block'),
		            'class'     => 'required-entry',
		            'required'  => true,
		            'name'      => 'block_id',
		            'values'=> Mage::getModel('banner/bannerblock')->getBannerBlockForForm()
		        )); 
			
			$fieldset->addField('bannerimage','file', array(
		            'label'     => Mage::helper('banner')->__('Banner Image'),
		            'name'      => 'bannerimage',
		            'after_element_html' => "<br/>All images should be of equal size"
		        )); 	
	if( $bannerModel->getBanner_id() ){
		  $tempArray = array(
				  'name'      => 'filethumbnail',
				  'style'     => 'display:none;'
                                  
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
        $fieldset->addField('position', 'select', array(
            'label'     => Mage::helper('banner')->__('Banner Position'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'position',
	    'values'    => array(
              array(
                  'value'     => 'left',
                  'label'     => Mage::helper('banner')->__('Left'),
              ),

              array(
                  'value'     => 'right',
                  'label'     => Mage::helper('banner')->__('Right'),
              ),
          ),
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
