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
 * @copyright  Copyright (c) 2011 WDCA (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * This class is an observer that listens to the paypal_prepare_line_items event dispatched by the Mage_Paypal_Model_Cart
 * class.  As of Sweet Tooth 1.5.0.3, this replaces the rewrite of the Mage_Paypal_Model_Standard class with TBT_Rewards_Model_Paypal_Standard
 * The rewrite is still there for versions of Magento lower than 1.4.1.0 since those previous versions do not dispatch the paypal_prepare_line_items
 * event (and don't have the Mage_Paypal_Model_Cart class actually)  
 * 
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Paypal_Observer extends Varien_Object {

    /**
     * Prepare the catalog redemption rule discounts 
     * @param $o observer object
     * 
     * @return    TBT_Rewards_Model_Paypal_Observer  
     */
    public function prepare($o) {
        // Only run for Magento 1.4.2.x and higher
        if ( ! Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4.2') ) {
            return $this;
        }
        
        $event = $o->getEvent();
        
        $ppCart = $event->getPaypalCart();
        $pps = Mage::getModel('rewards/paypal_standard');
        
        // IF the paypal cart object is not defined, we may be in a bad observer dispatch or Magento 1.4.1 (when the had crappy params for the observer)
        if ( ! $ppCart ) return $this;
        
        $_quote = $ppCart->getSalesEntity();
        $discountAmount = $pps->getDiscountDisplacement($_quote);
        
        if ( $discountAmount <= 0 ) {
            return $this; // no discount needed
        }
        
        // This actually adds to the total for that totals item.  This adds the $discountAmount value to the total discounts to be deducted.
        $ppCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, $discountAmount);
        
        return $this;
    }
}