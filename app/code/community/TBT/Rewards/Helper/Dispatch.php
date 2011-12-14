<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * This class is part of our new Smart Dispatch framework which
 * is designed to make Sweet Tooth more modular for developers
 * building on top of Sweet Tooth.
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Dispatch extends Mage_Core_Helper_Abstract {
	
    /**
     * Use this function which acts as Mage::dispatchEvent
     * but with validation where any observing entity can
     * change the isValid flag to tell the dispatcher to
     * act as if the event did not happen.
     * 
     * Callers should use this function in an if statement
     * which wraps code which depends on this event occuring.
     *
     * @param string $eventName
     * @param array $data key values of event attributes
     * @param unknown_type $isValid initial state of the dispatch
     * @return boolean
     */
    public function smartDispatch($eventName, array $data, $isValid = true) {
        
        $result = new Varien_Object(array(
            'is_valid'      => $isValid
        ));
        
        // Append result object to data
        $data['result'] = $result;
        
        Mage::dispatchEvent($eventName, $data);
        return $result->getIsValid();
    }
    
    /**
     * In Magento 1.3.3 and lower the data is stored as getObject(), but in newer versions
     * the data is stored as getDataObject.
     * @param Varien_Event_Observer $o
     * @return Mage_Core_Model_Abstract
     */
    public function getEventObject(Varien_Event_Observer $o) {
        $obj = $o->getEvent()->getDataObject();
        if ( ! Mage::helper('rewards/version')->isBaseMageVersionAtLeast("1.4.0.0") ) {
            $obj = $o->getEvent()->getObject();
        }
        
        return $obj;
    }
    
}
