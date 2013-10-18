<?php

class Magestore_Affiliateplusprogram_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= '1';
    const STATUS_DISABLED	= '0';

    static public function getOptionArray(){
        return array(
            self::STATUS_ENABLED    => Mage::helper('affiliateplusprogram')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('affiliateplusprogram')->__('Disabled')
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