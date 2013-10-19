<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Paymentmethod
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
			array('value' => 'api', 'label'=>Mage::helper('affiliateplus')->__('Using API')),
            array('value' => 'manual', 'label'=>Mage::helper('affiliateplus')->__('Manual')),
        );
    }

}