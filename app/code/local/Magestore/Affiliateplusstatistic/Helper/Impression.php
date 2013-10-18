<?php

class Magestore_Affiliateplusstatistic_Helper_Impression extends Mage_Adminhtml_Helper_Dashboard_Abstract
{
	protected function _initCollection(){
		$isFilter = $this->getParam('store');
		
		$this->_collection = Mage::getResourceModel('affiliateplusstatistic/statistic_collection')
			->prepareSummary($this->getParam('period'),0,0,$isFilter,1);
		
		if ($this->getParam('store'))
			$this->_collection->addFieldToFilter('store_id',$this->getParam('store'));
		
		$this->_collection->load();
	}
}