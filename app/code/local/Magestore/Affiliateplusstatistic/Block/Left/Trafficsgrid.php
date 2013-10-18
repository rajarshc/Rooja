<?php
class Magestore_Affiliateplusstatistic_Block_Left_Trafficsgrid extends Mage_Adminhtml_Block_Dashboard_Bar
{
	protected function _construct(){
		parent::_construct();
		$this->setTemplate('affiliateplusstatistic/grid.phtml');
	}
	
	protected function _prepareLayout(){
		$collection = Mage::getResourceModel('affiliateplusstatistic/statistic_collection')
			->prepareLifeTimeTotal();
		if ($storeId = $this->getRequest()->getParam('store'))
			$collection->addFieldToFilter('store_id',$storeId);
		$dataObject = $collection->getFirstItem();
		
		$this->addTotal($this->__('Total Clicks'),$dataObject->getTotalClicks(),true);
		$this->addTotal($this->__('Unique Clicks'),$dataObject->getTotalUniques(),true);
		
		$this->setTableId('trafficsgrid');
		parent::_prepareLayout();
	}
}