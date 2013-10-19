<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Feetax
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'incl', 'label'=>Mage::helper('affiliateplus')->__('Including Fee')),
            array('value' => 'excl', 'label'=>Mage::helper('affiliateplus')->__('Excluding Fee')),
        );
    }
}
