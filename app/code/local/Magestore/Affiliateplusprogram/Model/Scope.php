<?php

class Magestore_Affiliateplusprogram_Model_Scope extends Varien_Object
{
    const SCOPE_GLOBAL		= '0';
	const SCOPE_GROUPS		= '1';
    const SCOPE_CUSTOMER	= '2';

    static public function getOptionArray(){
        return array(
            self::SCOPE_GLOBAL		=> Mage::helper('affiliateplusprogram')->__('Global'),
            self::SCOPE_GROUPS		=> Mage::helper('affiliateplusprogram')->__('Customer Groups'),
			self::SCOPE_CUSTOMER	=> Mage::helper('affiliateplusprogram')->__('Customer'),
        );
    }
    
    static public function getOptions(){
    	$options = array();
    	foreach (self::getOptionArray() as $value=>$label)
    		$options[] = array(
				'value'	=> $value,
				'label'	=> $label
			);
    	return $options;
    }
    
    public function toOptionArray(){
    	return self::getOptions();
    }
}