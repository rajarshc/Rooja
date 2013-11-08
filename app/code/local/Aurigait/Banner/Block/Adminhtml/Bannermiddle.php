<?php
    class Aurigait_Banner_Block_Adminhtml_Bannermiddle extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            parent::__construct();
            //echo 'asds';die;
           // $this->_objectId = 'bannerid';
            $this->_blockGroup = 'banner';
            $this->_controller = 'adminhtml_bannermiddle';
	    //$this->_mode = 'bannersingle';
            $this->_addButtonLabel = Mage::helper('banner')->__('Add Banner Block');
            $this->_removeButton('back');
	        $this->_removeButton('reset');
         //   $this->_updateButton('save', 'label', Mage::helper('engraving')->__('Save File'));
       //     $this->_updateButton('delete', 'label', Mage::helper('engraving')->__('Delete Item'));
			
			/*$this->_addButton('Reset', array(
				'label'     => 'Reset',
				'onclick'   => 'setLocation(\'' . $this->getEngravingDataUrl() .'\')',
				'class'     => 'reset',
			));
            		*/		
        }
     
        public function getHeaderText()
        {
			return Mage::helper('banner')->__('Latest Sales Banner Block');		
        }
		
        				
    }
