<?php
class Magestore_Affiliateplus_Block_Payment_Paypal extends Magestore_Affiliateplus_Block_Payment_Form
{
	public function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('affiliateplus/payment/paypal.phtml');
		return $this;
    }
    
    public function getAcount(){
    	return Mage::getSingleton('affiliateplus/session')->getAccount();
    }
    
    public function isVerified($accountId,$email){
        $verifyCollection = Mage::getModel('affiliateplus/payment_verify')
            ->getCollection()
            ->addFieldToFilter('account_id',$accountId)
            ->addFieldToFilter('payment_method','paypal')
            ->addFieldToFilter('field',$email)
            ->addFieldToFilter('verified','1')
            ;
        if($verifyCollection->getSize())
            return true;
        return false;
    }
}