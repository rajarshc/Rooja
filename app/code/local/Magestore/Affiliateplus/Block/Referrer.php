<?php
class Magestore_Affiliateplus_Block_Referrer extends Mage_Core_Block_Template
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
		$collection = Mage::getModel('affiliateplus/action')->getCollection();
		if ($this->_getHelper()->getSharingConfig('balance') == 'store')
			$collection->addFieldToFilter('store_id',Mage::app()->getStore()->getId());
		$collection->addFieldToFilter('account_id',$account->getId())->setCustomGroupSql(true);
		
        $collection->getSelect()->columns(array(
            'total_clicks'  => 'COUNT(action_id)',
            'unique_clicks' => 'SUM(is_unique)',
        ));
		$request = $this->getRequest();
		if ($request->getParam('click') == 'desc'){
			$collection->getSelect()->order('total_clicks DESC');
		}elseif ($request->getParam('click') == 'asc'){
			$collection->getSelect()->order('total_clicks ASC');
		}elseif ($request->getParam('unique') == 'desc'){
			$collection->getSelect()->order('unique_clicks DESC');
		}elseif ($request->getParam('unique') == 'asc'){
			$collection->getSelect()->order('unique_clicks ASC');
		}else{
			$collection->getSelect()->order('action_id DESC');
		}
        $collection->getSelect()->group(array('referer','landing_page','store_id'));
		
		Mage::dispatchEvent('affiliateplus_prepare_referers_collection',array(
			'collection'	=> $collection,
		));
		
		$this->setCollection($collection);
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager','referer_pager')
                ->setTemplate('affiliateplus/html/pager.phtml')
                ->setCollection($this->getCollection());
		$this->setChild('referer_pager',$pager);
		
		$grid = $this->getLayout()->createBlock('affiliateplus/grid','referer_grid');
		
		// prepare column
//		$grid->addColumn('id',array(
//			'header'	=> $this->__('No.'),
//			'align'		=> 'left',
//			'render'	=> 'getNoNumber',
//		));
		
		$grid->addColumn('referer',array(
			'header'	=> $this->__('Traffic Source'),
			'align'		=> 'left',
			'render'	=> 'getReferer',
            'searchable'    => true,
		));
		
		$url = Mage::getUrl('*/*/*');
		if ($this->getRequest()->getParam('click') == 'desc'){
			$header = '<a href="'.$url.'click/asc" class="sort-arrow-desc" title="'.$this->__('ASC').'">'.$this->__('Clicks').'</a>';
		}else {
			$header = '<a href="'.$url.'click/desc" class="sort-arrow-asc" title="'.$this->__('DESC').'">'.$this->__('Clicks').'</a>';
		}
		$grid->addColumn('total_clicks',array(
			'header'	=> $header,
			'index'		=> 'total_clicks',
			'align'		=> 'left',
		));
		
		if ($this->getRequest()->getParam('unique') == 'desc'){
			$header = '<a href="'.$url.'unique/asc" class="sort-arrow-desc" title="'.$this->__('ASC').'">'.$this->__('Unique Clicks').'</a>';
		}else {
			$header = '<a href="'.$url.'unique/desc" class="sort-arrow-asc" title="'.$this->__('DESC').'">'.$this->__('Unique Clicks').'</a>';
		}
		$grid->addColumn('unique_clicks',array(
			'header'	=> $header,// $this->__('Unique Clicks'),
			'index'		=> 'unique_clicks',
			'align'		=> 'left',
		));
		
		if ($this->_getHelper()->getSharingConfig('balance') != 'store')
			$grid->addColumn('store_id',array(
				'header'	=> $this->__('Store View'),
				'index'		=> 'store_id',
				'type'		=> 'options',
				'options'	=> $this->getStoresOption(),
                'searchable'    => true,
			));
		
		$grid->addColumn('landing_page',array(
			'header'	=> $this->__('Landing Page'),
			'render'	=> 'getUrlPath',
            'searchable'    => true,
            'index'     => 'landing_page',
		));
		
		Mage::dispatchEvent('affiliateplus_prepare_referers_columns',array(
			'grid'	=> $grid,
		));
		
		$this->setChild('referer_grid',$grid);
		return $this;
    }
    
    public function getNoNumber($row){
    	return sprintf('#%d',$row->getId());
    }
    
    public function getReferer($row){
    	if ($row->getReferer())
    		return sprintf('<a target="_blank" href="http://%s">%s</a>',$row->getReferer(),$row->getReferer());
    	return $this->__('N/A');
    }
    
    public function getUrlPath($row){
    	return sprintf('<a href="%s">%s</a>',Mage::getBaseUrl().trim($row->getLandingPage(),'/'),$row->getLandingPage());
    }
    
    public function getStoresOption(){
    	$stores = array();
    	foreach (Mage::app()->getStores() as $id => $store)
    		$stores[$id] = $store->getName();
    	return $stores;
    }
    
    public function getPagerHtml(){
    	return $this->getChildHtml('referer_pager');
    }
    
    public function getGridHtml(){
    	return $this->getChildHtml('referer_grid');
    }
    
    protected function _toHtml(){
    	$this->getChild('referer_grid')->setCollection($this->getCollection());
    	return parent::_toHtml();
    }
    
    public function getTraffics()
    {
        $commissionInfo = $this->getCommissionInfo();
        $traffics = new Varien_Object(array());
        Mage::dispatchEvent('affiliateplus_traffics_statistic',array('traffics' => $traffics));
        $data = $traffics->getData();
        $data[] = $commissionInfo;
        return $data;
    }
    
    public function getCommissionInfo(){
        $info = array();
        $session = Mage::getSingleton('affiliateplus/session');
        $date = date('Y-m-d');
        $week = date('W');
        $month = date('m');
        $year = date('Y');
        if($session->isLoggedIn()){
            $account = $session->getAccount();
            $dateCollection = Mage::getModel('affiliateplus/transaction')->getCollection()
                            ->addFieldToFilter('account_id',$account->getId())
                            ->addFieldToFilter('date(created_time)',$date)
                    ;
            $dateCollection ->getSelect()
                            ->group('date(created_time)')
                            ->columns(array('commission_total'=>'SUM(commission)'));
            $first = $dateCollection->getFirstItem();
            $info['today'] = Mage::helper('core')->currency($first->getCommissionTotal());
            /*----------------------------------------------------------------*/
            $weekCollection = Mage::getModel('affiliateplus/transaction')->getCollection()
                            ->addFieldToFilter('account_id',$account->getId())
                            ->addFieldToFilter('week(created_time, 1)',$week)
                    ;
            $weekCollection ->getSelect()
                            ->group('week(created_time, 1)')
                            ->columns(array('commission_total'=>'SUM(commission)'));
            $first = $weekCollection->getFirstItem();
            $info['week'] = Mage::helper('core')->currency($first->getCommissionTotal());
            /*----------------------------------------------------------------*/
            $monthCollection = Mage::getModel('affiliateplus/transaction')->getCollection()
                            ->addFieldToFilter('account_id',$account->getId())
                            ->addFieldToFilter('month(created_time)',$month)
                    ;
            $monthCollection ->getSelect()
                            ->group('month(created_time)')
                            ->columns(array('commission_total'=>'SUM(commission)'));
            $first = $monthCollection->getFirstItem();
            $info['month'] = Mage::helper('core')->currency($first->getCommissionTotal());
            /*----------------------------------------------------------------*/
            $yearCollection = Mage::getModel('affiliateplus/transaction')->getCollection()
                            ->addFieldToFilter('account_id',$account->getId())
                            ->addFieldToFilter('year(created_time)',$year)
                    ;
            $yearCollection ->getSelect()
                            ->group('year(created_time)')
                            ->columns(array('commission_total'=>'SUM(commission)'));
            $first = $yearCollection->getFirstItem();
            $info['year'] = Mage::helper('core')->currency($first->getCommissionTotal());
            /*----------------------------------------------------------------*/
            $allCollection = Mage::getModel('affiliateplus/transaction')->getCollection()
                            ->addFieldToFilter('account_id',$account->getId())
                    ;
            
            $allCollection  ->getSelect()
                            ->group('account_id')
                            ->columns(array('commission_total'=>'SUM(commission)'));
            $first = $allCollection->getFirstItem();
            $info['all'] = Mage::helper('core')->currency($first->getCommissionTotal());
            $info['name']  =  'commission';
            $info['title']  =  'Commissions';
            return $info;
        }
    }
}