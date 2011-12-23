<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author sweber
 */
abstract class TBT_Testsweet_Model_Abstract {
    /**
     *
     * 
     * @return TBT_Testsweet_Helper_Data 
     */
    public function getHelper() {
        return Mage::helper('testsweet/data');
    }
    
    /**
     * Translate helper
     *
     * @return string
     */
    public function __() {
        $args = func_get_args();
        return call_user_func_array(array($this->getHelper(), '__'), $args);
    }
}
?>
