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
 * @see TBT_Rewards_Model_Newsetter_Subscription_Observer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Newsletter extends Mage_Newsletter_Model_Subscriber {
	
	/**
	 * 
	 * @var TBT_Rewards_Model_Newsletter_Subscriber_Wrapper
	 */
	protected $_rsubscriber = null;
	protected $_wasSubscribed = false;

    /**
     * 
     * @param Varien_Event_Observer $o
     */
    public function beforeSaveSubscription($o) {
        $subscriberInst = Mage::helper('rewards/dispatch')->getEventObject($o);
        $this->_wasSubscribed = $subscriberInst->isSubscribed();
        // save whether or not the user has already subscribed
        return $this;
    }

    /**
     * @deprecated using TBT_Rewards_Model_Newsletter_Subscription_Observer instead
     * @param Varien_Event_Observer $o
     */
    public function afterSaveSubscription($o) {
        $newSubscriberInst = Mage::helper('rewards/dispatch')->getEventObject($o);
        
        // check whether or not the user had already subscribed before saving.  If so, call the 
        // newsletter signup transfer model
        if ( $newSubscriberInst->isSubscribed() && ! $this->_wasSubscribed ) {
            Mage::dispatchEvent('rewards_newsletter_new_subscription', array(
                'subscriber' => $newSubscriberInst
            ));
            $this->rewardForNewSubscription();
        }
        
        return $this;
    }
	
	/**
	 * Loops through each Special rule. If it applies, create a new pending transfer.
	 * @deprecated using observer TBT_Rewards_Model_Newsletter_Subscription_Observer now
	 */
	public function rewardForNewSubscription() {
		try {
			throw new Exception ( "TBT_Rewards_Model_Newsletter::rewardForNewSubscription is depecated, please do not use it." );
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'core/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'Could not interface with customer rewards system.' ) );
		}
	}
	
	/**
	 * Returns an array outlining the number of points they will receive for signing up to thew newsletter
	 * @deprecated using TBT_Rewards_Model_Newsletter_Validator instead
	 *
	 * @return array
	 */
	public function getPredictPoints() {
		return Mage::getSingleton ( 'rewards/newsletter_validator' )->getPredictedSubscriptionPoints ();
	}
	
	/**
	 * Fetches the rewards customer trying to subscribe
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	protected function getRewardsCustomer() {
		return Mage::getModel ( 'rewards/customer' )->load ( $this->getCustomerId () );
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
	 * Pseudo newsletter ID since Magento only has one newsletter for the time being.
	 *
	 * @return integer
	 */
	public function getNewsletterId() {
		return 1;
	}
	
	/**
	 * Creates a customer point-transfer of any amount or currency.
	 *
	 * @param  $rule    : Special Rule
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function transferNewsletterPoints($rule) {
		
		$num_points = $rule->getPointsAmount ();
		$currency_id = $rule->getPointsCurrencyId ();
		$rule_id = $rule->getId ();
		$transfer = $this->_getTransferHelper ()->initTransfer ( $num_points, $currency_id, $rule_id );
		
		if (! $transfer) {
			return false;
		}
		
		// get the default starting status - usually Pending
		if (! $transfer->setStatus ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterNewsletter () )) {
			// we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
			return false;
		}
		
		$comments = Mage::helper ( 'rewards' )->__ ( Mage::getStoreConfig ( 'rewards/transferComments/newsletterEarned' ) );
		$customer_id = $this->getCustomerId ();
		
		$transfer->setNewsletterId ( $this->getNewsletterId () )->setComments ( $comments )->setCustomerId ( $customer_id )->save ();
		return true;
	}

}

?>