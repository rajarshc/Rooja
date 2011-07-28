<?php

class GoldenSpiralStudio_OneClickCartCheckout_Block_Adminhtml_OneClickCartCheckout_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'oneclickcartcheckout';
        $this->_controller = 'adminhtml_oneclickcartcheckout';
        
        $this->_updateButton('save', 'label', Mage::helper('oneclickcartcheckout')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('oneclickcartcheckout')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('oneclickcartcheckout_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'oneclickcartcheckout_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'oneclickcartcheckout_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('oneclickcartcheckout_data') && Mage::registry('oneclickcartcheckout_data')->getId() ) {
            return Mage::helper('oneclickcartcheckout')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('oneclickcartcheckout_data')->getTitle()));
        } else {
            return Mage::helper('oneclickcartcheckout')->__('Add Item');
        }
    }
}