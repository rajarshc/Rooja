<?php

class Magestore_Affiliateplusstatistic_Model_Observer
{
	public function refererSaveAfter($observer){
		$referer = $observer->getEvent()->getAffiliateplusReferer();
        
        $account = Mage::getModel('affiliateplus/account')->load($referer->getAccountId());
		$ipAddress = Mage::app()->getRequest()->getClientIp();
		$model = Mage::getModel('affiliateplusstatistic/statistic')
			->setRefererId($referer->getId())
			->setReferer($referer->getReferer())
			->setUrlPath($referer->getUrlPath())
			->setIpAddress($ipAddress)
			->setVisitAt(now())
			->setStoreId($referer->getStoreId())
            ->setAccountEmail($account->getEmail())
			->save();
		return $this;
	}

    public function sendReportEmail()
    {
        $websites = Mage::app()->getWebsites(true);
        $helper = Mage::helper('affiliateplus/config');
        foreach ($websites as $website) {
            if (!$website->getConfig('affiliateplus/email/is_sent_report'))
                continue;
            $periodData = array(
                'week' => array(
                    'date' => 'w',
                    'label' => $helper->__('last week'),
                ),
                'month' => array(
                    'date' => 'j',
                    'label' => $helper->__('last month'),
                ),
                'year' => array(
                    'date' => 'z',
                    'label' => $helper->__('last year'),
                )
            );
            $period = $website->getConfig('affiliateplus/email/report_period');
            if (date($periodData[$period]['date']) != 1)
                continue;

            $store = $website->getDefaultStore();
            if (!$store)
                continue;
            $storeId = $store->getId();

            $accounts = Mage::getResourceModel('affiliateplus/account_collection')
                ->addFieldToFilter('main_table.status', 1)
                ->addFieldToFilter('main_table.notification', 1);

            $accounts->getSelect()->joinLeft(
                    array('e' => $accounts->getTable('customer/entity')), 'main_table.customer_id	= e.entity_id', array('website_id')
                )->where('e.website_id = ?', $website->getId())
                ->where('e.is_active = 1');

            $date = new Zend_Date();
            $to = $date->toString();
            $function = 'sub' . ucfirst($period);
            $fromDate = $date->$function(1)->toString('YYYY-MM-dd');
            $from = $date->toString();

            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);
            $template = $website->getConfig('affiliateplus/email/report_template');
            $sender = Mage::getStoreConfig('trans_email/ident_sales', $store);

            foreach ($accounts as $account) {
                $statistic = new Varien_Object();
                $transactions = Mage::getResourceModel('affiliateplus/transaction_collection')
                    ->addFieldToFilter('account_id', $account->getId());
                $transactions->getSelect()->reset(Zend_Db_Select::COLUMNS)
                    ->where('date(created_time) >= ?', $fromDate)
                    ->columns(array(
                        'status',
                        'sales' => 'SUM(`total_amount`)',
                        'transactions' => 'COUNT(`transaction_id`)',
                        'commissions' => 'SUM(`commission`+`commission`*`percent_plus`+`commission_plus`)',
                    ))->group('status');
                foreach ($transactions as $transaction) {
                    if ($transaction->getStatus() == 1) {
                        $statistic->setData('complete', $transaction->getData());
                    } elseif ($transaction->getStatus() == 2) {
                        $statistic->setData('pending', $transaction->getData());
                    } elseif ($transaction->getStatus() == 3) {
                        $statistic->setData('cancel', $transaction->getData());
                    }
                }
                
                $actions = Mage::getResourceModel('affiliateplus/action_collection');
                $actions->getSelect()->reset(Zend_Db_Select::COLUMNS)
                    ->where('account_id = ?', $account->getId())
                    ->where('type = ?', 2)
                    ->where('created_date >= ?', $fromDate)
                    ->columns(array(
                        'clicks'	=> 'SUM(totals)',
                        'unique'	=> 'SUM(is_unique)',
                    ))->group('account_id');
                $statistic->setData('click', $actions->getFirstItem()->getData());
                
                $mailTemplate = Mage::getModel('core/email_template')
                    ->setDesignConfig(array(
                        'area' => 'frontend',
                        'store' => $storeId,
                    ))->sendTransactional(
                        $template, $sender, $account->getEmail(), $account->getName(), array(
                            'store'     => $store,
                            'account'   => $account,
                            'statistic' => $statistic,
                            'period'    => $helper->__($period),
                            'label'     => $periodData[$period]['label'],
                            'from'      => $from,
                            'to'        => $to,
                        )
                    );
            }
            
            $translate->setTranslateInline(true);
        }
    }
}
