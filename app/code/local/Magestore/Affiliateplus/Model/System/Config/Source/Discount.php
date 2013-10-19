<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Discount
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
			array('value' => '', 'label'=>Mage::helper('affiliateplus')->__('Both Affiliate program Discount and Shopping cart Discount')),
            array('value' => 'affiliate', 'label'=>Mage::helper('affiliateplus')->__('Only Affiliate program Discount')),
            array('value' => 'system', 'label' => Mage::helper('affiliateplus')->__('Only Shopping cart Discount')),
        );
    }
}
