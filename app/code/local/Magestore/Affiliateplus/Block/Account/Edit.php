<?php
class Magestore_Affiliateplus_Block_Account_Edit extends Mage_Core_Block_Template
{
	protected function _getSession(){
		return Mage::getSingleton('affiliateplus/session');
	}
    
    public function customerLoggedIn(){
    	return Mage::helper('affiliateplus/account')->customerLoggedIn();
    }
    
    public function isLoggedIn(){
    	return $this->_getSession()->isLoggedIn();
    }
    
    public function getCustomer(){
    	return Mage::getSingleton('customer/session')->getCustomer();
    }
    
    public function getFormData($field=null){
    	$formData = $this->_getSession()->getAffiliateFormData();
		if($field)
			return isset($formData[$field]) ? $formData[$field] : null;
		return $formData;
    }
    
    public function unsetFormData(){
    	$this->_getSession()->unsetData('affiliate_form_data');
    	return $this;
    }
    
    public function getAccount(){
    	return $this->_getSession()->getAccount();
    }
    
    public function requiredAddress(){
    	return Mage::helper('affiliateplus/config')->getSharingConfig('required_address');
    }
    
    public function requiredPaypal(){
    	return Mage::helper('affiliateplus/config')->getSharingConfig('required_paypal');
    }
    
    public function getFormattedAddress(){
		$account = $this->getAccount();
		return Mage::getModel('customer/address')->load($account->getAddressId())->format('html');
	}
    
    public function getAddress() {	
		$address = Mage::getModel('customer/address');
		$formData = $this->getFormData();
		if(isset($formData['account'])){
			$address->setData($formData['account']);
		} elseif($this->isLoggedIn()){
			$address->load($this->getAccount()->getAddressId());
		} elseif($this->customerLoggedIn()){
			if(!$address->getFirstname())
				$address->setFirstname($this->getCustomer()->getFirstname());
			if(!$address->getLastname())
				$address->setLastname($this->getCustomer()->getLastname());
		}
		return $address;
    }
    
    public function customerHasAddresses(){
    	return $this->getCustomer()->getAddressesCollection()->getSize();
    }
    
    public function getAddressesHtmlSelect($type){
        if ($this->customerLoggedIn()){
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value'=>$address->getId(),
                    'label'=>$address->format('oneline')
                );
            }

            $addressId = $this->getAddress()->getId();
            if (empty($addressId)) {
				$address = $this->getCustomer()->getPrimaryBillingAddress();
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange=lsRequestTrialNewAddress(this.value);')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }
    
    public function getCountryHtmlSelect($type){
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());

        return $select->getHtml();
    }
    
    public function getRegionCollection(){
        if (!$this->_regionCollection){
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter($this->getAddress()->getCountryId())
                ->load();
        }
        return $this->_regionCollection;
    }
    
    public function getRegionHtmlSelect($type){
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[region]')
            ->setId($type.':region')
            ->setTitle(Mage::helper('checkout')->__('State/Province'))
            ->setClass('required-entry validate-state')
            ->setValue($this->getAddress()->getRegionId())
            ->setOptions($this->getRegionCollection()->toOptionArray());

        return $select->getHtml();
    }
    
    public function getCountryCollection(){
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }
    
    public function getCountryOptions(){
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }
    
    protected function _afterToHtml($html){
    	$this->unsetFormData();
    	return parent::_afterToHtml($html);
    }
    
    public function getCheckCustomerEmailUrl(){
    	return $this->getUrl('affiliateplus/account/checkemailregister');
    }
}