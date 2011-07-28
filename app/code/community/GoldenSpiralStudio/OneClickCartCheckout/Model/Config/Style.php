<?php 

class GoldenSpiralStudio_OneClickCartCheckout_Model_Config_Style
{
    public function toOptionArray()
    {
        return array(
	        array('value'=>'default', 'label'=>Mage::helper('oneclickcartcheckout')->__('Default (based on your theme)')),
	        array('value'=>'dark', 'label'=>Mage::helper('oneclickcartcheckout')->__('Dark')),
	        array('value'=>'light', 'label'=>Mage::helper('oneclickcartcheckout')->__('Light')),
	        array('value'=>'macosx', 'label'=>Mage::helper('oneclickcartcheckout')->__('MacOSx')),
	        array('value'=>'red', 'label'=>Mage::helper('oneclickcartcheckout')->__('Red')),
        );
    }

} ?>