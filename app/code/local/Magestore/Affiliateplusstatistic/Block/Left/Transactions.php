<?php
class Magestore_Affiliateplusstatistic_Block_Left_Transactions extends Magestore_Affiliateplusstatistic_Block_Left_Pie
{
	public function __construct(){
		$collection = Mage::getResourceModel('affiliateplusstatistic/sales_collection')
			->prepareLifeTimeTotal();
		if ($storeId = $this->getRequest()->getParam('store'))
			$collection->addFieldToFilter('store_id',$storeId);
		
		$chartData = array();
		foreach ($collection->load() as $item)
			$chartData[] = $item->getTotal();
		
		if (count($chartData)) $this->_is_has_data = true;
		switch (count($chartData)){
			case 0:
				$chartData[] = 0;
			case 1:
				$chartData[] = 0;
			case 2:
				$chartData[] = 0;
			case 3:
				break;
			default:
				$chartData = array_slice($chartData,0,3);
		}
		$buffer = implode(',',$chartData);
		
		$this->_google_chart_params = array(
			'cht'  => 'p3',
			'chdl' => $this->__('Completed (%d)',$chartData[0])
						.'|'.$this->__('Pending (%d)',$chartData[1])
						.'|'.$this->__('Canceled (%d)',$chartData[2]),
			'chd'  => "t:$buffer",
			'chdlp'=> 'b',
			'chco' => '0000dd|00dd00|dd0000'
		);
		
		$this->setHtmlId('left_transactions');
        parent::__construct();
    }
    
    protected function _prepareData(){
    	$this->setDataHelperName('affiliateplusstatistic/traffics');
    }
}