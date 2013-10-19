<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct(){
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'affiliateplusprogram';
        $this->_controller = 'adminhtml_program';
        
        $this->_updateButton('save', 'label', Mage::helper('affiliateplusprogram')->__('Save Program'));
        $this->_updateButton('delete', 'label', Mage::helper('affiliateplusprogram')->__('Delete Program'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText(){
        if(Mage::registry('affiliateplusprogram_data') && Mage::registry('affiliateplusprogram_data')->getId()){
            return Mage::helper('affiliateplusprogram')->__("Edit Program '%s'", $this->htmlEscape(Mage::registry('affiliateplusprogram_data')->getName()));
        }else{
            return Mage::helper('affiliateplusprogram')->__('Add Program');
        }
    }
}