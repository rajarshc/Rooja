<?php
class Magestore_Affiliatepluslevel_Block_Tiers extends Mage_Core_Block_Template
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
		$accountId = Mage::getSingleton('affiliateplus/session')->getAccount()->getId();
		
		$allTierIds = Mage::helper('affiliatepluslevel')->getAllTierIds($accountId, Mage::app()->getStore()->getId());
		if(count($allTierIds))
			$allTierIdsString = implode(',' , $allTierIds);
		else
			$allTierIdsString = 0;
		
		$tierTable = Mage::getModel('core/resource')->getTableName('affiliatepluslevel_tier');
		
		$collection = Mage::getModel('affiliateplus/account')->getCollection()
					->setStoreId(Mage::app()->getStore()->getId())
					->setOrder('created_time','DESC');
		
		$collection->getSelect()
				->joinLeft($tierTable, "$tierTable.tier_id = main_table.account_id", array('level'=>'level', 'toptier_id' => 'toptier_id', ))
		
				->where("account_id IN ($allTierIdsString)");
				
		$request = $this->getRequest();
		if ($request->getParam('joined') == 'desc'){
			$collection->getSelect()->order('created_time DESC');
		}elseif ($request->getParam('joined') == 'asc'){
			$collection->getSelect()->order('created_time ASC');
		}elseif ($request->getParam('level') == 'desc'){
			$collection->getSelect()->order('level DESC');
		}elseif ($request->getParam('level') == 'asc'){
			$collection->getSelect()->order('level ASC');
		}else{
			$collection->getSelect()->order('account_id DESC');
		}
		
		$this->setCollection($collection);
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager','tiers_pager')->setCollection($this->getCollection());
		$this->setChild('tiers_pager',$pager);
		
		$grid = $this->getLayout()->createBlock('affiliateplus/grid','tiers_grid');
		
		
		$url = $this->getUrl('affiliatepluslevel/index/listTier/');
		// prepare column
		$grid->addColumn('id',array(
			'header'	=> $this->__('No.'),
			'align'		=> 'left',
			'render'	=> 'getNoNumber',
		));
		
		if ($this->getRequest()->getParam('joined') == 'desc')
			$header = '<a href="'.$url.'joined/asc" class="sort-arrow-desc" title="'.$this->__('ASC').'">'.$this->__('Joined time').'</a>';
		else
			$header = '<a href="'.$url.'joined/desc" class="sort-arrow-asc" title="'.$this->__('DESC').'">'.$this->__('Joined time').'</a>';
		 
		$grid->addColumn('created_time',array(
			'header'	=> $header,
			'index'		=> 'created_time',
			'type'		=> 'date',
			'format'	=> 'medium',
			'align'		=> 'left',
		));
		
		$grid->addColumn('name',array(
			'header'	=> $this->__('Affiliates'),
			'index'		=> 'name',
			'align'		=> 'left',
			'render'	=> 'getAffiliatesName'
		));
		
		
		if ($this->getRequest()->getParam('level') == 'desc')
			$header = '<a href="'.$url.'level/asc" class="sort-arrow-desc" title="'.$this->__('ASC').'">'.$this->__('Level').'</a>';
		else
			$header = '<a href="'.$url.'level/desc" class="sort-arrow-asc" title="'.$this->__('DESC').'">'.$this->__('Level').'</a>';
		 
		$grid->addColumn('level',array(
			'header'	=> $header,
			'index'		=> 'level',
			'align'		=> 'left',
			'render'	=> 'getLevel',
		));
		
		$grid->addColumn('sum',array(
			'header'	=> $this->__('Commissions'),
			'align'		=> 'left',
			'index'		=> 'sum',
			'render'	=> 'getSum',
		));
		
		$grid->addColumn('status',array(
			'header'	=> $this->__('Status'),
			'align'		=> 'left',
			'index'		=> 'status',
			'type'		=> 'options',
			'options'	=> array(
				1	=> $this->__('Enabled'),
				2	=> $this->__('Disabled'),
			)
		));
		
		$this->setChild('tiers_grid',$grid);
		return $this;
    }
    
    public function getNoNumber($row){
    	return sprintf('#%d',$row->getId());
    }
	
	public function getAffiliatesName($row){
        if ($row->getLevel() - $this->getCurrentAcountLevel() > 1) {
            return $row->getName();
        }
		return sprintf("%s (<a href='mailto:%s'>%s</a>)",$row->getName(),$row->getEmail(),$row->getEmail());
	}
    
    public function getCurrentAcountLevel() {
        if (!$this->hasData('current_account_level')) {
            $accountId = Mage::getSingleton('affiliateplus/session')->getAccount()->getId();
            $currentAccountLevel = Mage::helper('affiliatepluslevel')->getAccountLevel($accountId);
            $this->setData('current_account_level', $currentAccountLevel);
        }
        return $this->getData('current_account_level');
    }
	
	public function getLevel($row){
		// $accountId = Mage::getSingleton('affiliateplus/session')->getAccount()->getId();
		// $currentAccountLevel = Mage::helper('affiliatepluslevel')->getAccountLevel($accountId);
        $currentAccountLevel = $this->getCurrentAcountLevel();
		return $row->getLevel() - $currentAccountLevel + 1;
	}
	
	public function getSum($row){
		$accountId = Mage::getSingleton('affiliateplus/session')->getAccount()->getId();
		
		$transactions = Mage::getModel('affiliateplus/transaction')->getCollection()
							->addFieldToFilter('account_id', $row->getAccountId())
							->addFieldToFilter('status', 1);
		
		$transactionIds = array();
		foreach($transactions as $transaction){
			$transactionIds[] = $transaction->getId();
		}
		
		$tiertransactions = Mage::getModel('affiliatepluslevel/transaction')->getCollection()
							->addFieldToFilter('transaction_id', array('in' => $transactionIds))
							->addFieldToFilter('level', array('neq' => 0))
							->addFieldToFilter('tier_id', $accountId);
		$commissions = 0;
		foreach($tiertransactions as $tiertransaction){
			$commissions += $tiertransaction->getCommission();
		}
		
		return $this->getFormatedCurrency($commissions);
	}
    
    public function getPagerHtml(){
    	return $this->getChildHtml('tiers_pager');
    }
    
    public function getGridHtml(){
    	return $this->getChildHtml('tiers_grid');
    }
	
	public function getFormatedCurrency($value){
		return Mage::helper('core')->currency($value);
    }
    
    protected function _toHtml(){
    	$this->getChild('tiers_grid')->setCollection($this->getCollection());
    	return parent::_toHtml();
    }
}