<?php

class Magestore_Affiliateplusprogram_Model_Mysql4_Program extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('affiliateplusprogram/program', 'program_id');
    }
    
    public function setProgramIsProcessed(Mage_Core_Model_Abstract $object){
    	if (!$object->getId()) return $this;
    	return $this->save($object->setIsProcess('1'));
    }
}