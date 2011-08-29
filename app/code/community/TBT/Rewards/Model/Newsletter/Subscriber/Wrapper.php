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
 * Newsletter
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Newsletter_Subscriber_Wrapper extends Varien_Object {
	
	/**
	 * @var Mage_Newsletter_Model_Subscriber
	 */
	protected $_subscriber = null;
	
	/**
	 * @var TBT_Rewards_Model_Customer
	 */
	protected $_subscribedCustomer = null;
	
	/**
	 * @param Mage_Newsletter_Model_Subscriber $subscriber
	 */
	public function wrap(Mage_Newsletter_Model_Subscriber &$subscriber) {
		$this->_subscriber = $subscriber;
		return $this;
	}
	
	/**
	 * @return Mage_Newsletter_Model_Subscriber
	 */
	public function getOriginalModel() {
		return $this->_subscriber;
	}
	
	/**
	 * Pseudo newsletter ID since Magento only has one newsletter for the time being.
	 *
	 * @return integer
	 */
	public function getNewsletterId() {
		return 1;
	}
	
	/**
	 * Fetches the rewards customer trying to subscribe
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	public function getCustomer() {
		if ($this->_subscribedCustomer == null) {
			$customer_id = $this->_subscriber->getCustomerId ();
			$this->_subscribedCustomer = Mage::getModel ( 'rewards/customer' )->load ( $customer_id );
		}
		return $this->_subscribedCustomer;
	}
	
	/**
	 * @alias getCustomer()
	 */
	public function getRewardsCustomer() {
		return $this->getCustomer ();
	}
	
	/**
	 * True if the customer has received points for the newsletter
	 * @param integer $newsletter_id
	 * @return boolean
	 */
	public function customerHasPointsForNewsletter() {
		$customer = $this->getRewardsCustomer ();
		$hasReceivedPoints = ! $customer->getNewsletterTransfers ( $this->getNewsletterId () )->sumPoints ()->isNoPoints ();
		return $hasReceivedPoints;
	}
	
	/**
	 * @return Mage_Newsletter_Model_Subscriber
	 */
	public function getSubscriber() {
		return $this->getOriginalModel ();
	}

}

?>