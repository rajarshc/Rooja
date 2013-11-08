<?php

class Aurigait_Banner_Block_Adminhtml_Bannerfooter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'banner';
        $this->_controller = 'adminhtml_bannerfooter';        
        $this->_updateButton('save', 'label', Mage::helper('banner')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('banner')->__('Delete Banner'));
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
