<?php
class Magestore_Affiliateplus_Block_Payment_Request extends Mage_Core_Block_Template
{
	/**
	 * Get Affiliate Payment Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Payment
	 */
	protected function _getPaymentHelper(){
		return Mage::helper('affiliateplus/payment');
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		
		$layout = $this->getLayout();
		$paymentMethods = $this->getAllPaymentMethod();
		foreach ($paymentMethods as $code => $method){
			$paymentMethodFormBlock = $layout->createBlock($method->getFormBlockType(),"payment_method_form_$code")->setPaymentMethod($method);
			$this->setChild("payment_method_form_$code",$paymentMethodFormBlock);
		}
		
		return $this;
    }
    
    public function getAllPaymentMethod(){
    	if (!$this->hasData('all_payment_method')){
    		$this->setData('all_payment_method',$this->_getPaymentHelper()->getAvailablePayment());
    	}
    	return $this->getData('all_payment_method');
    }
    
    public function getAmount(){
        if($this->getRequest()->getParam('amount'))
            return $this->getRequest()->getParam('amount');
        $paymentSession = Mage::getSingleton('affiliateplus/session')->getPayment();
        if($paymentSession)
            if($paymentSession->getAmount())
                return $paymentSession->getAmount();
    }
    
    /**
     * get Current Affiliate Account
     *
     * @return Magestore_Affiliateplus_Model_Account
     */
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
        $url = $this->getUrl('affiliateplus/index/confirmRequest');
        return $url;
    }
    
    /**
     * get Tax rate when withdrawal
     * 
     * @return float
     */
    public function getTaxRate() {
        if (!$this->hasData('tax_rate')) {
            $this->setData('tax_rate', Mage::helper('affiliateplus/payment_tax')->getTaxRate());
        }
        return $this->getData('tax_rate');
    }
    
    public function includingFee() {
        return (Mage::getStoreConfig('affiliateplus/payment/who_pay_fees') != 'payer');
    }
    
    public function getPriceFormatJs() {
        $priceFormat = Mage::app()->getLocale()->getJsPriceFormat();
        return Mage::helper('core')->jsonEncode($priceFormat);
    }
}
