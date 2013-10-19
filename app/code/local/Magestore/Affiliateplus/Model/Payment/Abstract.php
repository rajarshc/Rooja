<?php

abstract class Magestore_Affiliateplus_Model_Payment_Abstract extends Mage_Core_Model_Abstract
{
	protected $_code = '';
	protected $_store_id = null;
	
	protected $_payment;
	
	protected $_formBlockType = 'affiliateplus/payment_form';
	protected $_infoBlockType = 'affiliateplus/payment_info';
	
    public function _construct(){
        parent::_construct();
        //$this->_init('affiliateplus/payment');
    }
    
    /**
     * Set Payment for this metho
     *
     * @param Magestore_Affiliateplus_Model_Payment $value
     * @return Magestore_Affiliateplus_Model_Payment_Abstract
     */
    public function setPayment($value){
    	$this->_payment = $value;
    	return $this;
    }
    
    /**
     * get Payment model
     *
     * @return Magestore_Affiliateplus_Model_Payment
     */
    public function getPayment(){
    	return $this->_payment;
    }
    
	public function setStoreId($value){
		$this->_store_id = $value;
		return $this;
	}
	
	public function getStoreId(){
		return $this->_store_id;
	}
	
	public function getPaymentCode(){
		return $this->_code;
	}
	
	public function getFormBlockType(){
		return $this->_formBlockType;
	}
	
	public function getInfoBlockType(){
		return $this->_infoBlockType;
	}
	
	protected function _getPaymentConfig($code){
		return Mage::getStoreConfig(Magestore_Affiliateplus_Helper_Payment::XML_PAYMENT_METHODS.'/'.$this->getPaymentCode().'/'.$code,$this->getStoreId());
	}
	
	public function isEnable(){
		return $this->_getPaymentConfig('active');
	}
	
	public function calculateFee(){
		return 0;
	}
	
	public function getFeePrice($includeContainer = true){
		$store = Mage::app()->getStore($this->getStoreId());
		return $store->getBaseCurrency()->format($this->calculateFee(),array(),$includeContainer);
	}
	
	public function getLabel(){
		return $this->_getPaymentConfig('label');
	}
	
	public function loadPaymentMethodInfo(){
		return $this;
	}
	
	public function savePaymentMethodInfo(){
		return $this;
	}
	
	public function getInfoString(){
		return Mage::helper('affiliateplus/payment')->__('
			Method: %s \n
			Fee: %s \n
		',$this->getLabel(),$this->getFeePrice(false));
	}
	
	public function getInfoHtml(){
		$html = Mage::helper('affiliateplus/payment')->__('Method: ');
		$html .= '<strong>'.$this->getLabel().'</strong><br />';
		$html .= Mage::helper('affiliateplus/payment')->__('Fee: ');
		$html .= '<strong>'.$this->getFeePrice(true).'</strong><br />';
		return $html;
	}
    
    public function getPaymentHelper() {
        if ($class = $this->_getPaymentConfig('helper')) {
            return Mage::helper($class);
        }
        return false;
    }
}