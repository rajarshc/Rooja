<?php

class Aurigait_Banner_Block_Adminhtml_Bannermultiple_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'banner';
        $this->_controller = 'adminhtml_bannermultiple';
        
        $this->_updateButton('save', 'label', Mage::helper('banner')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('banner')->__('Delete Banner'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('banners_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'banners_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'banners_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('banners_data') && Mage::registry('banners_data')->getId() ) {
            return Mage::helper('banner')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('banners_data')->getTitle()));
        } else {
            return Mage::helper('banner')->__('Add Banner');
        }
    }
}
