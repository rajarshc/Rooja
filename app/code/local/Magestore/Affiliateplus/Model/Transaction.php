<?php

class Magestore_Affiliateplus_Model_Transaction extends Mage_Core_Model_Abstract
{
	const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';
	const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';
	const XML_PATH_NEW_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/new_transaction_account_email_template';
	const XML_PATH_NEW_TRANSACTION_SALES_EMAIL = 'affiliateplus/email/new_transaction_sales_email_template';
	const XML_PATH_UPDATED_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/updated_transaction_account_email_template';
    
    const XML_PATH_REDUCE_TRANSACTION_ACOUNT_EMAIL = 'affiliateplus/email/reduce_commission_account_email_template';

    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplus/transaction');
    }
    
    public function complete(){
        if ($this->canRestore()) return $this;
    	if (!$this->getId()) return $this;
    	if ($this->getStatus() != '2') return $this;
    	// Add commission for affiliate account
    	$account = Mage::getModel('affiliateplus/account')
    		->setStoreId($this->getStoreId())
    		->load($this->getAccountId());
    	try {
			$commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
    		$account->setBalance($account->getBalance() + $commission)//$this->getCommission())
    			->save();
				
    		$this->setStatus('1')->save();
			
			//update balance tier affiliate
			Mage::dispatchEvent('affiliateplus_complete_transaction',array('transaction' => $this));
			
	    	// Send email to affiliate account
	    	$this->sendMailUpdatedTransactionToAccount(true);
    	} catch (Exception $e){
    		
    	}
    	return $this;
    }
    
    public function hold() {
        if ($this->canRestore()) return $this;
        if (!$this->getId()) return $this;
        if ($this->getStatus() != '2') return $this;
        // Hold transaction 
        try {
            $this->setStatus('4')
                ->setHoldingFrom(now())
                ->save();
        } catch (Exception $e) {
        }
        return $this;
    }
    
    public function unHold() {
        if ($this->canRestore()) return $this;
        if (!$this->getId()) return $this;
        if ($this->getStatus() != '4') return $this;
        // Un hold and complete transaction
        $this->setStatus('2')->complete();
        return $this;
    }
    
    public function reduce($creditmemo) {
        if ($this->canRestore()) return $this;
        if (!$this->getId() || $this->getStatus() != '1' || !$creditmemo->getId()) {
            return $this;
        }
        $reducedIds = explode(',', $this->getCreditmemoIds());
        if (is_array($reducedIds) && in_array($creditmemo->getId(), $reducedIds)) {
            return $this;
        }
        $reducedIds[] = $creditmemo->getId();
        // calculate reduced commission
        $reduceCommission = 0;
        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }
            $orderItem = $item->getOrderItem();
            $orderItemCommission = (float)$orderItem->getAffiliateplusCommission();
            $orderItemQty = $orderItem->getQtyOrdered();
            if ($orderItemCommission && $orderItemQty) {
                $reduceCommission += $orderItemCommission * $item->getQty() / $orderItemQty;
            }
        }
        if ($reduceCommission <= 0) {
            return $this;
        }
        // check reduced commission is over than total commission
        if ($reduceCommission > $this->getCommission()) {
            $reduceCommission = $this->getCommission();
        }
        $account = Mage::getModel('affiliateplus/account')
                ->setStoreId($this->getStoreId())
                ->load($this->getAccountId());
        try {
            $commission = $reduceCommission + $this->getCommissionPlus() * $reduceCommission / $this->getCommission() + $reduceCommission * $this->getPercentPlus() / 100;
            $account->setBalance($account->getBalance() - $commission)
                    ->save();
            $this->setCreditmemoIds(implode(',', array_filter($reducedIds)))
                    ->setCommissionPlus($this->getCommissionPlus() - $this->getCommissionPlus() * $reduceCommission / $this->getCommission())
                    ->setCommission($this->getCommission() - $reduceCommission)
                    ->save();
            
            // update balance for tier affiliate
            $commissionObj = new Varien_Object(array(
                'base_reduce'   => $reduceCommission,
                'total_reduce'  => $commission
            ));
            Mage::dispatchEvent('affiliateplus_reduce_transaction', array(
                'transaction'   => $this,
                // 'creditmemo'    => $creditmemo,
                'commission_obj' => $commissionObj
            ));
            
            $reduceCommission = $commissionObj->getBaseReduce();
            $commission = $commissionObj->getTotalReduce();
            // Send email for affiliate account
            $this->sendMailReduceCommissionToAccount($reduceCommission, $commission);
        } catch (Exception $e) {
            
        }
        return $this;
    }
    
    public function cancel(){
        if ($this->canRestore()) return $this;
    	if (!$this->getId()) return $this;
    	if ($this->getStatus() == '2'){
    		try {
    			$this->setStatus('3')->save();
    		} catch (Exception $e){
    			
    		}
    	} elseif ($this->getStatus() == '1') {
    		// Remove commission for affiliate account
    		$account = Mage::getModel('affiliateplus/account')
	    		->setStoreId($this->getStoreId())
	    		->load($this->getAccountId());
    		try {
				$commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
    			$account->setBalance($account->getBalance() - $commission)//$this->getCommission())
    				->save();
    			$this->setStatus('3')->save();
				
				//update balance tier affiliate
				Mage::dispatchEvent('affiliateplus_cancel_transaction',array('transaction' => $this));
				
	    		// Send email to affiliate account
	    		$this->sendMailUpdatedTransactionToAccount(false);
    		} catch (Exception $e){
    			
    		}
    	}
    	return $this;
    }
    
    /**
     * Cancel transaction
     * 
     * @return Magestore_Affiliateplus_Model_Transaction
     * @throws Exception
     */
    public function cancelTransaction() {
        if ($this->canRestore()) return $this;
        if (!$this->getId()) return $this;
        if ($this->getStatus() == '3') return $this;
        
        if ($this->getStatus() == '1') {
            // Remove commission for affiliate account
    		$account = Mage::getModel('affiliateplus/account')
	    		->setStoreId($this->getStoreId())
	    		->load($this->getAccountId());
            $commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
            if ($account->getBalance() < $commission) {
                throw new Exception(Mage::helper('affiliateplus')->__('Account not enough balance to cancel'));
            }
            $account->setBalance($account->getBalance() - $commission)
                ->save();

            //update balance tier affiliate
            Mage::dispatchEvent('affiliateplus_cancel_transaction',array('transaction' => $this));
        }
        $this->setStatus('3')->save();
        return $this;
    }
    
    public function canRestore() {
        return $this->getData('transaction_is_deleted');
    }
    
	public function sendMailNewTransactionToAccount(){
		if(!Mage::getStoreConfig('affiliateplus/email/is_sent_email_account_new_transaction'))
			return $this;
			
		$store = Mage::getModel('core/store')->load($this->getStoreId());
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());		

		$account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());
		
		if (!$account->getNotification()) return $this;
		
		//update commission tier affiliate
		Mage::dispatchEvent('affiliateplus_reset_transaction_commission',array('transaction' => $this));
		
		$this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($this->getOrderItemIds()))
				->setTotalAmountFormated(Mage::helper('core')->currency($this->getTotalAmount()))
				->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
				->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
				->setPlusCommissionFormated(Mage::helper('core')->currency($this->getPlusCommission()))
				->setAccountName($account->getName())
				->setAccountEmail($account->getEmail())
				->setCreatedAtFormated(Mage::helper('core')->formatDate($this->getCreatedTime(),'medium'))
				;
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
		$template = Mage::getStoreConfig(self::XML_PATH_NEW_TRANSACTION_ACCOUNT_EMAIL, $store->getId());
		
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
                        'transaction'  => $this,
						'store'  => $store,
                    )
                );
		}

		$translate->setTranslateInline(true);
		
		//set current currency
		$store->setCurrentCurrency($currentCurrency);				
		
		return $this;
	}
	
	public function sendMailNewTransactionToSales(){
		if(!Mage::getStoreConfig('affiliateplus/email/is_sent_email_sales_new_transaction'))
			return $this;
		
		$store = Mage::getModel('core/store')->load($this->getStoreId());
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());	
		$sales = Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getId());
		
		$account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());
		$customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
		
		//update commission tier affiliate
		//Mage::dispatchEvent('affiliateplus_reset_transaction_commission',array('transaction' => $this));
		
		$this->setCustomerName($this->getCustomerName())
				->setCustomerEmail($this->getCustomerEmail())
				->setAccountName($account->getName())
				->setAccountEmail($account->getEmail())
				->setProducts(Mage::helper('affiliateplus')->getBackendProductHtmls($this->getOrderItemIds()))
				->setTotalAmountFormated(Mage::helper('core')->currency($this->getTotalAmount()))
				->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
				->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
				->setPlusCommissionFormated(Mage::helper('core')->currency($this->getPlusCommission()))
				->setDiscountFormated(Mage::helper('core')->currency($this->getDiscount()))
				->setCreatedAtFormated(Mage::helper('core')->formatDate($this->getCreatedTime(),'medium'))
				->setSalesName($sales['name'])
				;
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
		$template = Mage::getStoreConfig(self::XML_PATH_NEW_TRANSACTION_SALES_EMAIL,$store->getId());
				
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
                        'transaction'  => $this,
						'store'  => $store,
                    )
                );
		}

		$translate->setTranslateInline(true);	
		//set current currency
		$store->setCurrentCurrency($currentCurrency);	
		return $this;
	}
	
	public function sendMailUpdatedTransactionToAccount($isCompleted){
		if(!Mage::getStoreConfig('affiliateplus/email/is_sent_email_account_updated_transaction'))
			return $this;
		
		$store = Mage::getModel('core/store')->load($this->getStoreId());
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());		

		$account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());
		
		if (!$account->getNotification()) return $this;
		
		//update commission tier affiliate
		Mage::dispatchEvent('affiliateplus_reset_transaction_commission',array('transaction' => $this));
		
		$this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($this->getOrderItemIds()))
				->setTotalAmountFormated(Mage::helper('core')->currency($this->getTotalAmount()))
				->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
				->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
				->setPlusCommissionFormated(Mage::helper('core')->currency($this->getPlusCommission()))
				->setAccountName($account->getName())
				->setAccountEmail($account->getEmail())
				->setCreatedAtFormated(Mage::helper('core')->formatDate($this->getCreatedTime(),'medium'))
				->setIsCompleted($isCompleted)
				;
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
		$template = Mage::getStoreConfig(self::XML_PATH_UPDATED_TRANSACTION_ACCOUNT_EMAIL, $store->getId());
		
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
                        'transaction'  => $this,
						'store'  => $store,
                    )
                );
		}

		$translate->setTranslateInline(true);
		//set current currency
		$store->setCurrentCurrency($currentCurrency);				
		return $this;
	}
    
    /**
     * Send email reduce commission to affiliate account
     * 
     * @param type $reduceCommission
     * @param type $totalReduce
     * @return Magestore_Affiliateplus_Model_Transaction
     */
    public function sendMailReduceCommissionToAccount($reduceCommission, $totalReduce) {
        if (!Mage::getStoreConfig('affiliateplus/email/is_sent_email_account_updated_transaction')) {
            return $this;
        }
        $account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());
        if (!$account->getNotification()) {
            return $this;
        }
        $store = Mage::getModel('core/store')->load($this->getStoreId());
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());
        
        Mage::dispatchEvent('affiliateplus_reset_transaction_commission',array('transaction' => $this));
        $this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($this->getOrderItemIds()))
            ->setTotalAmountFormated(Mage::helper('core')->currency($this->getTotalAmount()))
            ->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
            ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
            ->setPlusCommissionFormated(Mage::helper('core')->currency($this->getPlusCommission()))
            ->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setCreatedAtFormated(Mage::helper('core')->formatDate($this->getCreatedTime(),'medium'))
            ->setReducedCommission(Mage::helper('core')->currency($reduceCommission))
            ->setTotalReduced(Mage::helper('core')->currency($totalReduce));
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        
        $template = Mage::getStoreConfig(self::XML_PATH_REDUCE_TRANSACTION_ACOUNT_EMAIL, $store);
        $sendTo = array(array(
            'email' => $account->getEmail(),
            'name'  => $account->getName(),
        ));
        $mailTemplate = Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()));
        foreach ($sendTo as $recipient) {
            $mailTemplate->sendTransactional(
                $template,
                Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getId()),
                $recipient['email'],
                $recipient['name'],
                array(
                    'transaction'   => $this,
                    'store'         => $store,
                )
            );
        }
        
        $translate->setTranslateInline(true);
        $store->setCurrentCurrency($currentCurrency);
        return $this;
    }
}
