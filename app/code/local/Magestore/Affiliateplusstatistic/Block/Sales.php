<?php
class Magestore_Affiliateplusstatistic_Block_Sales extends Mage_Adminhtml_Block_Dashboard_Bar
{
	/**
	 * get Affiliate Plus Statistic Helper
	 *
	 * @return Magestore_Affiliateplusstatistic_Helper_Data
	 */
	protected function _getStatisticHelper(){
		return Mage::helper('affiliateplusstatistic');
	}
	
	/**
	 * get Statistic Reports Model
	 *
	 * @return Magestore_Affiliateplusstatistic_Model_Reports
	 */
	protected function _getReportsModel(){
		return Mage::getModel('affiliateplusstatistic/reports');
	}
	
	protected function _construct(){
		parent::_construct();
		$this->setTemplate('affiliateplusstatistic/salebar.phtml');
	}
	
	protected function _prepareLayout(){
		$reports = $this->_getReportsModel();
		
		$filterStoreId = array();
		$filterStoreIds = $filterStoreId;
		if ($storeId = $this->getRequest()->getParam('store')){
			$filterStoreId = array('store_id' => $storeId);
			$filterStoreIds = array('store_ids' => array('finset' => $storeId));
		}
		
		$reports->setCollection(Mage::getResourceModel('affiliateplus/transaction_collection'))
			->setFilters($filterStoreId)
			->addFilter('status',1);
		$reportDataObject = $reports->resetSelectColumns()
			->addCountColumn('total_transactions','transaction_id')
			->addSumColumn('lifetime_sales_amount','total_amount')
			->addSumColumn('total_commissions','commission')
			->getDataObject();
		$this->addTotal($this->__('Lifetime Sales'),$reportDataObject->getLifetimeSalesAmount());
		$this->addTotal($this->__('Total Commissions'),$reportDataObject->getTotalCommissions());
		$this->addTotal($this->__('Total Transactions'),$reportDataObject->getTotalTransactions(),true);
		
		$this->addTotal($this->__('Total Affiliate Accounts')
			,Mage::getResourceModel('affiliateplus/account_collection')
				//->addFieldToFilter('status',1)
				->getSize()
			,true
		);
		
		$reports->setCollection(Mage::getResourceModel('affiliateplus/payment_collection')->setLoadMethodInfo(false))
			->setFilters($filterStoreIds)
			->addFilter('status',3);
		$reportDataObject = $reports->resetSelectColumns()
			->addSumColumn('total_payout','amount')
			->getDataObject();
		$this->addTotal($this->__('Total Payout'),$reportDataObject->getTotalPayout());
		
		$this->setEntryEditHead($this->__('General'))->setTableId('general');
		parent::_prepareLayout();
	}
}