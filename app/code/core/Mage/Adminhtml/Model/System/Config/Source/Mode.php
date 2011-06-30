<?php

class Mage_Adminhtml_Model_System_Config_Source_Mode
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__('TEST')),
            array('value'=>'0', 'label'=>Mage::helper('adminhtml')->__('LIVE')),
        );
    }

}
