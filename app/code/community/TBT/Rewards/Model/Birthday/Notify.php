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
 * Transfer Reference
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Birthday_Notify extends Varien_Object {
    const logFileName = 'rewards_birthday.log';

    /**
     *
     * @param type $template
     * @param TBT_Rewards_Model_Customer $customer
     * @param type $pointsString
     * @return boolean send successful? 
     */
    public function sendEmail($customer, $pointsString, $date, $template) {
        /* @var $translate Mage_Core_Model_Translate */
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        /* @var $email Mage_Core_Model_Email_Template */
        $email = Mage::getModel('core/email_template');
        $sender = array(
            'name' => strip_tags(Mage::helper('rewards/expiry')->getSenderName($customer->getStoreId())),
            'email' => strip_tags(Mage::helper('rewards/expiry')->getSenderEmail($customer->getStoreId()))
        );
        $email->setDesignConfig(array(
            'area' => 'frontend',
            'store' => $customer->getStoreId())
        );
        $vars = array(
            'customer_name' => $customer->getName(),
            'customer_email' => $customer->getEmail(),
            'store_name' => $customer->getStore()->getName(),
            'points_transfered' => $pointsString,
            'points_balance' => (string) $customer->getPointsSummary(),
        );
        $email->sendTransactional($template, $sender, $customer->getEmail(), $customer->getName(), $vars);
        $translate->setTranslateInline(true);
        return $email->getSentSuccess();
    }

    /**
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string $pointsString
     * @param type $date
     */
    public function systemLog($customer, $pointsString, $date) {
        $storeId = $customer->getStoreId();
        if ($this->_getHelper()->isLogBirthdayEnabled($storeId)) {
            $name = $customer->getName();
            $email = $customer->getEmail();
            $msg = $this->_getHelper()->__("Customer %s with the e-mail %s has a birthday on %s and been rewarded %s.", $name, $email, $date, $pointsString);
            Mage::log($msg, null, $this->getLogFileName());
        }
    }

    /**
     * return the name of the log file that is to be saved under ./var/log/rewards/
     * @return string
     */
    public function getLogFileName() {
        return TBT_Rewards_Model_Birthday_Notify::logFileName;
    }

    /**
     *
     * @return TBT_Rewards_Helper_Birthday
     */
    protected function _getHelper() {
        return Mage::helper('rewards/birthday');
    }

}