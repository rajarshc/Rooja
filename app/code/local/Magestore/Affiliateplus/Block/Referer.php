<?php
class Magestore_Affiliateplus_Block_Referer extends Mage_Core_Block_Template
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
		$collection = Mage::getModel('affiliateplus/referer')->getCollection();
		if ($this->_getHelper()->getSharingConfig('balance') == 'store')
			$collection->addFieldToFilter('store_id',Mage::app()->getStore()->getId());
		$collection->addFieldToFilter('account_id',$account->getId());
		
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
			$collection->getSelect()->order('referer_id DESC');
		}
		
		Mage::dispatchEvent('affiliateplus_prepare_referers_collection',array(
			'collection'	=> $collection,
		));
		
		$this->setCollection($collection);
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager','referer_pager')->setCollection($this->getCollection());
		$this->setChild('referer_pager',$pager);
		
		$grid = $this->getLayout()->createBlock('affiliateplus/grid','referer_grid');
		
		// prepare column
		$grid->addColumn('id',array(
			'header'	=> $this->__('No.'),
			'align'		=> 'left',
			'render'	=> 'getNoNumber',
		));
		
		$grid->addColumn('referer',array(
			'header'	=> $this->__('Referer'),
			'align'		=> 'left',
			'render'	=> 'getReferer',
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
			));
		
		$grid->addColumn('url_path',array(
			'header'	=> $this->__('Path'),
			'render'	=> 'getUrlPath',
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
    	return sprintf('<a href="%s">%s</a>',Mage::getBaseUrl().trim($row->getUrlPath(),'/'),$row->getUrlPath());
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
}