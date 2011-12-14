<?php

class TBT_Rewards_Model_Customer_Indexer_Observer extends Varien_Object {
	
	
	/**
	 * Update points via observer method (updateUsablePointsBalance)
	 * @param  Varien_Event_Observer $observer
	 * @return TBT_Rewards_Model_Customer_Indexer_Points
	 */
	public function updateUsablePointsBalance($observer) {
	    try {
    	    if(!Mage::helper('rewards/customer_points_index')->canIndex()) {
    	        //shouldn't be using the index 
    	        return $this;
    	    }
    	    
    		$transfer = Mage::helper('rewards/dispatch')->getEventObject($observer);
    	    
    		Mage::helper('rewards/debug_profiler')->start('TBT_Rewards::Customer Points Index - Update usable points balance.');
    		
    		Mage::getSingleton ( 'index/indexer' )->processEntityAction ( $transfer, 'rewards/transfer', Mage_Index_Model_Event::TYPE_SAVE );
    		
    		Mage::helper('rewards/debug_profiler')->stop('"TBT_Rewards::Customer Points Index - Update usable points balance.');
	    } catch(Exception $e) {
	        Mage::helper('rewards/debug')->logException($e);
	    }
		return $this;
	}
	
	/**
	 * Update points via observer method (updateIndexAfterOrderSave)
	 * @param  Varien_Event_Observer $observer
	 * @return TBT_Rewards_Model_Customer_Indexer_Points
	 */
	public function updateIndexAfterOrderSave($observer) {
    	 // @nelkaake We don't need to update the index after an order save since it will update after any points transfer changes.
    	 //			  I'm removing this because I don't want to delay the order creation method at all.
		return $this;
	}

	/**
	 * Update points via observer method (updateIndexBeforeOrderSave)
	 * @param  Varien_Event_Observer $observer
	 * @return TBT_Rewards_Model_Customer_Indexer_Points
	 */
	public function updateIndexBeforeOrderSave($observer) {
	    try {
    	    if(!Mage::helper('rewards/customer_points_index')->canIndex()) {
    	        //shouldn't be using the index 
    	        return $this;
    	    }
    	    
    	    $event = $observer->getEvent();
    	    
    	    $session_customer = $this->_getRewardsCustomer($observer->getEvent()->getOrder());
    	
    		if(!$session_customer || !$session_customer->getId()) {
    		    // Only if a customer model exists and that customer has been already created.
                //TODO tempoarily edited: Mage::helper('rewards/customer_points_index')->error();
    		    Mage::helper('rewards/debug')->error("Customer model does not exist in observer or that customer has not been saved yet.  This caused the points index to be to become out of sync and disabled.");
    		    return $this;
    		}
    		
    		Mage::helper('rewards/debug_profiler')->start('TBT_Rewards::Customer Points Index - Update before order save.');
    		
    		Mage::getSingleton ( 'index/indexer' )->processEntityAction ( 
    		    $session_customer, 'rewards/customer', Mage_Index_Model_Event::TYPE_SAVE );
    		    
    		Mage::helper('rewards/debug_profiler')->stop('TBT_Rewards::Customer Points Index - Update before order save.');
    	    
	    } catch(Exception $e) {
	        Mage::helper('rewards/debug')->logException($e);
	    }
		return $this;
	}
	
	
	
	/**
	 * Update points via observer method (updateIndexOnNewCustomer)
	 * @param  Varien_Event_Observer $observer
	 * @return TBT_Rewards_Model_Customer_Indexer_Points
	 */
	public function updateIndexOnNewCustomer($observer) {
	    try {
    	    if(!Mage::helper('rewards/customer_points_index')->canIndex()) {
    	        //shouldn't be using the index 
    	        return $this;
    	    }
    	    
    	    
    	    $customer = $observer->getEvent()->getCustomer();
    	    $customer = Mage::getModel('rewards/customer')->load($customer->getId());
    	
    		if(!$customer || !$customer->getId()) {
    		    // Only if a customer model exists and that customer has been already created.
                    Mage::helper('rewards/customer_points_index')->error();
    		    Mage::helper('rewards/debug')->error("Customer model does not exist in observer or that customer has not been saved yet.  This caused the points index to be to become out of sync and disabled.");
    		    return $this;
    		}
    		
    		
    		Mage::helper('rewards/debug_profiler')->start('TBT_Rewards::Customer Points Index - Update on new customer.');
    		
    		Mage::getSingleton ( 'index/indexer' )->processEntityAction ( 
    		    $customer, 'rewards/customer', Mage_Index_Model_Event::TYPE_SAVE );
    		    
    		    
    		Mage::helper('rewards/debug_profiler')->stop('TBT_Rewards::Customer Points Index - Update on new customer.');
    	    
	    } catch(Exception $e) {
	        Mage::helper('rewards/debug')->logException($e);
	    }
		return $this;
	}
	
	
	
	/**
	 * Fetches the customer model from either an order/quote or the session, depending on what's available.
	 * @param Mage_Sales_Model_Order $order or quote
	 * @return TBT_Rewards_Model_Customer
	 */
	protected function _getRewardsCustomer($order=null) {
	
         // If the customer exists in the order, use that. If not, use the session customer from the rewards model.
        if ($order) {
            if( $order ->getCustomer() ) {
                // The index session dispatch requires a rewards model, so we should load that.
                $session_customer = $order->getCustomer();
                if (! ($session_customer instanceof TBT_Rewards_Model_Customer)) {
                    $session_customer = Mage::getModel('rewards/customer')->getRewardsCustomer( $session_customer );
                }
            } else {
                $session_customer = Mage::getModel('rewards/customer')->load( $order->getCustomerId() );
            }
        } else {
            $session_customer = $this->_getRewardsSess()->getSessionCustomer();
        }
        
        return $session_customer;
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