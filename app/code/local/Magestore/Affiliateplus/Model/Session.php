<?php

class Magestore_Affiliateplus_Model_Session extends Mage_Core_Model_Session
{
	public function getAccount(){
		if (!Mage::registry('load_account')){
			// $customer = $this->getCustomer();
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
			$account = Mage::getModel('affiliateplus/account')
				->setStoreId(Mage::app()->getStore()->getId());
			if (Mage::helper('affiliateplus/config')->getSharingConfig('balance') == 'global')
				$account->setBalanceIsGlobal(true);
			if ($customerId) {
                $account->loadByCustomerId($customerId);
            }
			$this->setData('account',$account);
			Mage::register('load_account',true);
		}
		return $this->getData('account');
	}
	
	public function isRegistered(){
		if ($this->getAccount() && $this->getAccount()->getId())
			return true;
		return false;
	}
	
	public function isLoggedIn(){
		if ($this->isRegistered())
			if ($this->getAccount()->getStatus() == '1')
				return true;
		return false;
	}
	
	public function getCustomer(){
		return Mage::getSingleton('customer/session')->getCustomer();
	}
}