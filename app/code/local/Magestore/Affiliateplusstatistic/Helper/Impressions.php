<?php

class Magestore_Affiliateplusstatistic_Helper_Impressions extends Mage_Adminhtml_Helper_Dashboard_Abstract
{
	protected function _initCollection(){
		$isFilter = $this->getParam('store');
		$this->_collection = Mage::getResourceModel('affiliateplusstatistic/impression_collection')
			->prepareSummary($this->getParam('period'),0,0,$isFilter);
        
		if ($this->getParam('store'))
			$this->_collection->addFieldToFilter('store_id',$this->getParam('store'));
		$this->_collection->load();
	}
}