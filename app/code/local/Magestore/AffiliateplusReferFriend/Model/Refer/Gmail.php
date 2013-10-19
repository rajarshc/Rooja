<?php

class Magestore_AffiliateplusReferFriend_Model_Refer_Gmail extends Zend_Oauth_Consumer
{
	protected $_options = null;
	
	public function __construct(){
		$this->_config = new Zend_Oauth_Config;
		$this->_options = array(
			'consumerKey'       => $this->_getConsumerKey(),
			'consumerSecret'    => $this->_getConsumerSecret(),
			'signatureMethod'   => 'HMAC-SHA1',
			'version'           => '1.0',
			'requestTokenUrl'   => 'https://www.google.com/accounts/OAuthGetRequestToken',
			'accessTokenUrl'    => 'https://www.google.com/accounts/OAuthGetAccessToken',
			'authorizeUrl'      => 'https://www.google.com/accounts/OAuthAuthorizeToken'
		);
		$this->_config->setOptions($this->_options);
	}
	
	/**
	 * get Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	public function _getHelper(){
		return Mage::helper('affiliateplus/config');
	}
	
	protected function _getConsumerKey(){
		return $this->_getHelper()->getReferConfig('google_consumer_key');
	}
	
	protected function _getConsumerSecret(){
		return $this->_getHelper()->getReferConfig('google_consumer_secret');
	}
	
	public function setCallbackUrl($url){
		$this->_config->setCallbackUrl($url);
	}
	
	public function getOptions(){
		return $this->_options;
	}
	
	public function getCoreSession(){
		return Mage::getSingleton('core/session');
	}
	
	public function getGmailRequestToken(){
		return $this->getCoreSession()->getAffiliateGmailRequestToken();
	}
	
	public function setGmailRequestToken($token){
		$this->getCoreSession()->setAffiliateGmailRequestToken($token);
		return $this;
	}
	
	public function isAuth(){
		$requestToken = $this->getGmailRequestToken();
		$request = Mage::app()->getRequest();
		if ($requestToken && $request->getParam('oauth_token') && $request->getParam('oauth_verifier'))
			return true;
		return false;
	}
	
	public function getAuthUrl(){
		$this->setCallbackUrl(Mage::getUrl('*/*/gmail'));
		$token = $this->getRequestToken(array('scope' => 'https://www.google.com/m8/feeds/'));
		$this->setGmailRequestToken(serialize($token));
		return $this->getRedirectUrl();
	}
}