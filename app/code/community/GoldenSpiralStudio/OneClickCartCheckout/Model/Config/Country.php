<?php 

class GoldenSpiralStudio_OneClickCartCheckout_Model_Config_Country
{
    public function toOptionArray()
    {
        return array(
        array('value'=>'enabled', 'label'=>Mage::helper('oneclickcartcheckout')->__('Enabled')),
            array('value'=>'disabled', 'label'=>Mage::helper('oneclickcartcheckout')->__('Disabled')),
            
            array('value'=>'detect', 'label'=>Mage::helper('oneclickcartcheckout')->__('Detect By IP-address')),
        );
    }

} ?>