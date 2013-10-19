<?php
class Magestore_Affiliatepluslevel_Block_Statistictransactions extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('affiliatepluslevel/statistictransactions.phtml');
		return $this;
    }
	
	
	public function getFormatedCurreny($value){
    	$store = Mage::app()->getStore();
    	return $store->getBaseCurrency()->format($value);
    }
	
	public function getInfoStandardCommission(){
		$accountId = $this->getAccount()->getId();
		$storeId = Mage::app()->getStore()->getId();
		$scope = Mage::getStoreConfig('affiliateplus/account/balance', $storeId);
		/* $transactionTable = Mage::getModel('core/resource')->getTableName('affiliateplus_transaction');
		$collection = Mage::getModel('affiliatepluslevel/transaction')->getCollection()
						->addFieldToFilter('tier_id', $accountId)
						->addFieldToFilter('level', 0);
		$collection->getSelect()
				->join($transactionTable, "$transactionTable.transaction_id=main_table.transaction_id", array('status'=> 'status')) ;*/
		
		$transactionTable = Mage::getModel('core/resource')->getTableName('affiliatepluslevel_transaction');
		$collection = Mage::getModel('affiliateplus/transaction')->getCollection()
					->addFieldToFilter('account_id', $accountId);
		$collection->getSelect()
				->joinLeft(array('ts' => $transactionTable),
					"ts.transaction_id = main_table.transaction_id", 
					array('level'=>'level','plus_commission'=>'commission_plus'))
				->columns("if (ts.commission IS NULL, main_table.commission, ts.commission) as commission")
				->where("ts.tier_id=$accountId OR (ts.tier_id IS NULL AND main_table.account_id = $accountId )"); 
		
		if($storeId && $scope == 'store')
			$collection->addFieldToFilter('store_id', $storeId);
			
		$totalCommission = 0;
		foreach($collection as $item){
			if($item->getStatus() == 1){
				$totalCommission += $item->getCommission();
				if ($item->getPlusCommission())
					$totalCommission += $item->getPlusCommission();
				else 
					$totalCommission += $item->getCommissionPlus() + $item->getCommission() * $item->getPercentPlus() / 100;
			}
		}
		
		return array(
			'number_commission'	=> count($collection),
			'commissions'		=> $totalCommission,
			'total_commission'	=> $this->getFormatedCurreny($totalCommission)
		);
	}
	
	public function getInfoTierCommission(){
		$accountId = $this->getAccount()->getId();
		$storeId = Mage::app()->getStore()->getId();
		$scope = Mage::getStoreConfig('affiliateplus/account/balance', $storeId);
		
		$transactionTable = Mage::getModel('core/resource')->getTableName('affiliateplus_transaction');
		$collection = Mage::getModel('affiliatepluslevel/transaction')->getCollection()
						->addFieldToFilter('tier_id', $accountId)
						->addFieldToFilter('level', array('neq'=>0));
		
		$collection->getSelect()
				->join($transactionTable, "$transactionTable.transaction_id=main_table.transaction_id", array('status'=> 'status'));
		
		
		if($storeId && $scope == 'store')
			$collection->addFieldToFilter('store_id', $storeId);
		
		$totalCommission = 0;
		foreach($collection as $item){
			if($item->getStatus() == 1)
				$totalCommission += $item->getCommission() + $item->getCommissionPlus(); 
		}
		//die();
		return array(
			'number_commission'	=> count($collection),
			'commissions'		=> $totalCommission,
			'total_commission'	=> $this->getFormatedCurreny($totalCommission)
		);
	}
	
	public function getAccount(){
    	return Mage::getSingleton('affiliateplus/session')->getAccount();
    }
}