<?php
class Magestore_Affiliateplus_Block_Payment_Miniform extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('affiliateplus/payment/miniform.phtml');
		return $this;
    }
    
    public function getAccount(){
    	return Mage::getSingleton('affiliateplus/session')->getAccount();
    }
    
    public function getBalance(){
        $balance = Mage::app()->getStore()->convertPrice($this->getAccount()->getBalance());
        return floor($balance * 100) / 100;
    	return round(Mage::app()->getStore()->convertPrice($this->getAccount()->getBalance()),2);
    }
    
    public function getFormatedBalance(){
    	return Mage::helper('core')->currency($this->getAccount()->getBalance());
    }
    
    public function getFormActionUrl(){
        $all = Mage::helper('affiliateplus/payment')->getAvailablePayment();
        if(count($all) > 1)
            $url = $this->getUrl('affiliateplus/index/paymentForm');
        else 
            $url = $this->getUrl('affiliateplus/index/confirmRequest');
        return $url;
    }
    
    public function canRequest() {
        return !Mage::helper('affiliateplus/account')->disableWithdrawal();
    }
    
    public function getMaxAmount() {
        $taxRate = Mage::helper('affiliateplus/payment_tax')->getTaxRate();
        if (!$taxRate) {
            return $this->getBalance();
        }
        $balance = $this->getBalance();
        $maxAmount = $balance * 100 / (100 + $taxRate);
        return round($maxAmount, 2);
    }
}
