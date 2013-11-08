<?php
class Aurigait_Banner_Block_Adminhtml_Banner_Form extends Mage_Adminhtml_Block_Widget_Form
{

	protected function _prepareForm()
    {      
	
        $form = new Varien_Data_Form(array(
                                        'id' => 'edit_form',
                                        'action' => $this->getUrl('*/*/save'),
                                        'method' => 'post',
					'enctype'=> "multipart/form-data",
                                     ));
	$bannerModel  = Mage::getModel('banner/banner')->load(1);
//	var_dump($bannerModel->getSize());die;
	$form->setUseContainer(true);
        $this->setForm($form);		
        $fieldset = $form->addFieldset('bannermultiple_form', array('legend'=>Mage::helper('banner')->__('Multiple Image Banner')));
	$fieldset->addField('textlimit', 'text', array(
            'label'     => Mage::helper('banner')->__('Multiple Image Banner'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'textlimit',
//	    'value'     => $bannerModel->getSize(),
        )); 	

	$fieldset->addField('cost', 'text', array(
            'label'     => Mage::helper('banner')->__('banner Cost'),
      //      'class'     => 'required-entry',
//            'required'  => true,
            'name'      => 'cost',
//	    'value'     => $bannerModel->getCost(),
        )); 	
	             
        return parent::_prepareForm();

    }	
    


}
