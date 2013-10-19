<?php
class Magestore_Affiliateplusstatistic_Block_Left_Transactionsgrid extends Mage_Adminhtml_Block_Dashboard_Bar
{
	protected function _construct(){
		parent::_construct();
		$this->setTemplate('affiliateplusstatistic/grid.phtml');
	}
	
	protected function _prepareLayout(){
		$collection = Mage::getResourceModel('affiliateplusstatistic/sales_collection')
			->prepareLifeTimeTotal();
		if ($storeId = $this->getRequest()->getParam('store'))
			$collection->addFieldToFilter('store_id',$storeId);
		
		$chartData = array();
		foreach ($collection->load() as $item)
			$chartData[] = $item->getTotal();
		
		if (count($chartData)) $this->_is_has_data = true;
		switch (count($chartData)){
			case 1:
				$chartData[1] = 0;
			case 2:
				$chartData[2] = 0;
			case 3:
				break;
			default:
				$chartData = array_slice($chartData,0,3);
		}
		
		$this->addTotal($this->__('Completed'),$chartData[0],true);
		$this->addTotal($this->__('Pending'),$chartData[1],true);
		$this->addTotal($this->__('Canceled'),$chartData[2],true);
		
		$this->setTableId('transactionsgrid');
		parent::_prepareLayout();
	}
}