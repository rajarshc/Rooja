<?php

class Magestore_Affiliateplus_Helper_Cookie extends Mage_Core_Helper_Abstract
{
	protected $_affiliateInfo = null;
    
    protected $_numberOrdered = null;
    
	public function getAffiliateInfo(){
		if (!is_null($this->_affiliateInfo)) return $this->_affiliateInfo;
		$info = array();
        
        // Check Life-Time sales commission
        if (Mage::helper('affiliateplus/config')->getCommissionConfig('life_time_sales')) {
            $tracksCollection = Mage::getResourceModel('affiliateplus/tracking_collection');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer && $customer->getId()) {
                $tracksCollection->getSelect()
                    ->where("customer_id = {$customer->getId()} OR customer_email = ?",
                        $customer->getEmail());
            } else {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $tracksCollection->addFieldToFilter('customer_email', $quote->getCustomerEmail());
            }
            $track = $tracksCollection->getFirstItem();
            if ($track && $track->getId()) {
                $account = Mage::getModel('affiliateplus/account')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($track->getAccountId());
                $info[$account->getIdentifyCode()] = array(
                    'index' => 1,
                    'code'  => $account->getIdentifyCode(),
                    'account'   => $account,
                );
                $this->_affiliateInfo = $info;
                return $this->_affiliateInfo;
            }
        }
        
		$cookie = Mage::getSingleton('core/cookie');
		$map_index = $cookie->get('affiliateplus_map_index');
		
		for($i=$map_index; $i>0 ; $i--){
			$accountCode = $cookie->get("affiliateplus_account_code_$i");
			$account = Mage::getModel('affiliateplus/account')->setStoreId(Mage::app()->getStore()->getId())->loadByIdentifyCode($accountCode);
			if ($account->getId()
				&& $account->getStatus() == 1
				&& $account->getId() != Mage::helper('affiliateplus/account')->getAccount()->getId()){
				$info[$accountCode] = array(
					'index'	=> $i,
					'code'	=> $accountCode,
					'account'	=> $account,
				);
			}
		}
		
		$infoObj = new Varien_Object(array(
			'info'	=> $info,
		));
		Mage::dispatchEvent('affiliateplus_get_affiliate_info',array(
			'cookie'	=> $cookie,
			'info_obj'	=> $infoObj,
		));
		
		$this->_affiliateInfo = $infoObj->getInfo();
		return $this->_affiliateInfo;
	}
    
    public function getNumberOrdered()
    {
        if (is_null($this->_numberOrdered)) {
            $orderCollection = Mage::getResourceModel('sales/order_collection');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer && $customer->getId()) {
                $orderCollection->addFieldToFilter('customer_id', $customer->getId());
            } else {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $orderCollection->addFieldToFilter('customer_email', $quote->getCustomerEmail());
            }
            $this->_numberOrdered = $orderCollection->getSize();
        }
        return $this->_numberOrdered;
    }
}
