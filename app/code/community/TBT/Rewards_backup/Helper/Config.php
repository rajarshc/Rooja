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
 * Helper Config
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Config extends Mage_Core_Helper_Abstract {
	
	public function getInitialTransferStatusAfterOrder() {
		return Mage::getStoreConfig ( 'rewards/InitialTransferStatus/AfterOrder' );
	}
	
	public function getInitialTransferStatusAfterReview() {
		return Mage::getStoreConfig ( 'rewards/InitialTransferStatus/AfterReview' );
	}
	
	public function getInitialTransferStatusAfterRating() {
		return Mage::getStoreConfig ( 'rewards/InitialTransferStatus/AfterRating' );
	}
	
	public function getInitialTransferStatusAfterPoll() {
		return Mage::getStoreConfig ( 'rewards/InitialTransferStatus/AfterPoll' );
	}
	
	public function getInitialTransferStatusAfterSendfriend() {
		return Mage::getStoreConfig ( 'rewards/InitialTransferStatus/AfterSendFriend' );
	}
	
	public function getInitialTransferStatusAfterTag() {
		return Mage::getStoreConfig ( 'rewards/InitialTransferStatus/AfterTag' );
	}
	
	/**
	 * @deprecated use TBT_Rewards_Helper_Newsletter_Config instead
	 */
	public function getInitialTransferStatusAfterNewsletter() {
		return Mage::helper ( 'rewards/newsletter_config' )->getInitialTransferStatusAfterNewsletter ();
	}
	
	public function getInitialTransferStatusAfterSignup() {
		return Mage::getStoreConfig ( 'rewards/InitialTransferStatus/AfterSignup' );
	}
	
	public function getInitialTransferToFriendStatus() {
		return Mage::getStoreConfig ( 'rewards/InitialTransferStatus/TransferToFriend' );
	}
	
	/**
	 * Not sure what this is and we're not using it so let's leave
	 * it as true for now.
	 * NOTE: Nothing in the system is currently using this, but its purpose is
	 * simply to check whether or not the TBT_Rewards module is currently active
	 * and/or its output is enabled. This would be useful in cart.phtml and such.
	 * @deprecated until we can figure out what place in the system uses this.
	 * 
	 * @return boolean
	 */
	public function getIsCustomerRewardsActive() {
		//return Mage::getStoreConfigFlag('wishlist/general/active');
		//return Mage::getStoreConfigFlag('rewards/active');
		// Mage::getConfig()->getModuleConfig('TBT_Rewards')->is('active', true)
		return true;
	}
	
	public function shouldRemovePointsOnCancelledOrder() {
		return Mage::getStoreConfig ( 'rewards/orders/shouldRemovePointsOnCancelledOrder' );
	}
	
	public function shouldApprovePointsOnInvoice() {
		return Mage::getStoreConfig ( 'rewards/orders/shouldApprovePointsOnInvoice' );
	}
	
	public function shouldApprovePointsOnShipment() {
		return Mage::getStoreConfig ( 'rewards/orders/shouldApprovePointsOnShipment' );
	}
	
	public function canHaveNegativePtsBalance() {
		return Mage::getStoreConfig ( 'rewards/general/canHaveNegativePtsBalance' );
	}
	
	public function getDefaultMassTransferComment() {
		return Mage::getStoreConfig ( 'rewards/transferComments/defaultMassTransferComment' );
	}
	
	public function getLKey() {
		return Mage::getStoreConfig ( 'rewards/registration/lKey' );
	}
	
	public function getCompanyName() {
		return Mage::getStoreConfig ( 'rewards/registration/companyName' );
	}
	
	public function getCompanyPhoneNumber() {
		return Mage::getStoreConfig ( 'rewards/registration/companyPhoneNumber' );
	}
	
	public function getSimulatedPointMax() {
		return 1000000000;
	}
	
	/**
	 * True if the customer can use points when they're not logged in.
	 * For example if the customer cannot use points when not logged in
	 * they will be asked to login before selecting an option in the 
	 * redemptions catalog drop box.
	 * 
	 * @return boolean
	 */
	public function canUseRedemptionsIfNotLoggedIn() {
		return Mage::getStoreConfigFlag ( 'rewards/general/canUseRedemptionsIfNotLoggedIn' );
	}
	
	/**
	 * If true the system should show "you have 0 points" instead of hiding the message.
	 * @deprecated AVOID AVOID AVOID using this method.
	 *
	 * @return unknown
	 */
	public function noPointsCaption() {
		return Mage::getStoreConfig ( 'rewards/display/noPointsCaption' );
	}
	
	public function showCartRedemptionsWhenZero() {
		return Mage::getStoreConfigFlag ( 'rewards/display/showCartRedemptionsWhenZero' );
	}
	
	public function showCartDistributionsWhenZero() {
		return Mage::getStoreConfigFlag ( 'rewards/display/showCartDistributionsWhenZero' );
	}
	
	public function showSidebarIfNotLoggedIn() {
		return Mage::getStoreConfigFlag ( 'rewards/display/showSidebarIfNotLoggedIn' );
	}
	
	public function showSidebar() {
		return Mage::getStoreConfigFlag ( 'rewards/display/showSidebar' );
	}
	
	public function rewardsCatalogNumProducts() {
		return ( int ) Mage::getStoreConfig ( 'rewards/display/rewardsCatalogNumProducts' );
	}
	
	/**
	 * @deprecated If the admin wants to hide the redeemer just make the redemption rule
	 * not apply to any customers that are not logged in.
	 *
	 * @return boolean
	 */
	public function showRedeemerWhenNotLoggedIn() {
		return true;
	}
	
	/**
	 * True if catalog distributions should be ignored when catalog redemptions are active
	 * on a particular line item.
	 *
	 * @return boolean
	 */
	public function doIgnoreCDWhenCR() {
		return Mage::getStoreConfigFlag ( 'rewards/general/doIgnoreCDWhenCR' );
	}
	
	/**
	 * True if catalog distributions should be ignored when shopping cart redemptions are active
	 * on a particular line item.
	 *
	 * @return boolean
	 */
	public function doIgnoreCDWhenSCR() {
		return Mage::getStoreConfigFlag ( 'rewards/general/doIgnoreCDWhenSCR' );
	}
	
	public function showProductEditPointsTab() {
		return false;
	}
	
	public function showCustomerEditPointsTab() {
		return true;
	}
	
	/**
	 * @deprecated rules now have a getApplyToShipping attribute
	 *
	 * @param unknown_type $store
	 * @return unknown
	 */
	public function discountShipping($store = null) {
		return Mage::getStoreConfigFlag ( 'rewards/general/shopping_cart_rule_discount_shipping', $store );
	}
	
	//@nelkaake Added on Wednesday May 5, 2010: 
	public function calcCartPointsAfterDiscount($store = null) {
		return Mage::getStoreConfigFlag ( 'rewards/general/shopping_cart_rule_earn_after_discount', $store );
	}
	
	//@nelkaake Added on Wednesday May 5, 2010: this will later be turned into a config option
	public function earnCatalogPointsForTax($store = null) {
		return false; //Mage::getStoreConfigFlag('rewards/general/shopping_cart_rule_discount_shipping', $store);
	}
	
	public function maximumPointsSpentInCart() {
		return ( int ) Mage::getStoreConfig ( 'rewards/general/maximum_points_spent_in_cart' );
	}
	
	public function maximumPointsSpentinCatalog() {
		return ( int ) Mage::getStoreConfig ( 'rewards/general/maximum_points_spent_in_catalog' );
	}

}