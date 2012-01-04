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
 * Special Validator
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Birthday_Validator extends TBT_Rewards_Model_Special_Validator {
    // date of birth
    const birthday_field = 'dob';

    /**
     * Returns all Birthday rules that are valid
     *
     * @return TBT_rewards_Model_Special[]
     */
    public function getApplicableRulesOnBirthday() {
        return $this->getApplicableRules(TBT_Rewards_Model_Birthday_Action::ACTION_CODE);
    }

    /**
     * Returns all rules that are valid for the customer
     * 
     * @param Mage_Model_Customer $customer
     * @return TBT_rewards_Model_Special[] 
     */
    public function getApplicableRulesOnBirthdayForCustomer($customer) {
        return $this->getApplicableRules(TBT_Rewards_Model_Birthday_Action::ACTION_CODE, null, $customer);
    }

    public function getApplicableRules($action, $or_action = null, $customer = null) {
        $resultCollection = array();
        $ruleCollection = Mage::getModel('rewards/special')->getCollection();
        foreach ($ruleCollection as $rule) {
            if ($this->isRuleValidCheck($rule, $action, $customer)) {
                $resultCollection [] = $rule;
            } else if ($or_action != null) {
                if ($this->isRuleValidCheck($rule, $or_action, $customer)) {
                    $resultCollection [] = $rule;
                }
            }
        }
        return $resultCollection;
    }

    /**
     * Runs through each rule validator
     * 
     * Note: the parent does not have the extra $customer argument 
     * parent uses SessionCustomer in place of this param
     *
     * @param TBT_Rewards_Model_Special $rule
     * @param unknown_type $actionType
     * @param Mage_Model_Customer $customer
     * @return boolean true if rule valid
     */
    protected function isRuleValidCheck($rule, $actionType, $customer=null) {
        //check to see if its active
        if (!$rule->getIsActive())
            return false;

        //check its after the start date
        $localDate = Mage::getModel('core/date')->gmtDate();
        if (strtotime($rule->getFromDate()) >= strtotime($localDate))
            return false;

        //check ending date if not empty
        if ($rule->getToDate() != "") {
            //if it isn't make sure its before the ending date
            if (strtotime($rule->getToDate()) + 86399 <= strtotime($localDate))
                return false;
        }

        //check customer has a birthday today and the rule is within the allowed group
        if ($customer != null) {
            if (!$this->isCustomerBirthdayRewardValidToday($customer))
                return false;
            $customer_group_ids = explode(",", $rule->getCustomerGroupIds());
            if (!$this->isInGroup($customer, $customer_group_ids)) {
                return false;
            }
        }

        //Unhashes the coditions and check rule triggers on customer performing the correct action
        $rule_conditions = $this->_getRH()->unhashIt($rule->getConditionsSerialized());
        if (is_array($rule_conditions)) {
            if (!in_array($actionType, $rule_conditions)) {
                return false;
            }
        } else {
            if ($rule_conditions != $actionType) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param Mage_Model_Customer $customer
     * @return bool return true if the customer has a rewardable birthday today  
     */
    public function isCustomerBirthdayRewardValidToday($customer) {
        $dateField = TBT_Rewards_Model_Birthday_Validator::birthday_field;
        // check birthday is set
        if (empty($customer[$dateField]))
            return false;

        // check birthday is today
        $birthdayTimestamp = strtotime($customer[$dateField]);
        $birthMonthDay = date('m-d', $birthdayTimestamp);
        $nowMonthDay = $this->_getHelper()->getMagentoDate('m-d');
        if ($birthMonthDay != $nowMonthDay)
            return false;

        // check birthday is a valid range since the last time they were rewarded for their birthday
        if (false == $this->hasPreviousBirthdayPassedValidTimeRange($customer))
            return false;

        return true;
    }

    /**
     * check enought day have passed since the last birthday reward for the customer.
     * 
     * @param Mage_Model_Customer $customer
     * @return bool true if last birthday's reward was x number of days ago
     */
    public function hasPreviousBirthdayPassedValidTimeRange($customer) {
        $recentTransfer = $this->_getTransfer()->getMostRecentBirthdayTransfer($customer);
        if ($recentTransfer == null)
            return true;

        $lastTransferTime = strtotime($recentTransfer->getCreationTs());

        // use system datetime not Magento's because transfers are relative to system time 
        $timeNow = time();

        $minDaysBetweenReward = $this->_getHelper()->getMinDaysBetweenReward($customer->getStoreId());
        $minSeconds = $minDaysBetweenReward * 24 * 60 * 60;

        if (($timeNow - $lastTransferTime) >= $minSeconds)
            return true;
        return false;
    }

    /**
     *
     * @return TBT_Rewards_Helper_Birthday
     */
    protected function _getHelper() {
        return Mage::helper('rewards/birthday');
    }

    /**
     * Get the transfer module
     * @return TBT_Rewards_Model_Birthday_Transfer 
     */
    protected function _getTransfer() {
        return Mage::getSingleton('rewards/birthday_transfer');
    }

    /**
     * Get the proccess module
     * @return TBT_Rewards_Model_Birthday_Proccess 
     */
    protected function _getProccess() {
        return Mage::getSingleton('rewards/birthday_proccess');
    }

}