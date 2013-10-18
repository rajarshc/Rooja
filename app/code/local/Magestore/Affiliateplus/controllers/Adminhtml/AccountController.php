<?php

class Magestore_Affiliateplus_Adminhtml_AccountController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('affiliateplus/account')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Accounts Manager'), Mage::helper('adminhtml')->__('Account Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Accounts'));
		$this->_initAction()
			->renderLayout();
	}
	
	public function gridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->loadLayout();
        $this->renderLayout();
    }
	
	public function customerAction()
	{
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->loadLayout();
        $this->getLayout()->getBlock('account.edit.tab.customer')
            ->setCustomers($this->getRequest()->getPost('rcustomers', null));
        $this->renderLayout();	
	}
	
	public function customerGridAction()
	{
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->loadLayout();
        $this->getLayout()->getBlock('account.edit.tab.customer')
            ->setCustomers($this->getRequest()->getPost('rcustomers', null));
        $this->renderLayout();		
	}
	
	public function changeCustomerAction()
	{
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$customer_id = $this->getRequest()->getParam('customer_id');
		$customer = Mage::getModel('customer/customer')
						->load($customer_id);
		$html = '';
		$html .= '<input type="hidden" id="map_customer_name" value="'.$customer->getName().'" />';
		$html .= '<input type="hidden" id="map_customer_email" value="'.$customer->getEmail().'" />';
		$html .= '<input type="hidden" id="map_customer_id" value="'.$customer->getId().'" />';
		$this->getResponse()->setHeader('Content-type', 'application/x-json');
		$this->getResponse()->setBody($html);
	}	
	
	
	public function transactionAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
		//$this->getLayout()->getBlock('account.edit.tab.transaction');
        $this->renderLayout();
	}

	
	public function transactionGridAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
        $this->renderLayout();
	}
	
	public function paymentAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
        $this->renderLayout();
	}
	
	public function paymentGridAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
        $this->renderLayout();
	}
	
	public function processpaymentAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$accountId = $this->getRequest()->getParam('id');
		$storeId = $this->getRequest()->getParam('store');
		$paymentRelease = Mage::getStoreConfig('affiliateplus/payment/payment_release', $storeId);
		$whoPayFees = Mage::getStoreConfig('affiliateplus/payment/who_pay_fees');
		$isBalanceIsGlobal = Mage::helper('affiliateplus')->isBalanceIsGlobal();
		
		$account = Mage::getModel('affiliateplus/account')->load($accountId)
					//->setBalanceIsGlobal($isBalanceIsGlobal)
					->setStoreId($storeId);
		
		if($whoPayFees == 'payer'){
			$amount = round($account->getBalance(), 2);
			$isPayerFees = 1;
		}
		else{
			$isPayerFees = 0;
			
			if($account->getBalance() >= 50)
				$amount = round($account->getBalance()-1, 2); // max fee is 1$ by api
			else
				$amount = round($account->getBalance()/1.02, 2); // fees 2% when payment by api
		}
		
		$paid = $account->getBalance();
		
		if($account->getBalance() >=  $paymentRelease){
			$data = array(array('amount' => $amount, 'email' => $account->getPaypalEmail()));
			$url = Mage::helper('affiliateplus/payment_paypal')->getPaymanetUrl($data);
			
			$http = new Varien_Http_Adapter_Curl();
			$http->write(Zend_Http_Client::GET, $url);
			$response = $http->read();
			$pos = strpos($response, 'ACK=Success');
			
			if($pos){ //create payment
				$storeIds = array();
				if(!$storeId){
					$stores = Mage::app()->getStores();
					foreach($stores as $store){
						$storeIds[] = $store->getId();
					}
				}else
					$storeIds = array($storeId);
					
				try{
                    $payment->setData('affiliateplus_account', $account);
					$payment = Mage::getModel('affiliateplus/payment')
								->setAccountId($accountId)
								->setAccountName($account->getName())
								->setPaymentMethod('paypal')
								->setAmount($account->getBalance())
								->setFee(round($amount*0.02, 2))
								->setRequestTime(now())
								->setStatus(3) //complete
								->setDescription(Mage::helper('affiliateplus')->__('Payment by API Paypal'))
								->setStoreIds(implode(',' , $storeIds))
								->setIsRequest(0)
								->setIsPayerFee($isPayerFees)
								->save();
					
					$paypalPayment = $payment->getPayment()
								->setEmail($account->getPaypalEmail())
								//->setTransactionId($data['transaction_id'])
								->savePaymentMethodInfo();

//					$account->setBalance(0)
//							->setTotalCommissionReceived($account->getTotalCommissionReceived() + $amount)
//							->setTotalPaid($account->getTotalPaid() + $paid)
//							->save();
							
					//send mail process payment to account
//					$payment->sendMailProcessPaymentToAccount();
					
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplus')->__('Paid sucessful'));
				}catch(Exception $e){
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('There is an error, please try again'));
				}
			}else{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('There is an error in paying out by paypal, please try again'));
			}
			$this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
		}
	}
	
	
	
	public function editAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$id     = $this->getRequest()->getParam('id');
		$storeId = $this->getRequest()->getParam('store');
		$isBalanceIsGlobal = Mage::helper('affiliateplus')->isBalanceIsGlobal();
		$account  = Mage::getModel('affiliateplus/account')
					//->setBalanceIsGlobal($isBalanceIsGlobal)
					->setStoreId($storeId)
					->load($id);
		
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Accounts'));
		if($account && $account->getId())
			$this->_title($this->__($account->getName()));
		else
			$this->_title($this->__('New Account'));
		
		if ($account->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$account->setData($data);
			}
			$customer = Mage::getModel('customer/customer')->load($account->getData('customer_id'));
			$account->setFirstname($customer->getFirstname())
				->setLastname($customer->getLastname());

			Mage::register('account_data', $account);

			$this->loadLayout();
			$this->_setActiveMenu('affiliateplus/account');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Account Manager'), Mage::helper('adminhtml')->__('Account Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Account News'), Mage::helper('adminhtml')->__('Account News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('affiliateplus/adminhtml_account_edit'))
				->_addLeft($this->getLayout()->createBlock('affiliateplus/adminhtml_account_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Account does not exist'));
			$this->_redirect('*/*/', array('store' => $storeId));
		}
	}
 
	public function newAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->editAction();
		// $this->_forward('edit');
	}
 
	public function saveAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		if ($data = $this->getRequest()->getPost()) {
			$accountId = $this->getRequest()->getParam('id');
			$storeId = $this->getRequest()->getParam('store');

			$customer = Mage::getModel('customer/customer')->load($data['customer_id']);
			
			$email = isset($data['email']) ? $data['email'] : '';
			if (!$accountId && !$customer->getId()){
				if (!$email || !strpos($email,'@')){
					Mage::getSingleton('adminhtml/session')->addError($this->__('Invalid email address'));
	                Mage::getSingleton('adminhtml/session')->setFormData($data);
	                $this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
	                return;
				}
				$customer = Mage::getResourceModel('customer/customer_collection')
					->addFieldToFilter('email',$email)
					->getFirstItem();
				if (!$customer || !$customer->getId()){
					try {
                        $websiteId = isset($data['associate_website_id']) ? $data['associate_website_id'] : null;
						$customer->setEmail($email)
							->setWebsiteId(Mage::app()->getWebsite($websiteId)->getId())
							->setGroupId($customer->getGroupId())
							->setFirstname($data['firstname'])
							->setLastname($data['lastname'])
							->setForceConfirmed(true);
						$password = $data['password'];
						if (!$password) $password = $customer->generatePassword();
						$customer->setPassword($password);
						$customer->save();
						//$customer->sendPasswordReminderEmail();
					} catch (Exception $e){
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		                Mage::getSingleton('adminhtml/session')->setFormData($data);
		                $this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
		                return;
					}
				} else {
					$existedAccount = Mage::getModel('affiliateplus/account')->loadByCustomerId($customer->getId());
					if ($existedAccount->getId()) $accountId = $existedAccount->getId();
					if ($data['password']){
						try {
                            $customer->setFirstname($data['firstname'])
                                ->setLastname($data['lastname']);
							$customer->changePassword($data['password']);
							$customer->sendPasswordReminderEmail();
						} catch (Exception $e){}
					}
				}
			}
			
			$address = $customer->getDefaultShippingAddress();
			
			if($address && $address->getId())
				$data['address_id'] = $address->getId();
			
			$beforeAccount = Mage::getModel('affiliateplus/account')->load($accountId);
			$beforeStatusIsDisabled = ($beforeAccount->getStatus() == 2) ? true : false;
			$unapproved = ($beforeAccount->getApproved() == 2) ? true : false;
			
			$account = Mage::getModel('affiliateplus/account');
			$account->setStoreId($storeId);
			$account->setData($data)->setId($accountId);
			
			
			try {
				//add event to before save 
		  		Mage::dispatchEvent('affiliateplus_adminhtml_before_save_account', array('post_data' => $data, 'account' => $account));
				//save customer info
				$customer->setFirstname($data['firstname'])
						->setLastname($data['lastname']);
				if ($email && strpos($email,'@'))						
					$customer->setEmail($email);
				$customer->save();
				
				$account->setName($customer->getName())
					->setCustomerId($customer->getId());
			
				if(!$accountId){
					$account->setIdentifyCode($account->generateIdentifyCode())
							->setCreatedTime(now())
							->setApproved(1)//approved
							;	
				}
				
				$account->save();
				
				if($accountId){
					if($account->isEnabled() && $beforeStatusIsDisabled && $unapproved){
						//send mail to approved account
						$account->sendMailToApprovedAccount();
					}
				}else{
					//send mail to new account
					$account->sendMailToNewAccount();
				}
				
				//add event after save
				Mage::dispatchEvent('affiliateplus_adminhtml_after_save_account', array('post_data' => $data, 'account' => $account));
				//ssss
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplus')->__('Account was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $account->getId(), 'store' => $storeId));
					return;
				}
				$this->_redirect('*/*/', array('store'=>$storeId));
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Unable to find account to save'));
        $this->_redirect('*/*/', array('store' => $storeId));
	}
 
	public function deleteAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$accountId =  $this->getRequest()->getParam('id');
		$storeId = $this->getRequest()->getParam('store');
		if($accountId > 0) {
			try {
				$model = Mage::getModel('affiliateplus/account');
				 
				$model->setId($accountId)
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Account was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $accountId, 'store'=>$storeId));
			}
		}
		$this->_redirect('*/*/', array('store'=>$storeId));
	}

    public function massDeleteAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $accountIds = $this->getRequest()->getParam('account');
		$storeId = $this->getRequest()->getParam('store');
        if(!is_array($accountIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select account(s)'));
        } else {
            try {
                foreach ($accountIds as $accountId) {
                    $account = Mage::getModel('affiliateplus/account')->load($accountId);
                    $account->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($accountIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('store'=>$storeId));
    }
	
    public function massStatusAction()
    {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $accountIds = $this->getRequest()->getParam('account');
		$storeId = $this->getRequest()->getParam('store');
		
        if(!is_array($accountIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select account(s)'));
        } else {
            try {
                foreach ($accountIds as $accountId) {
                    $account = Mage::getSingleton('affiliateplus/account')
						->setStoreId($storeId)
                        ->load($accountId);
					$beforeStatusIsDisabled = ($account->getStatus() == 2) ? true : false;
					$unapproved = ($account->getApproved() == 2) ? true : false;
					$account	->setStatus($this->getRequest()->getParam('status'))
								->setIsMassupdate(true)
								->save();
					if($account->isEnabled() && $beforeStatusIsDisabled && $unapproved){
						//send mail to approved account
						$account->sendMailToApprovedAccount();
					}
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($accountIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('store'=>$storeId));
    }
  
    public function exportCsvAction()
    {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $fileName   = 'account.csv';
        $content    = $this->getLayout()->createBlock('affiliateplus/adminhtml_account_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $fileName   = 'account.xml';
        $content    = $this->getLayout()->createBlock('affiliateplus/adminhtml_account_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}