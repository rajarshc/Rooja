<?php
class Magestore_Affiliateplus_Block_Payment_Info extends Mage_Core_Block_Template
{
	protected $_payment_method;
	
	public function setPaymentMethod($value){
		$this->_payment_method = $value;
		return $this;
	}
	
	public function getPaymentMethod(){
		return $this->_payment_method;
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('affiliateplus/payment/info.phtml');
		return $this;
    }
    
    public function getPayment(){
    	if ($this->getPaymentMethod())
    		return $this->getPaymentMethod()->getPayment();
    	return null;
    }
}