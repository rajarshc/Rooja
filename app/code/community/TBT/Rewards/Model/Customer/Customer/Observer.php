<?php

class TBT_Rewards_Model_Customer_Customer_Observer extends Varien_Object
{
	
	/**
	 * @var int
	 */
	protected $oldId = -1;
	
	/**
	 * AfterLoad for customer
	 * @param Varien_Event_Observer $observer
	 */
	public function customerAfterLoad(Varien_Event_Observer $observer)
	{
		if(! Mage::helper('rewards/version')->isBaseMageVersionAtLeast("1.3.3")) {
		    $review = $o->getEvent ()->getObject ();
		}
        $customer = $this->_loadCustomer(Mage::helper('rewards/dispatch')->getEventObject($observer));
        $customer->loadCollections();
    }

    /**
     * AfterSave for customer
     * @param Varien_Event_Observer $observer
     */
    public function customerAfterSave(Varien_Event_Observer $observer) {
        $customer_obj = $observer->getEvent()->getCustomer();
        $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($customer_obj);
        //If the customer is new (hence not having an id before) get applicable rules,
        //and create a transfer for each one
        $isNew = false;
        if ( $customer->isNewCustomer($customer->getId()) ) {
            $isNew = true;
            $this->oldId = $customer->getId(); //This stops multiple triggers of this function
            $customer->createTransferForNewCustomer(); //@TODO Change to separate transfer model
        }
        Mage::getSingleton('rewards/session')->setCustomer($customer);
        if ( $isNew ) {
            Mage::getSingleton('rewards/session')->triggerNewCustomerCreate($customer);
            Mage::dispatchEvent('rewards_new_customer_create', array(
                'customer' => &$customer
            ));
        }
    }
	
	/**
	 * BeforeSave for customer
	 * @param Varien_Event_Observer $observer
	 */
	public function customerBeforeSave(Varien_Event_Observer $observer)
	{
        $customer = $this->_loadCustomer(Mage::helper('rewards/dispatch')->getEventObject($observer));
        $oldId = $customer->getId();
        if ( ! empty($oldId) ) {
            $this->oldId = $oldId;
        }
	}
	
	/**
     * True if the id specified is new to this customer model after a SAVE event.
     *
     * @param integer $checkId
     * @return boolean
     */
    public function isNewCustomer($checkId)
    {
        return ($this->oldId != $checkId);
    }
	
	/**
	 * Loads the customer wrapper
	 * @param Mage_Customer_Model_Customer $customer
	 * @return TBT_Rewards_Model_Customer_Customer_Wrapper
	 */
	private function _loadCustomer(Mage_Customer_Model_Customer $customer)
	{
	    return Mage::getModel('rewards/customer')->getRewardsCustomer($customer);
	}
	
}