<?php

class Magestore_Affiliatepluslevel_Model_Transaction extends Mage_Core_Model_Abstract
{
	
	const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';
	const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';
	const XML_PATH_NEW_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/multilevel_new_transaction_account_email_template';
	const XML_PATH_UPDATED_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/multilevel_updated_transaction_account_email_template';
    const XML_PATH_REDUCED_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/multilevel_reduce_commission_account_email_template';

    public function _construct()
    {
        parent::_construct();
        $this->_init('affiliatepluslevel/transaction');
    }
	
	public function sendMailNewTransactionToAccount($transaction){
		if(!Mage::getStoreConfig('affiliateplus/email/multilevel_is_sent_email_account_new_transaction'))
			return $this;
			
		$store = Mage::getModel('core/store')->load($transaction->getStoreId());
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());		

		$account = Mage::getModel('affiliateplus/account')->load($this->getTierId());		
		
		
		$this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($transaction->getOrderItemIds()))
				->setTotalAmountFormated(Mage::helper('core')->currency($transaction->getTotalAmount()))
				->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
				->setCommissionPlusFormated(Mage::helper('core')->currency($this->getCommissionPlus()))
				->setCommissionPlus(floatval($this->getCommissionPlus()))
				->setAccountName($account->getName())
				->setAccountEmail($account->getEmail())
				->setCreatedAtFormated(Mage::helper('core')->formatDate($transaction->getCreatedTime(),'medium'))
				->setTransactionId($transaction->getId())
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
		 
        $this->setLevel($this->getLevel()+1);
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
		$this->setLevel($this->getLevel()-1);

		$translate->setTranslateInline(true);
		
		//set current currency
		$store->setCurrentCurrency($currentCurrency);				
		
		return $this;
	}
	
	public function sendMailUpdatedTransactionToAccount($transaction, $isCompleted){
		if(!Mage::getStoreConfig('affiliateplus/email/multilevel_is_sent_email_account_updated_transaction'))
			return $this;
		
		$store = Mage::getModel('core/store')->load($transaction->getStoreId());
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());		

		$account = Mage::getModel('affiliateplus/account')->load($this->getTierId());
		
		$this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($transaction->getOrderItemIds()))
				->setTotalAmountFormated(Mage::helper('core')->currency($transaction->getTotalAmount()))
				->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
				->setCommissionPlusFormated(Mage::helper('core')->currency($this->getCommissionPlus()))
				->setCommissionPlus(floatval($this->getCommissionPlus()))
				->setAccountName($account->getName())
				->setAccountEmail($account->getEmail())
				->setCreatedAtFormated(Mage::helper('core')->formatDate($transaction->getCreatedTime(),'medium'))
				->setIsCompleted($isCompleted)
				->setTransactionId($transaction->getId())
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
		 
	 	$this->setLevel($this->getLevel()+1);
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
		$this->setLevel($this->getLevel()-1);

		$translate->setTranslateInline(true);
		//set current currency
		$store->setCurrentCurrency($currentCurrency);				
		return $this;
	}
    
    public function sendMailReducedTransactionToAccount($transaction, $reduceCommission, $totalReduce) {
        if(!Mage::getStoreConfig('affiliateplus/email/multilevel_is_sent_email_account_updated_transaction'))
			return $this;
		
		$store = Mage::getModel('core/store')->load($transaction->getStoreId());
		$currentCurrency = $store->getCurrentCurrency();
		$store->setCurrentCurrency($store->getBaseCurrency());		

		$account = Mage::getModel('affiliateplus/account')->load($this->getTierId());
		
		$this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($transaction->getOrderItemIds()))
				->setTotalAmountFormated(Mage::helper('core')->currency($transaction->getTotalAmount()))
				->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
				->setCommissionPlusFormated(Mage::helper('core')->currency($this->getCommissionPlus()))
				->setCommissionPlus(floatval($this->getCommissionPlus()))
				->setAccountName($account->getName())
				->setAccountEmail($account->getEmail())
				->setCreatedAtFormated(Mage::helper('core')->formatDate($transaction->getCreatedTime(),'medium'))
                ->setReducedCommission(Mage::helper('core')->currency($reduceCommission))
                ->setTotalReduced(Mage::helper('core')->currency($totalReduce))
				->setTransactionId($transaction->getId());
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
		
		$template = Mage::getStoreConfig(self::XML_PATH_REDUCED_TRANSACTION_ACCOUNT_EMAIL, $store->getId());
		
        $sendTo = array(
            array(
                'email' => $account->getEmail(),
                'name'  => $account->getName(),
            )
        );
		$mailTemplate = Mage::getModel('core/email_template');
		 
	 	$this->setLevel($this->getLevel()+1);
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
		$this->setLevel($this->getLevel()-1);

		$translate->setTranslateInline(true);
		//set current currency
		$store->setCurrentCurrency($currentCurrency);				
		return $this;
    }
}
