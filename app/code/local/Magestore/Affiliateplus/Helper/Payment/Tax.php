<?php
class Magestore_Affiliateplus_Helper_Payment_Tax extends Mage_Core_Helper_Abstract
{
    protected $_calculator = null;
    
    /**
     * get tax calculation object
     * 
     * @return Mage_Tax_Model_Calculation
     */
    public function getCalculator() {
        if ($this->_calculator === null) {
            $this->_calculator = Mage::getSingleton('tax/calculation');
        }
        return $this->_calculator;
    }
    
    /**
     * Check enable tax calculation or not
     * 
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function isTaxEnable($store = null) {
        return Mage::getStoreConfig('affiliateplus/payment/tax_class', $store);
    }
    
    /**
     * get Tax rate (percent) for customer
     * 
     * @param Magestore_Affiliateplus_Model_Account $account
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getTaxRate($account = null, $store = null) {
        $store = Mage::app()->getStore($store);
        
        $taxClassId = Mage::getStoreConfig('affiliateplus/payment/tax_class', $store);
        if (!$taxClassId) return 0;
        
        $calculator = $this->getCalculator();
        $customer = $calculator->getCustomer();
        if (!$customer) {
            $customer = Mage::getModel('customer/customer')->load($account->getCustomerId());
            $calculator->setCustomer($customer);
        }
        
        $request = $calculator->getRateRequest(null, null, null, $store);
        $percent = $calculator->getRate($request->setProductClassId($taxClassId));
        return (float)$percent;
    }
    
    /**
     * calculate tax amount for account
     * 
     * @param float $price
     * @param Magestore_Affiliateplus_Model_Account $account
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getPriceTaxAmount($price, $account, $store = null) {
        $store = Mage::app()->getStore($store);
        
        $rate = $this->getTaxRate($account, $store);
        $taxAmount = $price * $rate / 100;
        return $store->roundPrice($taxAmount);
    }
    
    /**
     * Calculate tax amount
     * 
     * @param float $amount
     * @param float $fee
     * @param Magestore_Affiliateplus_Model_Account $account
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getTaxAmount($amount, $fee, $account, $store = null) {
        /* if (Mage::getStoreConfig('affiliateplus/payment/tax_calc', $store) == 'excl') {
            $price = $amount;
        } else {
            $price = $amount + $fee;
        } */
        return $this->getPriceTaxAmount($amount, $account, $store);
    }
}
