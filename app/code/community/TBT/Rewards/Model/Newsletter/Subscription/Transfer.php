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
 * Transfer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Newsletter_Subscription_Transfer extends TBT_Rewards_Model_Transfer {
	
	public function _construct() {
		parent::_construct ();
	}
	
	/**
	 * 
	 * @param int $id
	 */
	public function setNewsletterId($id) {
		$this->clearReferences ();
		$this->setReferenceType ( TBT_Rewards_Model_Newsletter_Subscription_Reference::REFERENCE_TYPE_ID );
		$this->setReferenceId ( $id );
		$this->_data ['newsletter_id'] = $id;
		
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isNewsletter() {
		return ($this->getReferenceType () == TBT_Rewards_Model_Newsletter_Subscription_Reference::REFERENCE_TYPE_ID) || isset ( $this->_data ['newsletter_id'] );
	}
	
	/**
	 * 
	 * Gets all transfers associated with the given newsletter ID
	 * @param int $newsletter_id
	 */
	public function getTransfersAssociatedWithNewsletter($newsletter_id) {
		return $this->getCollection ()->addFilter ( 'reference_type', TBT_Rewards_Model_Newsletter_Subscription_Reference::REFERENCE_TYPE_ID )->addFilter ( 'reference_id', $newsletter_id );
	}
	
	/**
	 * Fetches the transfer helper
	 *
	 * @return TBT_Rewards_Helper_Transfer
	 */
	protected function _getTransferHelper() {
		return Mage::helper ( 'rewards/transfer' );
	}
	
	/**
	 * Fetches the rewards special validator singleton
	 *
	 * @return TBT_Rewards_Model_Special_Validator
	 */
	protected function _getSpecialValidator() {
		return Mage::getSingleton ( 'rewards/special_validator' );
	}
	
	/**
	 * Fetches the rewards special validator singleton
	 *
	 * @return TBT_Rewards_Model_Special_Validator
	 */
	protected function _getNewsletterValidator() {
		return Mage::getSingleton ( 'rewards/newsletter_validator' );
	}
	
	/**
	 * Creates a customer point-transfer of any amount or currency.
	 *
	 * @param  $rule    : Special Rule
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function createNewsletterSubscriptionPoints(TBT_Rewards_Model_Newsletter_Subscriber_Wrapper $rsubscriber, $rule) {
		
		$num_points = $rule->getPointsAmount ();
		$currency_id = $rule->getPointsCurrencyId ();
		$rule_id = $rule->getId ();
		$transfer = $this->initTransfer ( $num_points, $currency_id, $rule_id );
		$customer = Mage::getModel('rewards/customer')->getRewardsCustomer($rsubscriber->getCustomer ());
		$store_id = $customer->getStore ()->getId ();
		
		if (! $transfer) {
			return false;
		}
		
		// get the default starting status - usually Pending
		$initial_status = Mage::helper ( 'rewards/newsletter_config' )->getInitialTransferStatusAfterNewsletter ( $store_id );
		
		if (! $transfer->setStatus ( null, $initial_status )) {
			// we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
			return false;
		}
		
		// Translate the message through the core translation engine (nto the store view system) in case people want to use that instead
		// This is not normal, but we found that a lot of people preferred to use the standard translation system insteaed of the 
		// store view system so this lets them use both.
		$initial_transfer_msg = Mage::getStoreConfig ( 'rewards/transferComments/newsletterEarned', $store_id );
		$comments = Mage::helper ( 'rewards' )->__ ( $initial_transfer_msg );
		
		$customer_id = $rsubscriber->getCustomer ()->getId ();
		
		$this->setNewsletterId ( $rsubscriber->getNewsletterId () )->setComments ( $comments )->setCustomerId ( $customer_id )->save ();
		
		return true;
	}

}