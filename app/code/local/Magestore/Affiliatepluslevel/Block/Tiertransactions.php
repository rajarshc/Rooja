<?php
class Magestore_Affiliatepluslevel_Block_Tiertransactions extends Mage_Core_Block_Template
{
	/**
	 * get Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	public function _getHelper(){
		return Mage::helper('affiliateplus/config');
	}
	
	protected function _construct(){
		parent::_construct();
		$account = Mage::getSingleton('affiliateplus/session')->getAccount();
		
		$accountId = $account->getAccountId();
		$transactionTable = Mage::getModel('core/resource')->getTableName('affiliateplus_transaction');
		
		$collection = Mage::getModel('affiliatepluslevel/transaction')->getCollection()
					->addFieldToFilter('tier_id', $accountId);
					
		
		$collection->getSelect()
				->columns('(main_table.level + 1) AS real_level')
				->joinLeft($transactionTable, "$transactionTable.transaction_id = main_table.transaction_id", 
					array(	'account_name'		=> 'account_name',
							'account_email'		=> 'account_email',
							'order_id' 			=> 'order_id',
							'order_number'		=> 'order_number',
							'order_item_ids'	=> 'order_item_ids',
							'order_item_names'	=> 'order_item_names',
							'total_amount'		=> 'total_amount',
							'discount'			=> 'discount',
							'created_time'		=> 'created_time',
							'status'			=> 'status',
							'store_id'			=> 'store_id',
					));
		
		$collection->setOrder('created_time','DESC');
		
		if ($this->_getHelper()->getSharingConfig('balance') == 'store')
			$collection->addFieldToFilter('store_id',Mage::app()->getStore()->getId());
		
		$collection->addFieldToFilter('level', array('neq'=>0));

		//echo count($collection);die();
		$this->setCollection($collection);
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager','tiertransactions_pager')
            ->setTemplate('affiliateplus/html/pager.phtml')
            ->setCollection($this->getCollection());
		$this->setChild('tiertransactions_pager',$pager);
		
		$grid = $this->getLayout()->createBlock('affiliateplus/grid','tiertransactions_grid');
		
		// prepare column
		// $grid->addColumn('id',array(
			// 'header'	=> $this->__('No.'),
			// 'align'		=> 'left',
			// 'render'	=> 'getNoNumber',
		// ));
		
		$grid->addColumn('created_time',array(
			'header'	=> $this->__('Date'),
			'index'		=> 'created_time',
			'type'		=> 'date',
			'format'	=> 'medium',
			'align'		=> 'left',
            'searchable'    => true,
            'width'     => '118px'
		));
		
		$grid->addColumn('account_name',array(
			'header'	=> $this->__('Affiliates'),
			'index'		=> 'account_name',
			'align'		=> 'left',
			'render'	=> 'getAffiliatesName'
		));
		
		$grid->addColumn('order_item_names',array(
			'header'	=> $this->__('Products'),
			'index'		=> 'order_item_names',
			'align'		=> 'left',
			'render'	=> 'getFrontendProductHtmls',
		));
		
		$grid->addColumn('total_amount',array(
			'header'	=> $this->__('Total').'<br />'.$this->__('Amount'),//$this->__('Total Amount'),
			'align'		=> 'left',
			'type'		=> 'baseprice',
			'index'		=> 'total_amount'
		));
		
		$grid->addColumn('commission',array(
			'header'	=> $this->__('Commission'),
			'align'		=> 'left',
			'type'		=> 'baseprice',
			'index'		=> 'commission'
		));
		
		Mage::dispatchEvent('affiliatepluslevel_prepare_sales_columns_plus', array(
            'grid'  => $grid
        ));
		
		$grid->addColumn('real_level',array(
			'header'	=> $this->__('Level'),
			'align'		=> 'left',
			'index'		=> 'real_level'
		));
		
		$grid->addColumn('status',array(
			'header'	=> $this->__('Status'),
			'align'		=> 'left',
			'index'		=> 'status',
			'type'		=> 'options',
			'options'	=> array(
				1	=> $this->__('Complete'),
				2	=> $this->__('Pending'),
				3	=> $this->__('Canceled'),
                4   => $this->__('On Hold'),
			),
            'width' => '51px',
            'searchable'    => true,
		));
		
		$this->setChild('tiertransactions_grid',$grid);
		return $this;
    }
    
    public function getNoNumber($row){
    	return sprintf('#%d',$row->getId());
    }
	
	public function getAffiliatesName($row){
        if ($row->getRealLevel() > 2) {
            return $row->getAccountName();
        }
		return sprintf("%s <a href='mailto:%s'>%s</a>",$row->getAccountName(),$row->getAccountEmail(),$row->getAccountEmail());
	}
    
    public function getFrontendProductHtmls($row){
    	return Mage::helper('affiliateplus')->getFrontendProductHtmls($row->getData('order_item_ids'));
    }
    
    public function getPagerHtml(){
    	return $this->getChildHtml('tiertransactions_pager');
    }
    
    public function getGridHtml(){
    	return $this->getChildHtml('tiertransactions_grid');
    }
    
    protected function _toHtml(){
    	$this->getChild('tiertransactions_grid')->setCollection($this->getCollection());
    	return parent::_toHtml();
    }
    
    public function getStatisticInfo(){
    	$accountId = Mage::getSingleton('affiliateplus/session')->getAccount()->getId();
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
		return array(
			'number_commission'	=> count($collection),
			'transactions'		=> $this->__('Tier Transactions'),
			'commissions'		=> $totalCommission,
			'earning'			=> $this->__('Tier Earnings')
		);
    }
}