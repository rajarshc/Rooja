<?php

class TBT_Rewards_Model_Newsletter_Subscription_Observer extends Varien_Object {
	
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
        $subscriberInst = $o->getEvent()->getObject();
        
        if ( ! ($subscriberInst instanceof Mage_Newsletter_Model_Subscriber) ) return $this;
        
        $this->_rsubscriber = Mage::getModel('rewards/newsletter_subscriber_wrapper')->wrap($subscriberInst);
        $this->_wasSubscribed = $subscriberInst->isSubscribed ();
		// save whether or not the user has already subscribed
		return $this;
    }

    /**
     * 
     * @param Varien_Event_Observer $o
     */
    public function afterSaveSubscription($o) {
        $newSubscriberInst = $o->getEvent()->getObject();
        
        if ( ! ($newSubscriberInst instanceof Mage_Newsletter_Model_Subscriber) ) return $this;
        
        $newRSubscriberInst = Mage::getModel('rewards/newsletter_subscriber_wrapper')->wrap($newSubscriberInst);
        // We got a call back but the model appears to be different
        if ( $newRSubscriberInst->getCustomer()->getId() != $this->_rsubscriber->getCustomer()->getId() ) {
            return $this;
        }
		
		// check whether or not the user had already subscribed before saving.  If so, call the 
		// newsletter signup transfer model
		if ($newSubscriberInst->isSubscribed () && ! $this->_wasSubscribed) {
			Mage::dispatchEvent ( 'rewards_newsletter_new_subscription', array ('subscriber' => $newRSubscriberInst->getSubscriber () ) );
			$transfer = $this->initReward ( $newRSubscriberInst->getSubscriber () );
		}
		
		return $this;
	}
	
	/**
	 * Loops through each Special rule. If it applies, create a new pending transfer.
	 */
	public function initReward(Mage_Newsletter_Model_Subscriber $subscriber) {
		/**
		 * @var TBT_Rewards_Model_Newsletter_Subscriber_Wrapper
		 */
		$rsubscriber = Mage::getModel ( 'rewards/newsletter_subscriber_wrapper' )->wrap ( $subscriber );
		
		try {
			$ruleCollection = $this->_getNewsletterValidator ()->getApplicableRulesOnNewsletter ();
			
			$customer = $rsubscriber->getRewardsCustomer ();
			$newsletter_id = $rsubscriber->getNewsletterId ();
			
			if (! $rsubscriber->customerHasPointsForNewsletter ()) {
				foreach ( $ruleCollection as $rule ) {
					if (! $rule->getId ()) {
						continue;
					}
					
					try {
						$transfer = Mage::getModel ( 'rewards/newsletter_subscription_transfer' );
						$is_transfer_successful = $transfer->createNewsletterSubscriptionPoints ( $rsubscriber, $rule );
						
						if ($is_transfer_successful) {
							$points_for_signing_up = Mage::getModel ( 'rewards/points' )->set ( $rule );
							Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'You received %s for signing up to a newsletter', $points_for_signing_up ) );
						}
					} catch ( Exception $ex ) {
						Mage::logException ( $ex );
						Mage::getSingleton ( 'core/session' )->addError ( $ex->getMessage () );
					}
				}
			} else {
				Mage::getSingleton ( 'core/session' )->addNotice ( Mage::helper ( 'rewards' )->__ ( "You've already received points for signing up to this newsletter in the past, so you won't get any this time." ) );
			}
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'core/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'Could not interface with customer rewards system.' ) );
		}
	}
	
	/**
	 * Fetches the rewards special validator singleton
	 *
	 * @return TBT_Rewards_Model_Newsletter_Validator
	 */
	protected function _getNewsletterValidator() {
		return Mage::getSingleton ( 'rewards/newsletter_validator' );
	}

}
