<?php

class TBT_Rewards_Model_Customer_Observer extends Varien_Object
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
	    $customer = $observer->getEvent()->getCustomer();
	    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($customer);
	    return $this;
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
        
        $newId = $customer->getId();
        
        if ( $this->oldId != $newId ) {
            $isNew = true;
            $this->oldId = $customer->getId(); //This stops multiple triggers of this function
            $customer->createTransferForNewCustomer(); //@TODO Change to separate transfer model
        }
        
        Mage::getSingleton('rewards/session')->setCustomer($customer);
        
        if ( $isNew ) {
            if ( Mage::helper('rewards/dispatch')->smartDispatch('rewards_customer_signup', array(
                'customer' => $customer
            )) ) {
                Mage::getSingleton('rewards/session')->triggerNewCustomerCreate($customer);
                Mage::dispatchEvent('rewards_new_customer_create', array(
                    'customer' => &$customer
                ));
            }
        }
        
        return $this;
    }
	
	/**
	 * BeforeSave for customer
	 * @param Varien_Event_Observer $observer
	 */
	public function customerBeforeSave($observer)
	{
	    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer(  $observer->getEvent()->getCustomer()  );
		$oldId = $customer->getId();
        if (!empty($oldId)) {
            $this->oldId = $oldId;
        }
        
        return $this;
	}
	
	/**
     * True if the id specified is new to this customer model after a SAVE event.
     *
     * @param integer $checkId
     * @return boolean
     */
    public function isNewCustomer($checkId)
    {
        return $this->oldId != $checkId;
    }
	
	/**
	 * Loads the customer wrapper
	 * @param Mage_Customer_Model_Customer $customer
	 * @return TBT_Rewards_Model_Customer_Wrapper
	 */
	private function _loadCustomer(Mage_Customer_Model_Customer $customer)
	{
	    return Mage::getModel('rewards/customer')->load($customer->getId());
	}
	
}