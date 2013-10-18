<?php
class Magestore_Affiliateplus_Block_Payment_Form extends Mage_Core_Block_Template
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
		return parent::_prepareLayout();
    }
    
    public function getAmount(){
    	return $this->getRequest()->getParam('amount');
    }
}