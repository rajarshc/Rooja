<?php

class Magestore_Affiliateplus_Model_Referer extends Mage_Core_Model_Abstract
{
	protected $_eventPrefix = 'affiliateplus_referer';
    protected $_eventObject = 'affiliateplus_referer';
	
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplus/referer');
    }
    
    public function loadExistReferer($accountId, $referer, $storeId, $pathInfo){
    	$item = $this->getCollection()
    		->addFieldToFilter('account_id',$accountId)
    		->addFieldToFilter('referer',$referer)
    		->addFieldToFilter('store_id',$storeId)
    		->addFieldToFilter('url_path',$pathInfo)
    		->getFirstItem();
    	if ($item && $item->getId())
    		$this->setData($item->getData())
    			->setId($item->getId());
    	else 
    		$this->setData('account_id',$accountId)
    			->setData('referer',$referer)
    			->setData('store_id',$storeId)
    			->setData('url_path',$pathInfo)
    			->setId(null);
    	return $this;
    }
    
    protected function _beforeSave(){
		if ($ipAddress = $this->getIpAddress()){
			$ipList = explode(',',$this->getIpList());
			if (!in_array($ipAddress,$ipList)){
				$this->setIpList($this->getIpList().','.$ipAddress);
				$this->setUniqueClicks($this->getUniqueClicks() + 1);
			}
			$this->setTotalClicks($this->getTotalClicks() + 1);
			$this->setIpAddress(null);
		}
    	return parent::_beforeSave();
    }
}