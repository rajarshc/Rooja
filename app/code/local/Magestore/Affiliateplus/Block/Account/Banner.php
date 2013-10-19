<?php
class Magestore_Affiliateplus_Block_Account_Banner extends Mage_Core_Block_Template
{
	protected $_banner_collection;
	
	protected $_ids = array();
	protected $_types = array();
	protected $_sizes = array();
	
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
    
    protected function _construct(){
    	parent::_construct();
    	$storeId = Mage::app()->getStore()->getId();
    	$bannerCollection = Mage::getModel('affiliateplus/banner')->getCollection()->setStoreId($storeId);
    	
    	Mage::dispatchEvent('affiliateplus_banner_prepare_collection',array(
    		'collection'	=> $bannerCollection,
    	));
    	
    	$ids = array();
    	$types = array();
    	$sizes = array();
    	foreach ($bannerCollection as $banner){
    		if ($banner->getStatus() == 1)
    			$ids[] = $banner->getId();
    		$types[] = $banner->getTypeId();
    		if ($banner->getTypeId() != 3)
    			$sizes[] = array(
	    			'w' => $banner->getWidth(),
		    		'h' => $banner->getHeight()
	    		);
    	}
    	$this->_ids = $ids;
    	$this->_types = array_unique($types);
    	$this->_sizes = $sizes;
    }
    
    public function setBannerCollection($collection){
    	$this->_banner_collection = $collection;
    	return $this;
    }
    
    public function getBannerCollection(){
    	if (!$this->_banner_collection){
    		$storeId = Mage::app()->getStore()->getId();
    		$bannerCollection = Mage::getModel('affiliateplus/banner')->getCollection()
	    		->setStoreId($storeId)
	    		->addFieldToFilter('main_table.banner_id',array('in' => $this->_ids));
	    	if ($type = $this->getRequest()->getParam('type'))
	    		$bannerCollection->addFieldToFilter('main_table.type_id',$type);
	    	if ($width = $this->getRequest()->getParam('w'))
	    		$bannerCollection->addFieldToFilter('main_table.width',$width);
	    	if ($height = $this->getRequest()->getParam('h'))
	    		$bannerCollection->addFieldToFilter('main_table.height',$height);
            
            $clickSelect = clone $bannerCollection->getSelect();
            $viewsSelect = clone $clickSelect;
            $clickSelect->reset()
                ->from(array('ct' => $bannerCollection->getTable('affiliateplus/action')),array(
                    'banner_id' => 'banner_id',
                    'raw_click' => 'SUM(totals)',
                    'uni_click' => 'SUM(is_unique)'
                ))->group('banner_id')
                ->where('type = ?', 2);
            $viewsSelect->reset()
                ->from(array('vt' => $bannerCollection->getTable('affiliateplus/action')),array(
                    'banner_id' => 'banner_id',
                    'raw_view' => 'SUM(totals)',
                    'uni_view' => 'SUM(is_unique)'
                ))->group('banner_id')
                ->where('type = ?', 1);
            
            $bannerCollection->getSelect()
                ->joinLeft(array('c' => new Zend_Db_Expr("({$clickSelect->__toString()})")),
                    'main_table.banner_id = c.banner_id',
                    array('raw_click', 'uni_click')
                )->joinLeft(array('v' => new Zend_Db_Expr("({$viewsSelect->__toString()})")),
                    'main_table.banner_id = v.banner_id',
                    array('raw_view', 'uni_view')
                );
            
	    	$this->setBannerCollection($bannerCollection);
    	}
    	return $this->_banner_collection;
    }
    
    /**
     * get Filter
     *
     * @return array
     */
    public function getFilters(){
    	$request = $this->getRequest();
    	$filters = array(
    		array(
    			'label'	=> $this->__('All'),
    			'current'	=> !($request->getParam('type') || $request->getParam('w') || $request->getParam('h')),
    			'url'	=> $this->getFilterUrl(),
    		)
    	);
    	
    	// Filter by size
    	foreach ($this->_sizes as $size)
			$filters[$size['w'].'x'.$size['h']] = array(
				'label'	=> $this->__('%sx%s',$size['w'],$size['h']),
				'current'	=> ($request->getParam('w') == $size['w'] && $request->getParam('h') == $size['h']),
				'url'	=> $this->getFilterUrl(array(
					'w'	=> $size['w'],
					'h'	=> $size['h']
				))
			);
		
    	// Filter by type
    	$typesLabel = $this->getTypesLabel();
    	foreach ($this->_types as $type)
    		$filters[] = array(
    			'label'	=> $typesLabel[$type],
    			'current'	=> ($request->getParam('type') == $type),
    			'url'	=> $this->getFilterUrl(array(
    				'type' => $type
    			))
    		);
    	
    	return $filters;
    }
    
    public function getTypesLabel(){
    	return array(
    		1 => $this->__('Image'),
			2 => $this->__('Flash'),
			3 => $this->__('Text')
		);
    }
    
    /**
     * Get Filter URL
     *
     * @param array $params
     * @return text
     */
    public function getFilterUrl($params = array()){
    	return $this->getUrl('affiliateplus/banner/list',array('_query' => $params));
    }
    
    /**
     * Get share url for banner
     *
     * @param Magestore_Affiliateplus_Model_Banner $banner
     * @return text
     */
    public function getBannerUrl($banner){
    	return Mage::helper('affiliateplus/url')->getBannerUrl($banner);
    }
    
    /**
     * Get banner source file url
     *
     * @param Magestore_Affiliateplus_Model_Banner $banner
     * @return text
     */
    public function getBannerSrc($banner){
    	return Mage::getBaseUrl('media').'affiliateplus/banner/'.$banner->getSourceFile();
    }
    
    public function getAccount(){
    	return Mage::getSingleton('affiliateplus/session')->getAccount();
    }
    
    public function getStoreCode(){
    	if (Mage::app()->getStore()->getId() != Mage::app()->getDefaultStoreView()->getId())
    		return Mage::app()->getStore()->getCode();
    	return false;
    }
    
    public function getAffiliateUrl(){
    	return Mage::helper('affiliateplus/url')->addAccToUrl($this->getBaseUrl());
    }
    
    public function getImageUrl($params = array())
    {
        if (!Mage::getStoreConfig('affiliateplus/action/use_magento_link')) {
            $url = $this->getJsUrl() . 'magestore/affiliateplus/banner.php?';
            $url .= http_build_query($params);
        } else {
            $url = $this->getUrl('affiliateplus/banner/image', $params);
        }
        return $url;
    }
}
