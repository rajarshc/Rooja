<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
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
 * Customer Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @nelkaake Added on Saturday June 26, 2010:  
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        $this->loadLayout();

        $customer = Mage::getSingleton('rewards/session')->getRewardsCustomer();
        Mage::register('customer', $customer);

        $this->renderLayout();

        return $this;
    }

    public function referAction() {
        try {
            $email = $this->getRequest()->get("email", null);
            $email = urldecode($email);
            $code = $this->getRequest()->get("code", null);
            $code = urldecode($code);
            $referrer_id = $this->getRequest()->get("id", null);
            $referrer_id = urldecode($referrer_id);

            if (empty($email) && empty($code) && empty($referrer_id)) {
                throw new Exception($this->__('Please specify either a referral e-mail address or referral code.'));
            }

            if (empty($email) && empty($referrer_id)) {
                $email = Mage::helper('rewardsref/code')->getEmail($code);
            } elseif (empty($email) && empty($code)) {
                $email = Mage::getModel('rewards/customer')->load($referrer_id)->getEmail();
            } else {
                if (empty($email)) {
                    throw new Exception($this->__('Please specify either a referral e-mail address or referral code.'));
                }
            }

            Mage::getSingleton('core/session')->setReferrerEmail($email);
        } catch (Exception $e) {
            Mage::logException($e);
            die($e->getMessage());
        }

        //@nelkaake -a 17/02/11: Redirect the affiliate to the right page
        $this->_redirectAffiliate();

        return $this;
    }

    /**
     * Redirects the affiliate that came to this page through some predefined URL
     * to another URL based on the config.
     */
    protected function _redirectAffiliate() {
        $redirect_path = Mage::helper('rewardsref/config')->getRedirectPath(Mage::app()->getStore()->getId());
        $this->getResponse()->setRedirect($redirect_path);
        return $this;
    }

    //@nelkaake Added on Saturday June 26, 2010: TEST
    public function getCurrentRefEmailAction() {
        try {
            $email = Mage::getSingleton('core/session')->getReferrerEmail();
            $website_id = Mage::app()->getStore()->getWebsiteId();
            echo "website: {$website_id}, email: {$email}.";
            die();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    //@nelkaake Added on Saturday June 26, 2010: TEST
    public function makeCodeAction() {
        try {
            $email = $this->getRequest()->get("email", null);
            $email = urldecode($email);

            $code = Mage::helper('rewardsref/code')->getCode($email);
            echo $code;

            die();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function preDispatch() {
        parent::preDispatch();
        if (!Mage::helper('rewards/config')->getIsCustomerRewardsActive()) {
            $this->norouteAction();
            return;
        }
    }

}