<?php
class Magestore_Affiliateplus_Block_Account_Login extends Mage_Core_Block_Template
{
	/**
	 * get Core Session
	 *
	 * @return Mage_Core_Model_Session
	 */
	protected function _getCoreSession(){
		return Mage::getSingleton('core/session');
	}
	
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
    
    public function getUsername(){
    	if ($loginData = $this->getLoginFormData())
    		return $loginData['email'];
    	return null;
    }
    
    public function getLoginFormData(){
    	return $this->_getCoreSession()->getLoginFormData();
    }
    
    public function getRegisterUrl(){
    	return $this->getUrl('affiliateplus/account/register');
    }
    
    public function getRegisterDescription(){
    	return Mage::helper('affiliateplus/config')->getSharingConfig('register_description');
    }
    
    protected function _afterToHtml($html){
    	$this->_getCoreSession()->unsetData('login_form_data');
    	return parent::_afterToHtml($html);
    }
}