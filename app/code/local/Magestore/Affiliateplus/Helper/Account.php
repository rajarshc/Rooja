<?php

class Magestore_Affiliateplus_Helper_Account extends Mage_Core_Helper_Abstract
{
	public function customerNotLogin(){
		return !$this->customerLoggedIn();
	}
	
	public function customerLoggedIn(){
		return Mage::getSingleton('customer/session')->isLoggedIn();
	}
	
	public function accountNotRegistered(){
		return !$this->isRegistered();
	}
	
	public function isRegistered(){
		return Mage::getSingleton('affiliateplus/session')->isRegistered();
	}
	
	public function accountNotLogin(){
		return !$this->isLoggedIn();
	}
	
	public function isLoggedIn(){
		return Mage::getSingleton('affiliateplus/session')->isLoggedIn();
	}
	
	/**
	 * get Affiliate Session
	 *
	 * @return Magestore_Affiliateplus_Model_Session
	 */
	public function getSession(){
		return Mage::getSingleton('affiliateplus/session');
	}
	
	/**
	 * get Affiliate Account
	 *
	 * @return Magestore_Affiliateplus_Model_Account
	 */
	public function getAccount(){
		return $this->getSession()->getAccount();
	}
	
	public function getNavigationLabel(){
		return $this->__('My Affiliate Account');
	}
	
	public function getAccountBalanceFormated(){
		return Mage::helper('core')->currency($this->getAccount()->getBalance());
		//$baseCurrency = Mage::app()->getStore()->getBaseCurrency();
		//return $baseCurrency->format($this->getAccount()->getBalance());
	}
	
	public function getBalanceLabel(){
		return $this->__('Balance: %s',$this->getAccountBalanceFormated());
	}
	
	public function isEnoughBalance(){
		return ($this->getAccount()->getBalance() >= Mage::helper('affiliateplus/config')->getPaymentConfig('payment_release'));
	}
    
    public function disableStoreCredit() {
        if ($this->accountNotLogin()) {
            return true;
        }
        if (Mage::helper('affiliateplus/config')->getPaymentConfig('store_credit')) {
            return false;
        }
        return true;
    }
    
    public function disableWithdrawal() {
        if ($this->accountNotLogin()) {
            return true;
        }
        if (Mage::helper('affiliateplus/config')->getPaymentConfig('withdrawals')) {
            return false;
        }
        return true;
    }
    
    public function hideWithdrawalMenu() {
        return $this->disableStoreCredit() && $this->disableWithdrawal();
    }
    
    public function getWithdrawalLabel() {
        if ($this->disableWithdrawal()) {
            return 'Store Credits';
        }
        return 'Withdrawals';
    }
}
