<?php
class Magestore_Affiliateplus_AccountController extends Mage_Core_Controller_Front_Action
{
	/**
	 * get Affiliateplus session
	 *
	 * @return Magestore_Affiliateplus_Model_Session
	 */
	protected function _getSession(){
		return Mage::getSingleton('affiliateplus/session');
	}
	
	/**
	 * get Core Session
	 *
	 * @return Mage_Core_Model_Session
	 */
	protected function _getCoreSession(){
		return Mage::getSingleton('core/session');
	}
	
	/**
	 * get Customer session
	 *
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getCustomerSession(){
		return Mage::getSingleton('customer/session');
	}
	
    public function editAction(){
    	if (Mage::helper('affiliateplus/account')->accountNotLogin())
    		return $this->_redirect('affiliateplus/account/login');
		
    	$session = $this->_getSession();
    	$formData = $session->getCustomer()->getData();
    	$formData['account'] = $session->getAccount()->getData();
    	$formData['account_name'] = $session->getCustomer()->getName();
    	$formData['paypal_email'] = $session->getAccount()->getPaypalEmail();
    	$formData['notification'] = $session->getAccount()->getNotification();
    	$session->setAffiliateFormData($formData);
    	
    	$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('affiliateplus')->__('Account Settings'));
		$this->renderLayout();
    }
    
    public function editPostAction(){
    	if (Mage::helper('affiliateplus/account')->accountNotLogin())
    		return $this->_redirect('affiliateplus/account/login');
    	if(!$this->getRequest()->isPost())
    		return $this->_redirect('affiliateplus/account/edit');
    	$session = $this->_getSession();
    	$coreSession = $this->_getCoreSession();
    	$customerSession = $this->_getCustomerSession();
    	
    	$data = $this->_filterDates($this->getRequest()->getPost(), array('dob'));
    	
    	$customer = $customerSession->getCustomer();
		$customer->addData($data);
    	$customer->setFirstname($data['firstname']);
    	$customer->setLastname($data['lastname']);
    	
        $errors = array();
    	if (isset($data['account_address_id']) && $data['account_address_id']){
    		$address = Mage::getModel('customer/address')->load($data['account_address_id']);
    	} else {
    		$address_data = $this->getRequest()->getPost('account');
    		$address = Mage::getModel('customer/address')
				->setData($address_data)
				->setParentId($customer->getId())
				->setFirstname($customer->getFirstname())
				->setLastname($customer->getLastname())
				->setId(null);
			$customer->addAddress($address);
			$errors = $address->validate();
    	}
    	if (!is_array($errors)) $errors = array();
    	if ($this->getRequest()->getParam('change_password')){
			$currPass   = $this->getRequest()->getPost('current_password');
			$newPass	= $this->getRequest()->getPost('password');
			$confPass   = $this->getRequest()->getPost('confirmation');

			$oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
			if (Mage::helper('core/string')->strpos($oldPass, ':'))
				list($_salt, $salt) = explode(':', $oldPass);
			else
				$salt = false;

			if ($customer->hashPassword($currPass, $salt) == $oldPass) {
				if (strlen($newPass)) {
					$customer->setPassword($newPass);
					$customer->setConfirmation($confPass);
				} else {
					$errors[] = $this->__('New password field cannot be empty.');
				}
			} else {
				$errors[] = $this->__('Invalid current password');
			}
		}
    	try {
    		$validationCustomer = $customer->validate();
    		if (is_array($validationCustomer))
    			$errors = array_merge($validationCustomer,$errors);
    		$validationResult = (count($errors) == 0);
    		
    		if (true === $validationResult){
    			$customer->save();
    			if (!$address->getId())
    				$address->save();
    		}else {
    			foreach ($errors as $error)
    				$coreSession->addError($error);
    			$formData = $this->getRequest()->getPost();
    			$formData['account_name'] = $customer->getName();
    			$formData['account']['address_id'] = isset($formData['account_address_id']) ? $formData['account_address_id'] : '';
    			$session->setAffiliateFormData($formData);
    			return $this->_redirect('affiliateplus/account/edit');
    		}
    	}catch (Exception $e){
    		$coreSession->addError($e->getMessage());
    		$formData = $this->getRequest()->getPost();
			$formData['account_name'] = $customer->getName();
			$formData['account']['address_id'] = isset($formData['account_address_id']) ? $formData['account_address_id'] : '';
			$session->setAffiliateFormData($formData);
			return $this->_redirect('affiliateplus/account/edit');
    	}
    	$account = Mage::getModel('affiliateplus/account')
    		->setStoreId(Mage::app()->getStore()->getId())
    		->load($session->getAccount()->getId());
    	try {
    		$account->setData('name',$customer->getName())
    			->setData('paypal_email',$data['paypal_email'])
				->setData('notification',isset($data['notification']) ? 1 : 0);
    		if ($address)
    			$account->setData('address_id',$address->getId());
    		$account->save();
    		$successMessage = $this->__('Your account information has been saved!');
    		$coreSession->addSuccess($successMessage);
    		return $this->_redirect('affiliateplus/account/edit');
    	}catch (Exception $e){
    		$coreSession->addError($e->getMessage());
    		$formData = $this->getRequest()->getPost();
			$formData['account_name'] = $customer->getName();
			$formData['account']['address_id'] = isset($formData['account_address_id']) ? $formData['account_address_id'] : '';
			$session->setAffiliateFormData($formData);
			return $this->_redirect('affiliateplus/account/edit');
    	}
    }
    
    public function loginAction(){
    	if (Mage::helper('affiliateplus/account')->isLoggedIn()){
    		$this->_getCoreSession()->addSuccess(Mage::helper('affiliateplus')->__('You are logged in!'));
    		return $this->_redirect('affiliateplus/index/index');
    	}elseif (Mage::helper('affiliateplus/account')->isRegistered()){
    		$this->_getCoreSession()->addError(Mage::helper('affiliateplus')->__('Your affiliate account is blocked. Please contact us to get our help.'));
    		return $this->_redirect('affiliateplus/index/index');
    	}
    	if ($this->getRequest()->getServer('HTTP_REFERER'))
    		$this->_getSession()->setDirectUrl($this->getRequest()->getServer('HTTP_REFERER'));
    	$this->loadLayout();
    	$this->getLayout()->getBlock('head')->setTitle(Mage::helper('affiliateplus')->__('Affiliate login'));
    	$this->renderLayout();
    }
    
    public function loginPostAction(){
    	if (!$this->getRequest()->isPost() || $this->_getCustomerSession()->isLoggedIn())
    		return $this->_redirect('affiliateplus/account/login');
    	//Login to affiliate system
    	$login = $this->getRequest()->getPost('login');
    	if (!empty($login['username']) && !empty($login['password'])){
    		try {
    			$this->_getCustomerSession()->login($login['username'],$login['password']);
    			if ($this->_getSession()->getDirectUrl()){
    				$this->_redirectUrl($this->_getSession()->getDirectUrl());
    				$this->_getSession()->setDirectUrl(null);
    				return ;
    			}
    			return $this->_redirect('affiliateplus/index/index');
    		}catch (Mage_Core_Exception $e){
    			switch ($e->getCode()){
                    case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                        $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                        break;
                    case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                        $message = $e->getMessage();
                        break;
                    default:
                        $message = $e->getMessage();
                }
                $this->_getCoreSession()->addError($message);
                $this->_getCoreSession()->setLoginFormData(array('email' => $login['username']));
    		}
    	}else {
    		$this->_getCoreSession()->addError($this->__('Login and password are required.'));
    	}
    	
    	return $this->_redirect('affiliateplus/account/login');
    }
    
    public function logoutAction(){
    	$this->_getCustomerSession()->logout()
    		->setBeforeAuthUrl(Mage::getUrl());
    	$this->_redirect('customer/account/logoutSuccess');
    }
    
    public function registerAction(){
    	if (Mage::helper('affiliateplus/account')->isRegistered()){
    		if (Mage::helper('affiliateplus/account')->isLoggedIn()){
    			$this->_getCoreSession()->addSuccess(Mage::helper('affiliateplus')->__('You are logged in!'));
    			return $this->_redirect('affiliateplus/index/index');
    		}else{
    			//$this->_getCoreSession()->addError(Mage::helper('affiliateplus')->__('You had an affiliate account. Please login to system!'));
    			return $this->_redirect('affiliateplus/account/login');
    		}
    	}
    	if ($this->_getCustomerSession()->isLoggedIn()){
    		$formData = array('account_name' => $this->_getCustomerSession()->getCustomer()->getName());
    		$this->_getSession()->setAffiliateFormData($formData);
    	}
    	$this->loadLayout();
    	$this->getLayout()->getBlock('head')->setTitle(Mage::helper('affiliateplus')->__('Signup Affiliate Account'));
    	$this->renderLayout();
    }
    
    public function createPostAction(){
    	if(!$this->getRequest()->isPost())
    		return $this->_redirect('affiliateplus/account/register');
    	
    	$session = $this->_getSession();
    	$coreSession = $this->_getCoreSession();
    	$customerSession = $this->_getCustomerSession();
    	
		$address = '';
		
    	if ($session->isRegistered()) {
    		//Registered
    		//$coreSession->addError(Mage::helper('affiliateplus')->__('You had an affiliate account. Please login to system!'));
    		return $this->_redirect('affiliateplus/account/login');
    	}elseif ($customerSession->isLoggedIn()) {
    		$data = $this->_filterDates($this->getRequest()->getPost(), array('dob'));
    		//Check Captcha Code
    		$captchaCode = $coreSession->getData('register_account_captcha_code');
    		if ($captchaCode != $data['account_captcha']){
    			$session->setAffiliateFormData($this->getRequest()->getPost());
    			$coreSession->addError(Mage::helper('affiliateplus')->__('Please enter a correct verification code!'));
    			return $this->_redirect('affiliateplus/account/register');
    		}
    		//Customer not register affiliate account
    		$customer = $customerSession->getCustomer();
    		if (isset($data['account_address_id']) && $data['account_address_id']){
    			$address = Mage::getModel('customer/address')->load($data['account_address_id']);
    		}elseif (Mage::helper('affiliateplus/config')->getSharingConfig('required_address')){
    			$address_data = $this->getRequest()->getPost('account');
    			$address = Mage::getModel('customer/address')
					->setData($address_data)
					->setParentId($customer->getId())
					->setFirstname($customer->getFirstname())
					->setLastname($customer->getLastname())
					->setId(null);
				$customer->addAddress($address);
				$errors = $address->validate();
				if (!is_array($errors))
					$errors = array();
				try {
					$validationCustomer = $customer->validate();
					if (is_array($validationCustomer))
						$errors = array_merge($validationCustomer,$errors);
					$validationResult = (count($errors) == 0);
					if (true === $validationResult){
						$customer->save();
						$address->save();
					}else {
						foreach ($errors as $error)
							$coreSession->addError($error);
						$formData = $this->getRequest()->getPost();
						$formData['account_name'] = $customer->getName();
						$session->setAffiliateFormData($formData);
						return $this->_redirect('affiliateplus/account/register');
					}
				}catch (Exception $e){
					$coreSession->addError($e->getMessage());
					$formData = $this->getRequest()->getPost();
					$formData['account_name'] = $customer->getName();
					$session->setAffiliateFormData($formData);
					return $this->_redirect('affiliateplus/account/register');
				}
    		}
    	}else {
    		$data = $this->_filterDates($this->getRequest()->getPost(), array('dob'));
    		//Check Captcha Code
    		$captchaCode = $coreSession->getData('register_account_captcha_code');
    		if ($captchaCode != $data['account_captcha']){
    			$session->setAffiliateFormData($this->getRequest()->getPost());
    			$coreSession->addError(Mage::helper('affiliateplus')->__('Please enter a correct verification code!'));
    			return $this->_redirect('affiliateplus/account/register');
    		}
    		
    		//Create new customer and affiliate account
    		$customerSession->setEscapeMessages(true);
    		$errors = array();
    		if (!$customer = Mage::registry('current_customer')){
    			$customer = Mage::getModel('customer/customer')->setId(null);
    		}
    		
    		foreach (Mage::getConfig()->getFieldset('customer_account') as $code=>$node)
				if ($node->is('create') && isset($data[$code])) {
					if ($code == 'email')
						$data[$code] = trim($data[$code]);
					$customer->setData($code, $data[$code]);
				}
			
			$customer->getGroupId();
			
			if (Mage::helper('affiliateplus/config')->getSharingConfig('required_address')){
				$address_data = $this->getRequest()->getPost('account');
				$address = Mage::getModel('customer/address')
					->setData($address_data)
					->setFirstname($customer->getFirstname())
					->setLastname($customer->getLastname())				
					->setIsDefaultBilling(true)
					->setIsDefaultShipping(true)
					->setId(null);
				$customer->addAddress($address);
				
				$errors = $address->validate();
			}
			if (!is_array($errors))
				$errors = array();
			
			try {
				$validationCustomer = $customer->validate();
				if (is_array($validationCustomer))
					$errors = array_merge($validationCustomer,$errors);
				$validationResult = (count($errors) == 0);
				if (true === $validationResult){
					$customer->save();
					if ($address)
						$address->save();
					if ($customer->isConfirmationRequired()){
						$customer->sendNewAccountEmail(
                            'confirmation',
                            $customerSession->getBeforeAuthUrl(),
                            Mage::app()->getStore()->getId()
                        );
					}else {
						$customerSession->setCustomerAsLoggedIn($customer);
					}
				}else {
					foreach ($errors as $error)
						$coreSession->addError($error);
					$formData = $this->getRequest()->getPost();
					$formData['account_name'] = $customer->getName();
					$session->setAffiliateFormData($formData);
					return $this->_redirect('affiliateplus/account/register');
				}
			}catch (Exception $e){
				$coreSession->addError($e->getMessage());
				$formData = $this->getRequest()->getPost();
				$formData['account_name'] = $customer->getName();
				$session->setAffiliateFormData($formData);
				return $this->_redirect('affiliateplus/account/register');
			}
    	}
    	try {
    		$account = Mage::getModel('affiliateplus/account')
    			->setData('customer_id',$customer->getId())
    			//->setData('address_id',$address->getId())
    			->setData('name',$customer->getName())
				->setData('email',$customer->getEmail())
    			->setData('paypal_email',$data['paypal_email'])
    			->setData('created_time',now())
    			->setData('balance',0)
    			->setData('total_commission_received',0)
    			->setData('total_paid',0)
    			->setData('total_clicks',0)
    			->setData('unique_clicks',0)
    			->setData('status',1)
    			->setData('status_default',1)
    			->setData('approved_default',1)
    			->setData('notification',$this->getRequest()->getPost('notification'))
    			;
    		$successMessage = Mage::helper('affiliateplus/config')->getSharingConfig('notification_after_signing_up');
    		if (Mage::helper('affiliateplus/config')->getSharingConfig('need_approved')){
    			$account->setData('status',2);
				$account->setData('approved',2);
				$coreSession->setData('has_been_signup',true);
				$successMessage .= ' ' . $this->__('We are checking your personal information before approving and will inform you later.');
			}
    		if ($address)
    			$account->setData('address_id',$address->getId());
    		$account->setData('identify_code',$account->generateIdentifyCode());
    		$account->setStoreId(Mage::app()->getStore()->getId())->save();
    		
    		//send email
    		$account->sendMailToNewAccount();
            $account->sendNewAccountEmailToAdmin();
            
    		//add success
    		$coreSession->addSuccess($successMessage);
    		return $this->_redirect('affiliateplus/index/index');
    	}catch (Exception $e){
    		$coreSession->addError($e->getMessage());
    		$formData = $this->getRequest()->getPost();
			$formData['account_name'] = $customer->getName();
			$session->setAffiliateFormData($formData);
			return $this->_redirect('affiliateplus/account/register');
    	}
    }
    
    public function imagecaptchaAction(){
    	require_once(Mage::getBaseDir('lib') . DS .'captcha'. DS .'class.simplecaptcha.php');
		$config['BackgroundImage'] = Mage::getBaseDir('lib') . DS .'captcha'. DS . "white.png";
		$config['BackgroundColor'] = "FF0000";
		$config['Height']=30;
		$config['Width']=100;
		$config['Font_Size']=23;
		$config['Font']= Mage::getBaseDir('lib') . DS .'captcha'. DS . "ARLRDBD.TTF";
		$config['TextMinimumAngle']=15;
		$config['TextMaximumAngle']=30;
		$config['TextColor']='2B519A';
		$config['TextLength']=4;
		$config['Transparency']=80;
		$captcha = new SimpleCaptcha($config);
		$this->_getCoreSession()->setData('register_account_captcha_code',$captcha->Code);
    }
    
    public function refreshcaptchaAction(){
    	$result = Mage::getModel('core/url')->getUrl('*/*/imageCaptcha', array('tms' => time()));
		echo $result;
    }
    
    public function checkemailregisterAction(){
		$email_address = $this->getRequest()->getParam('email_address');
		$isvalid_email = true;
		if (!Zend_Validate::is(trim($email_address), 'EmailAddress')) {
			$isvalid_email = false;
		}
		if($isvalid_email){
			$error = false;
			$email = Mage::getModel('customer/customer')->getCollection()
				->addAttributeToFilter('email',$email_address)
				->getFirstItem();
			if($email->getId()) {
				$error = true;
			} 
			if($error != '') {
				$html = "<div class='error-msg'>".$this->__('The email %s belongs to a customer. If it is your email address, you can use it to <a href="%s">login</a> our system.',$email_address,Mage::getUrl('*/*/login'))."</div>";
				$html .= '<input type="hidden" id="is_valid_email" value="0"/>';
			} else{
				$html = "<div class='success-msg'>".$this->__('You can use this email address.')."</div>";
				$html .= '<input type="hidden" id="is_valid_email" value="1"/>';			
			}
		} else {
			$html = "<div class='error-msg'>".$this->__('Invalid email address.')."</div>";
			$html .= '<input type="hidden" id="is_valid_email" value="1"/>';
		}
		$this->getResponse()->setBody($html);
	}
}