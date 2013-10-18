<?php

class Magestore_Affiliatepluscoupon_Model_Coupon extends Mage_Core_Model_Abstract
{
	protected $_accountId;
	
	public function _construct(){
        parent::_construct();
        $this->_init('affiliatepluscoupon/coupon');
    }
    
    public function setCurrentAccountId($accountId){
    	$this->_accountId = $accountId;
    	return $this;
    }
    
    public function loadByProgram($program = '0'){
    	if (is_object($program))
    		$program = $program->getId();
   		$accountId = $this->_accountId ? $this->_accountId : $this->getAccountId();
    	$coupon = $this->getCollection()
    		->addFieldToFilter('account_id',$accountId)
    		->addFieldToFilter('program_id',$program)
    		->getFirstItem();
   		if ($coupon && $coupon->getId())
   			$this->setData($coupon->getData());
  		$this->setAccountId($accountId)
		  	->setProgramId($program);
   		return $this;
    }
    
    public function getAccountByCoupon($couponCode){
    	$this->load($couponCode,'coupon_code');
    	$account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());
    	return $account
			->setCouponCode($couponCode)
			->setCouponPid($this->getProgramId());
    }
}