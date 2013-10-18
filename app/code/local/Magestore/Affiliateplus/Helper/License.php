<?php

class Magestore_Affiliateplus_Helper_License extends Magestore_Magenotification_Helper_Data {
    
    const EXT_CODE = 'Affiliateplus-platinum';
    const EXT_ORG_CODE = 'Affiliateplus';
    const EXT_NAME = 'Affiliate Plus Platinum';
	
	public function checkLicense()
	{
		return $this->checkLicenseKey(self::EXT_CODE);
	}
	
	public function checkLicenseKey($extensionName)
	{
		$extensionName = self::EXT_ORG_CODE;
		if(strpos('a'.$extensionName,'Magestore')){
			$arrName = explode('_',$extensionName);
			$extensionName = isset($arrName[1]) ? $arrName[1] : str_replace('Magestore','',$extensionName);
		}
		$this->_extension = $extensionName;
		$baseUrl = Mage::getBaseUrl();
			//check passed key words
		foreach($this->PASSED_KEYWORDS as $passed_keyword){
			if(strpos($baseUrl,$passed_keyword))
				return true;	
		}
			
		$domain = $this->getDomain($baseUrl);
		$licensekey = Mage::getStoreConfig('magenotificationsecure/extension_keys/Magestore_'.$extensionName);
		$licensekey = trim($licensekey);
	
		//get cached data
		if($this->getDBLicenseKey() == $licensekey
			&& $this->getDBCheckdate() == date('Y-m-d')
			&& $this->getDBSumcode() == $this->getSumcode()){
			$responsecode = $this->getDBResponseCode();
		} else {
		//check license key online
			$responsecode = Mage::getSingleton('magenotification/keygen')->checkLicenseKey($licensekey,self::EXT_CODE,$domain);
		//save data into database
			$this->setDBLicenseKey($licensekey);
			$this->setDBCheckdate(date('Y-m-d'));
			$this->setDBResponseCode((int)$responsecode);	
			$this->setDBSumcode($this->getSumcode($responsecode));	
			$this->_saveLicenseLog();
		}
		//save error message
		$this->_errorMessage = $this->getLicenseKeyError($responsecode);
		return $this->isValidCode($responsecode);
	}	
	
	public function checkLicenseKeyFrontController($controller)
	{
		$extensionName = get_class($controller);
		if(!$this->checkLicense()){
			$request = $controller->getRequest();
			$request->initForward();	
			$request->setActionName('noRoute')->setDispatched(false);				
			return false;
		} else {
			return true;
		}
	}	

	// used for checking license in back-end controller
	public function checkLicenseKeyAdminController($controller)
	{	
		$_helper = Mage::helper('magenotification');
		if(!$_helper->checkLicenseKey(self::EXT_CODE)){
			$message = $_helper->getInvalidKeyNotice();
			$controller->loadLayout();
			$contentBlock = $controller->getLayout()->createBlock('core/text');
			$contentBlock->setText($message);
			$controller->getLayout()->getBlock('root')->setChild('content',$contentBlock);
			$controller->renderLayout();
			return false;
		}elseif((int)$_helper->getDBLicenseType() == Magestore_Magenotification_Model_Keygen::TRIAL_VERSION){
			Mage::getSingleton('core/session')->addNotice($this->__('You are using a trial version of %s extension. It will be expired on %s.',
														 self::EXT_NAME,
														 $_helper->getDBExpiredTime()
											));
		}
		return true;	
	}		
}