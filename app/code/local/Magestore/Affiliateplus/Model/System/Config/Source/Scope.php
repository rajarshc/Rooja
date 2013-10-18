<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Scope
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
			array('value' => 'global', 'label'=>Mage::helper('affiliateplus')->__('Global')),
            array('value' => 'store', 'label'=>Mage::helper('affiliateplus')->__('Store')),
        );
    }

}