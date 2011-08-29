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
 * Handler for birthdays
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Birthday extends Mage_Core_Helper_Abstract {

    /**
     * Defined by min_days_between_reward in the config.  
     * This prevents a customer from updating their birthday to recive points again before a ~year passed.
     * You could also use the min_days_between_reward to only reward customers every two or more years on their birthday.
     * 
     * @param type $storeId
     * @return type 
     */
    public function getMinDaysBetweenReward($storeId) {
        return (int) Mage::getStoreConfig("rewards/birthday/min_days_between_reward", $storeId);
    }
        
    public function getEmailTemplate($storeId) {
        return Mage::getStoreConfig("rewards/birthday/email_template", $storeId);
    }
    
    public function isLogBirthdayEnabled($storeId) {
        return Mage::getStoreConfigFlag("rewards/birthday/is_log_birthday_enabled", $storeId);
    }

    public function getSenderName($storeId) {
        return Mage::getStoreConfig("trans_email/ident_support/name", $storeId);
    }

    public function getSenderEmail($storeId) {
        return Mage::getStoreConfig("trans_email/ident_support/email", $storeId);
    }

    public function getInitialTransferStatus() {
        return Mage::getStoreConfig('rewards/InitialTransferStatus/AfterBirthday');
    }

    public function getTransferComment() {
        return Mage::getStoreConfig('rewards/transferComments/birthdayEarned');
    }

    /**
     * Get the magento date in format:
     * 2033-12-33 0:0:0
     * 
     * @return string 
     */
    public function getMagentoDate($format='Y-m-d 00:00:00') {
        /* @var $dt Mage_Core_Model_Date */
        $dt = Mage::getModel('core/date');
        $now = $dt->timestamp(time());
        $date = date($format, $now);
        return $date;
    }
        
    /**
     * get customers with a birthday today
     * 
     * @return Mage_Model_Customer[] customers that have a birthday today
     */
    public function getCustomersWithBirthdaysToday() {
        $monthDay = $this->getMagentoDate('m-d');
        $collection = Mage::getResourceModel('customer/customer_collection');
        $collection->addAttributeToSelect('*')                
                ->addAttributeToFilter(TBT_Rewards_Model_Birthday_Validator::birthday_field, array('like'=>"____-$monthDay %"))
                ->load();
        return $collection;
    }
    
}
