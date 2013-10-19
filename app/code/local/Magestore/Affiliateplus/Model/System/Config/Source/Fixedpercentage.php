<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Fixedpercentage
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'fixed', 'label'=>Mage::helper('affiliateplus')->__('Fixed')),
            array('value' => 'percentage', 'label'=>Mage::helper('affiliateplus')->__('Percentage')),
        );
    }

}