<?php

class Magestore_Affiliateplus_Model_System_Config_Source_Actiontype
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        $types = new Varien_Object(array(
    		'actions'	=> array(
    			array('value' => '3', 'label'=>Mage::helper('affiliateplus')->__('Sale')),
    		)
    	));
        Mage::dispatchEvent('affiliateplus_get_action_types',array(
    		'types'		=> $types,
    	));
        return $types->getActions();
    }
    
    public function getOptionList(){
        $result = array();
        foreach($this->toOptionArray() as $option){
            $result[$option['value']] = $option['label']; 
        }
        return $result;
    }
}