<?php

class Magestore_AffiliateplusReferFriend_Model_Refer_Hotmail
{
	protected $_tokens = array();
	
	/**
	 * get Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	public function _getHelper(){
		return Mage::helper('affiliateplus/config');
	}
	
	protected function _getClientId(){
		return $this->_getHelper()->getReferConfig('hotmail_client_id');
	}
	
	protected function _getClientSecret(){
		return $this->_getHelper()->getReferConfig('hotmail_client_secret');
	}
	
	/**
	 * get Return back URL
	 *
	 * @return string
	 */
	public function getBackUrl(){
		return Mage::getUrl('*/*/hotmail');
	}
	
	public function getTokenUrl($code){
		return sprintf("https://oauth.live.com/token?client_id=%s&redirect_uri=%s&client_secret=%s&code=%s&grant_type=authorization_code",
				$this->_getClientId(),
				$this->getBackUrl(),
				$this->_getClientSecret(),
				$code
			);
	}
	
	public function getToken($code){
		if (isset($this->_tokens[$code])) return $this->_tokens[$code];
		try {
			$url = $this->getTokenUrl($code);
			$httpClient = new Zend_Http_Client($url);
			$response = $httpClient->request(Zend_Http_Client::GET);
			$body = $response->getBody();
			$data = Zend_Json::decode($body);
			$this->_tokens[$code] = isset($data['access_token']) ? $data['access_token'] : false;
		} catch (Exception $e){
			$this->_tokens[$code] = false;
		}
		return $this->_tokens[$code];
	}
	
	public function isAuth(){
		$request = Mage::app()->getRequest();
		if ($code = $request->getParam('code'))
			if ($this->getToken($code))
				return true;
		return false;
	}
	
	public function getAuthUrl(){
		return sprintf("https://oauth.live.com/authorize?client_id=%s&scope=wl.basic,wl.emails&response_type=code&redirect_uri=%s",
				$this->_getClientId(),
				$this->getBackUrl()
			);
	}
	
	public function getContactsData(){
		try {
			$code = Mage::app()->getRequest()->getParam('code');
			$url = "https://apis.live.net/v5.0/me/contacts?pretty=false&access_token=".$this->getToken($code);
			return $this->getResponse($url);
		} catch (Exception $e){}
		return array();
	}
	
	public function getResponse($url){
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			curl_close($ch);
			return Zend_Json::decode($response);
		} catch (Exception $e){
			$httpClient = new Zend_Http_Client($url);
			$response = $httpClient->request(Zend_Http_Client::GET);
			$body = $response->getBody();
			return Zend_Json::decode($body);
		}
		return array();
	}
}