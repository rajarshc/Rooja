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
 * proccess birthdays
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Birthday_Proccess extends Varien_Object {

    /**
     * proccess all customers for birthdays.
     * If they have a birthday and it's in a range from their last rewarded birthday 
     * reward the customer points and send them an email.
     * 
     * @return int number of birthdays credited
     */
    public function proccessAllCustomers() {
        $proccessed = 0;
        $rules = $this->_getValidator()->getApplicableRulesOnBirthday();

        // no Birthday rules so nothing to do
        if (count($rules) == 0)
            return $proccessed;

        $customers = $this->_getHelper()->getCustomersWithBirthdaysToday();
        foreach ($customers as $customer) {
            /* @var $rules TBT_Rewards_Model_Special[] */
            $rules = $this->_getValidator()->getApplicableRulesOnBirthdayForCustomer($customer);

            // sum rule points using all applicable birthday rules
            $rule = null;
            /* @var $totalPoints TBT_Rewards_Model_Points */
            $totalPoints = Mage::getModel('rewards/points');
            foreach ($rules as $rule) {
                $totalPoints->add($rule);
            }

            // using the last rule proccessed update the points amount to the calculated sum then credit the customer
            if ($rule != null) {
                $rule = clone($rule);
                $points = $totalPoints->getPoints();
                // get the number of points that are of the selected rules currency.
                // TODO: note: points of different currencies are ignored
                $pointsInRuleCurrency = $points[$rule->getPointsCurrencyId()];
                $rule->setPointsAmount($pointsInRuleCurrency);
                $customer = Mage::getModel('rewards/customer')->load($customer->getId());
                if ($this->_creditCustomrForBirthday($customer, $rule)) {
                    $proccessed += 1;
                }
            }
        }
        return $proccessed;
    }

    /**
     * Note: No checks are performed to ensure the birthday is valid
     * 
     * Reward customer points and send them an email.
     * 
     * A valid range is defined by min_days_between_reward.  
     * This prevents a customer from updating their birthday to recive points again befor a year passed.
     * You couyld also use the min_days_between_reward to only reward customers every two or more years on their birthday
     * 
     * @param Mage_Model_Customer $customer
     * @param TBT_Rewards_Model_Special $rule
     */
    protected function _creditCustomrForBirthday($customer, $rule) {
        $pointsString = (string) Mage::getModel('rewards/points')->add($rule);
        // reward customer
        $isTransferSuccess = $this->_getTransfer()->transferBirthdayPoints($customer, $rule);

        if ($isTransferSuccess == true) {
            // use current magento date not the customers birthday
            $date = $this->_getHelper()->getMagentoDate();
            // log transfer
            $this->_getNotify()->systemLog($customer, $pointsString, $date);
            // email customer
            $emailTemplate = $this->_getHelper()->getEmailTemplate($customer->getStoreId());
            $this->_getNotify()->sendEmail($customer, $pointsString, $date, $emailTemplate);
        }
        return $isTransferSuccess;
    }

    /**
     *
     * @return TBT_Rewards_Helper_Birthday
     */
    protected function _getHelper() {
        return Mage::helper('rewards/birthday');
    }

    /**
     * Get the validator module
     * @return TBT_Rewards_Model_Birthday_Validator
     */
    public function _getValidator() {
        return Mage::getSingleton('rewards/birthday_validator');
    }

    /**
     * Get the transfer module
     * @return TBT_Rewards_Model_Birthday_Transfer 
     */
    public function _getTransfer() {
        return Mage::getSingleton('rewards/birthday_transfer');
    }

    /**
     * Get the notify module
     * @return TBT_Rewards_Model_Birthday_Notify 
     */
    public function _getNotify() {
        return Mage::getSingleton('rewards/birthday_notify');
    }

}