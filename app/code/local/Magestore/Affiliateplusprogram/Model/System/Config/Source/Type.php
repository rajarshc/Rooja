<?php

class Magestore_Affiliateplusprogram_Model_System_Config_Source_Type
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        return array(
        	array('value' => '', 'label'=>Mage::helper('affiliateplusprogram')->__('As General Configuration')),
			array('value' => 'sales', 'label'=>Mage::helper('affiliateplus')->__('Pay per Sales')),
            array('value' => 'profit', 'label'=>Mage::helper('affiliateplus')->__('Pay per Profit')),
        );
    }
}