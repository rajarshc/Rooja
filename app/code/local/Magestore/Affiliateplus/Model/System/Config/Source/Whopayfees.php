<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Whopayfees
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
			array('value' => 'recipient', 'label'=>Mage::helper('affiliateplus')->__('Recipient')),
            array('value' => 'payer', 'label'=>Mage::helper('affiliateplus')->__('Payer')),
        );
    }

}