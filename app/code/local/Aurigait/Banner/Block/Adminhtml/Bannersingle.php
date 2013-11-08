<?php
    class Aurigait_Banner_Block_Adminhtml_Bannersingle extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            $this->_controller = "adminhtml_bannerfooter";
			$this->_blockGroup = "banner";
			$this->_headerText = Mage::helper("banner")->__("Bannerblock Manager");
			$this->_addButtonLabel = Mage::helper("banner")->__("Add New Item");
			parent::__construct();
        }
     
        public function getHeaderText()
        {
			return Mage::helper('banner')->__('Footer Banner');		
        }
		
        				
    }
