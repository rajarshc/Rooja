<?php

class Mage_Avenues_Model_Source_Language
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'EN', 'label' => Mage::helper('secureebs')->__('English')),
            array('value' => 'RU', 'label' => Mage::helper('secureebs')->__('Russian')),
            array('value' => 'NL', 'label' => Mage::helper('secureebs')->__('Dutch')),
            array('value' => 'DE', 'label' => Mage::helper('secureebs')->__('German')),
        );
    }
}



