<?php

class Magestore_Affiliateplus_Model_Payment extends Mage_Core_Model_Abstract
{
	const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';
	const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';
	const XML_PATH_REQUEST_PAYMENT_EMAIL = 'affiliateplus/email/request_payment_email_template';
	const XML_PATH_PROCESS_PAYMENT_EMAIL = 'affiliateplus/email/process_payment_email_template';
	
	protected $_eventPrefix = 'affiliateplus_payment';
    protected $_eventObject = 'affiliateplus_payment';
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('affiliateplus/payment');
    }
    
    /**
     * Magestore_Affiliateplus_Model_Payment::getPayment()
     * 
     * @return Magestore_Affiliateplus_Model_Payment_Abstract
     */
    public function getPayment(){
    	if (!$this->hasData('payment')){
    		Mage::dispatchEvent($this->_eventPrefix.'_get_payment_before', $this->_getEventData());
    		
    		$paymentMethodCode = $this->getPaymentMethod();
    		$storeId = $this->getStoreId();
			try {
				$paymentMethod = Mage::helper('affiliateplus/payment')->getPaymentMethod($paymentMethodCode,$storeId);
				$paymentMethod->setPayment($this)->loadPaymentMethodInfo();
				$this->setData('payment',$paymentMethod);
				
				$payment_method = array('payment' => $paymentMethod);
				$params = array_merge($this->_getEventData(), $payment_method);
				Mage::dispatchEvent($this->_eventPrefix.'_get_payment_after', $params);
			} catch (Exception $e){
				
			}
    	}
    	return $this->getData('payment');
    }
    
    /**
     * Magestore_Affiliateplus_Model_Payment::addPaymentInfo()
     * 
     * @return Magestore_Affiliateplus_Model_Payment
     */
    public function addPaymentInfo(){
    	if (!$this->hasData('add_payment_info')){
    		
    		Mage::dispatchEvent($this->_eventPrefix.'_add_paymentinfo_before', $this->_getEventData());
    		
    		$paymentMethod = $this->getPayment();
    		if ($paymentMethod){
    			foreach ($paymentMethod->getData() as $key => $value)
    				$this->setData($paymentMethod->getPaymentCode().'_'.$key, $value);
	    		$this->setData('payment_method_label',$paymentMethod->getLabel());
	    		$this->setData('payment_method_info',$paymentMethod->getInfoString());
	    		$this->setData('payment_method_html',$paymentMethod->getInfoHtml());
	    		$this->setData('payment_fee',$paymentMethod->calculateFee());
				$this->setData('add_payment_info',true);
    		}
    		
    		Mage::dispatchEvent($this->_eventPrefix.'_add_paymentinfo_after', $this->_getEventData());
    	}
    	return $this;
    }
    
    protected function _beforeSave(){
    	$this->addPaymentInfo();
    	if ($this->getData('fee') == NULL) {
    		$this->setData('fee',$this->getData('payment_fee'));
        }
    	if (!$this->getData('store_ids')) {
    		$this->setData('store_ids',implode(',',array_keys(Mage::app()->getStores())));
        }
        // Apply tax when create Payment
        if (!$this->getId() && $this->getPaymentMethod() != 'credit') {
            $this->applyTax();
        }
        
        // send email for completed payment
        if ($this->getOrigData('status') < 3 && $this->getStatus() == 3 && !$this->getData('is_created_by_recurring')) {
            $this->sendMailProcessPaymentToAccount();
        }
        
        if ($this->getId() && $this->getOrigData('status') < 3 && $this->getStatus() == 3) {
            $this->addComment(Mage::helper('affiliateplus')->__('Complete Withdrawal'));
        }
        
        // change the account Balance
        if (!$this->getData('is_reduced_balance') && $this->getStatus() && $this->getStatus() < 4
            && ($this->getStatus() == 3 || Mage::getStoreConfig('affiliateplus/payment/reduce_balance'))
        ) {
            // reduce balance when create payment
            $account = $this->getAffiliateplusAccount();
            if ($account && $account->getId()) {
                try {
                    $account->setBalance($account->getBalance() - $this->getAmount() - $this->getTaxAmount())
                        ->setTotalPaid($account->getTotalPaid() + $this->getAmount() + $this->getTaxAmount());
                    $commissionReceived = $this->getAmount();
                    if (!$this->getIsPayerFee()) {
                        $commissionReceived -= $this->getFee();
                    }
                    $account->setTotalCommissionReceived($account->getTotalCommissionReceived() + $commissionReceived)
                        ->save();
                    $this->setData('is_reduced_balance', 1);
                } catch (Exception $e) {
                }
            }
        }
        if ($this->getData('is_reduced_balance')
            && $this->getStatus() == 4 && !$this->getData('is_refund_balance')
        ) {
            // cancel payment -> update affilate account balance
            $account = $this->getAffiliateplusAccount();
            if ($account && $account->getId()) {
                try {
                    $account->setBalance($account->getBalance() + $this->getAmount() + $this->getTaxAmount())
                        ->setTotalPaid($account->getTotalPaid() - $this->getAmount() - $this->getTaxAmount());
                    $commissionReceived = $this->getAmount();
                    if (!$this->getIsPayerFee()) {
                        $commissionReceived -= $this->getFee();
                    }
                    $account->setTotalCommissionReceived($account->getTotalCommissionReceived() - $commissionReceived)
                        ->save();
                    $this->setData('is_refund_balance', 1);
                } catch (Exception $e) {
                }
                if ($this->getId()) {
                    $this->addComment(Mage::helper('affiliateplus')->__('Cancel Withdrawal'));
                }
            }
        }
    	return parent::_beforeSave();
    }
    
    /**
     * Apply tax for this payment
     * 
     * @return Magestore_Affiliateplus_Model_Payment
     */
    public function applyTax() {
        if ($this->getData('applied_tax_calculation')) {
            return $this;
        }
        $helper = Mage::helper('affiliateplus/payment_tax');
        /* @var $helper Magestore_Affiliateplus_Helper_Payment_Tax */
        $taxAmount = $helper->getTaxAmount(
            $this->getAmount(),
            $this->getIsPayerFee() ? 0 : $this->getFee(),
            $this->getAffiliateplusAccount(),
            $this->_getStore()
        );
        $this->setData('tax_amount', $taxAmount)
            ->setData('amount_incl_tax', $this->getAmount() + $taxAmount);
        $this->setData('applied_tax_calculation', true);
        return $this;
    }
    
    public function _afterSave() {
        if ($this->isObjectNew()) {
            if ($this->getData('is_reduced_balance') && $this->getStatus() == 3) {
                $title = Mage::helper('affiliateplus')->__('Create and complete Withdrawal');
            } else {
                $title = Mage::helper('affiliateplus')->__('Create Withdrawal');
            }
            $this->addComment($title);
        } else if (!$this->getData('is_reduced_balance')
            && $this->getStatus() == 4
        ) {
            $this->addComment(Mage::helper('affiliateplus')->__('Cancel Withdrawal'));
        }
        return parent::_afterSave();
    }
    
    public function getAffiliateplusAccount() {
        if (!$this->hasData('affiliateplus_account')) {
            $account = Mage::getModel('affiliateplus/account')
                ->setStoreId($this->_getStore()->getId())
                ->load($this->getAccountId());
            $this->setData('affiliateplus_account', $account);
        }
        return $this->getData('affiliateplus_account');
    }
    
    public function hasWaitingPayment(){
    	$payments = $this->getCollection()
    		->addFieldToFilter('account_id',$this->getAccountId())
    		->addFieldToFilter('status',1)
    		->addFieldToFilter('store_ids',array('finset' => Mage::app()->getStore()->getId()));
    	return $payments->getSize();
    }
	
	public function getFrontendFee(){
		if($this->getIsPayerFee())
			$fee = 0;
		else
			$fee = $this->getFee();
		return $fee;
	}
	
	public function isRequest(){
		if($this->getIsRequest())
			return true;
		else
			return false;	
	}
	
	public function sendMailRequestPaymentToSales(){
		if(!Mage::getStoreConfig('affiliateplus/email/is_sent_email_sales_request_payment'))
			return $this;
			
		//set base currency
		$store = Mage::app()->getStore();
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());	
		$account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());		
		
		$sales = Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getId());
		
		$this->setAccountName($account->getName())
				->setAccountEmail($account->getEmail())
				->setBalanceFormated(Mage::helper('core')->currency($account->getBalance()))
				->setRequestPayment(Mage::helper('core')->currency($this->getAmount()))
				->setRequestDateFormated(Mage::helper('core')->formatDate($this->getRequestDate(),'medium'))
				->setSalesName($sales['name'])
				;
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
		$template = Mage::getStoreConfig(self::XML_PATH_REQUEST_PAYMENT_EMAIL, $store->getId());
		
        $sendTo = array(
            array(
                'email' => $sales['email'],
                'name'  => $sales['name'],
            )
        );
		
		$mailTemplate = Mage::getModel('core/email_template');
		
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$store->getId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_ADMIN_EMAIL_IDENTITY, $store->getId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'payment'  => $this,
						'store'  => $store,
                    )
                );
		}
		
		$translate->setTranslateInline(true);
		
		//set current currency
		$store->setCurrentCurrency($currentCurrency);
		return $this;
	}
	
	public function sendMailProcessPaymentToAccount(){
		$store = $this->_getStore();
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());	

		$account = $this->getAffiliateplusAccount();// Mage::getModel('affiliateplus/account')->load($this->getAccountId());
		$whoPayFees = Mage::getStoreConfig('affiliateplus/payment/who_pay_fees');
		
		if($whoPayFees == 'payer')
			$payAmount = $this->getAmount();
		else
			$payAmount = $this->getAmount() - $this->getFee();
		
		$this->addPaymentInfo()->setAccountName($account->getName())
				->setAccountEmail($account->getEmail())
				->setAccountPaypalEmail($account->getPaypalEmail())
				->setBalanceFormated(Mage::helper('core')->currency($account->getBalance()))
				->setRequestPayment(Mage::helper('core')->currency($this->getAmount()))
				->setPayPayment(Mage::helper('core')->currency($payAmount))
				->setFeeFormated(Mage::helper('core')->currency($this->getFrontendFee()))
				->setCreatedTimeFormated(Mage::helper('core')
											->formatDate(date('Y-m-d H:i:s',Mage::getSingleton('core/date')->timestamp(time()))),
											'medium'
										)
				->setRequestDateFormated(Mage::helper('core')->formatDate($this->getRequestDate(),'medium'))
				;
				
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
		$template = Mage::getStoreConfig(self::XML_PATH_PROCESS_PAYMENT_EMAIL,$store->getId());
		
        $sendTo = array(
            array(
                'email' => $account->getEmail(),
                'name'  => $account->getName(),
            )
        );
		$mailTemplate = Mage::getModel('core/email_template');
		 
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$store->getId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'payment'  => $this,
						'store'  => $store,
                    )
                );
		}
		
		$translate->setTranslateInline(true);
		//set current currency
		$store->setCurrentCurrency($currentCurrency);			
		
		return $this;	
	}
	
	protected function _getStore(){
		$storeIds = $this->getStoreIds();
		if(strpos($storeIds, ','))// mutil store
			$store = Mage::app()->getStore();
		else
			$store = Mage::app()->getStore(intval($storeIds)); // Mage::getModel('core/store')->load($storeIds);
		return $store;
	}
	
	public function isMultiStore(){
		$storeIds = $this->getStoreIds();
		if(strpos($storeIds, ','))// mutil store
			return true;
		else
			return false;
	}
    
    public function addComment($comment) {
        try {
            Mage::getModel('affiliateplus/payment_history')
                ->setData(array(
                    'payment_id'    => $this->getId(),
                    'status'        => $this->getData('status'),
                    'created_time'  => now(),
                    'description'   => $comment,
                ))
                ->setId(null)->save();
        } catch (Exception $e) {
        }
    }
    
    public function canRestore() {
        return $this->getData('payment_is_deleted');
    }
}
