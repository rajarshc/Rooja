<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Period
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        return array(
			array('value' => 'week', 'label'=>Mage::helper('affiliateplus')->__('Weekly')),
            array('value' => 'month', 'label'=>Mage::helper('affiliateplus')->__('Monthly')),
            array('value' => 'year', 'label'=>Mage::helper('affiliateplus')->__('Yearly')),
        );
    }
}
