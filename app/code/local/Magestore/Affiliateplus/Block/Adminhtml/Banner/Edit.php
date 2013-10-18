<?php

class Magestore_Affiliateplus_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'affiliateplus';
        $this->_controller = 'adminhtml_banner';
        
        $this->_updateButton('save', 'label', Mage::helper('affiliateplus')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('affiliateplus')->__('Delete Banner'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('affiliateplus_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'affiliateplus_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'affiliateplus_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
			
			function showFileField(){
				var file = $('source_file').up('tr');
				var width = $('width').up('tr');
				var height = $('height').up('tr');
				var view = $('banner_view');

				if($('type_id').getValue() == 1 || $('type_id').getValue() == 2){					
					$('source_file').addClassName('required-entry');
					$('width').addClassName('required-entry');
					$('height').addClassName('required-entry');
					
					if(view != null || view != undefined){
						view.up('tr').show();
						$('source_file').removeClassName('required-entry');
					}
					
					file.show();
					width.show();
					height.show();
				}else{
					if(view != null || view != undefined){
						view.up('tr').hide();
					}
					
					$('source_file').removeClassName('required-entry');
					$('width').removeClassName('required-entry');
					$('height').removeClassName('required-entry');
					
					file.hide();
					width.hide();
					height.hide();
				}
			}
			
			showFileField();
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('banner_data') && Mage::registry('banner_data')->getId() ) {
            return Mage::helper('affiliateplus')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('banner_data')->getTitle()));
        } else {
            return Mage::helper('affiliateplus')->__('Add Banner');
        }
    }
}