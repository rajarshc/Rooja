<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Salestype
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        return array(
			array('value' => 'orders', 'label'=>Mage::helper('affiliateplus')->__('Total Orders')),
            array('value' => 'sales', 'label'=>Mage::helper('affiliateplus')->__('Total Sales')),
        );
    }
}