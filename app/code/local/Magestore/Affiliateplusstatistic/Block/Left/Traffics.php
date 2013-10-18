<?php
class Magestore_Affiliateplusstatistic_Block_Left_Traffics extends Magestore_Affiliateplusstatistic_Block_Left_Pie
{
	public function __construct(){
		$collection = Mage::getResourceSingleton('affiliateplusstatistic/statistic_collection')
			->prepareLifeTimeTotal();
		if ($storeId = $this->getRequest()->getParam('store'))
			$collection->addFieldToFilter('store_id',$storeId);
		$dataObject = $collection->getFirstItem();
		
		$total = $dataObject->getTotalClicks();
		$uniques = $dataObject->getTotalUniques();
		if ($total && $uniques) $this->_is_has_data = true;
		
		$this->_google_chart_params = array(
			'cht'  => 'p3',
			'chdl' => $this->__('Total Clicks (%d)',$total).'|'.$this->__('Unique Clicks (%d)',$uniques),
			'chd'  => "t:$total,$uniques",
			'chdlp'=> 'b',
			'chco' => 'dd0000|0000dd'
		);
		
		$this->setHtmlId('left_traffics');
        parent::__construct();
    }
    
    protected function _prepareData(){
    	$this->setDataHelperName('affiliateplusstatistic/traffics');
    }
}