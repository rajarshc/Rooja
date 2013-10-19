<?php

class Magestore_Affiliateplus_Model_Total_Address_Credit extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	public function __construct(){
		$this->setCode('affiliateplus_credit');
	}
	
	/**
	 * get Config Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	protected function _getConfigHelper(){
		return Mage::helper('affiliateplus/config');
	}
	
	public function collect(Mage_Sales_Model_Quote_Address $address){
		if (!$this->_getConfigHelper()->getPaymentConfig('store_credit')) {
            return $this;
        }
        if (!$address->getQuote()->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        
        $discount = 0;
        $session = Mage::getSingleton('checkout/session');
        $helper = Mage::helper('affiliateplus/account');
        if ($session->getUseAffiliateCredit()
            && $helper->isLoggedIn()
            && !$helper->disableStoreCredit()
            && $helper->isEnoughBalance()
        ) {
            $balance = $helper->getAccount()->getBalance();
            $discount = floatval($session->getAffiliateCredit());
            if ($discount > $balance) {
                $discount = $balance;
            }
            if ($discount > $address->getGrandTotal()) {
                $discount = $address->getGrandTotal();
            }
            if ($discount < 0) {
                $discount = 0;
            }
            $session->setAffiliateCredit($discount);
        } else {
            $session->setUseAffiliateCredit('');
        }
        
		if ($discount) {
			$baseDiscount = $discount / Mage::app()->getStore()->convertPrice(1);
			$address->setBaseAffiliateCredit(-$baseDiscount);
			$address->setAffiliateCredit(-$discount);
			
			$address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
			$address->setGrandTotal($address->getGrandTotal() - $discount);
		}
		return $this;
	}
	
	public function fetch(Mage_Sales_Model_Quote_Address $address){
		if ($amount = $address->getAffiliateCredit()){
			$address->addTotal(array(
				'code'	=> $this->getCode(),
				'title'	=> $this->_getConfigHelper()->__('Paid by Affiliate Credit'),
				'value'	=> $amount,
			));
		}
		return $this;
	}
}
