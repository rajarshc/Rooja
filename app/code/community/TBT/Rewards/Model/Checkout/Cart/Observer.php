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
 * An observer that observs cart changes and determines if the customer will have enough points
 * to checkout.  If not, a message is displayed.
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Checkout_Cart_Observer {
	
	/**
	 * observers controller_action_postdispatch for Mage_Checkout_CartController
         * then add messages if the layout has not loaded
         * 
	 * @return  TBT_Rewards_Model_Checkout_Cart_Observer
	 */
	public function checkRedemptions($observer) {
            if($observer['controller_action'] instanceof Mage_Checkout_CartController) {
                /* @var Mage_Checkout_CartController */
                $cartController = $observer['controller_action'];
                /* @var Mage_Core_Model_Layout */
                $cartLayoutBlocks = $cartController->getLayout()->getAllBlocks();
                // only add session messages before the layout has been loaded
                if(empty($cartLayoutBlocks)) {
                    if ($this->_getRewardsSess ()->isCustomerLoggedIn ()) {
                            if ($this->_getRewardsSess ()->isCartOverspent ()) {
                                    $msg = Mage::helper ( 'rewards' )->__ ( "You are trying to redeem more points than you have in your balance.  You will not be able to checkout." );
                                    $this->_getCheckoutSession ()->addError ( $msg );
                            }
                    } else {
                            if ($this->_getRewardsSess ()->hasRedemptions ()) {
                                    $msg = Mage::helper ( 'rewards' )->__ ( "You are trying to redeem points but you're not logged in. You may not have enough points to checkout." );
                                    $this->_getCheckoutSession ()->addError ( $msg );
                            }
                    }
                }
            }
            return $this;
	}
	
	/**
	 * Fetches the customer rewards session.
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Fetches the checkout session
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getCheckoutSession() {
		return Mage::getSingleton ( 'checkout/session' );
	}

}
