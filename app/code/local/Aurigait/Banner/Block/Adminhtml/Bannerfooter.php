<?php
    class Aurigait_Banner_Block_Adminhtml_Bannerfooter extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            parent::__construct();
            $this->_blockGroup = 'banner';
            $this->_controller = 'adminhtml_bannerfooter';
	        $this->_addButtonLabel = Mage::helper('banner')->__('Add Footer Banner');
            $this->_removeButton('back');
	        $this->_removeButton('reset');
         	
        }
     
        public function getHeaderText()
        {
			return Mage::helper('banner')->__('Footer Banner Block');		
        }
		
        				
    }
