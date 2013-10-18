<?php
class Magestore_Affiliateplusstatistic_Block_Statistic extends Mage_Adminhtml_Block_Template
{
	const XML_PATH_ENABLE_CHARTS = 'affiliateplus/statistic/charts';
	
	public function __construct(){
		parent::__construct();
		$this->setTemplate('affiliateplusstatistic/statistic.phtml');
	}
	
	protected function _prepareLayout(){
		$this->setChild('sales',$this->getLayout()->createBlock('affiliateplusstatistic/sales'));
		
		if (Mage::getStoreConfig(self::XML_PATH_ENABLE_CHARTS)){
			$this->setChild('diagrams',$this->getLayout()->createBlock('affiliateplusstatistic/diagrams'));
			$this->setChild('left_traffics',$this->getLayout()->createBlock('affiliateplusstatistic/left_traffics'));
			$this->setChild('left_commissions',$this->getLayout()->createBlock('affiliateplusstatistic/left_transactions'));
		}else {
			$block = $this->getLayout()->createBlock('adminhtml/template')
				->setTemplate('dashboard/graph/disabled.phtml')
				->setConfigUrl($this->getUrl('adminhtml/system_config/edit', array('section'=>'affiliateplus')));
			$this->setChild('diagrams',$block);
			$this->setChild('left_traffics',$this->getLayout()->createBlock('affiliateplusstatistic/left_trafficsgrid'));
			$this->setChild('left_commissions',$this->getLayout()->createBlock('affiliateplusstatistic/left_transactionsgrid'));
		}
		
		$this->setChild('totals',$this->getLayout()->createBlock('affiliateplusstatistic/diagrams_totals'));
		
		$this->setChild('grids',$this->getLayout()->createBlock('affiliateplusstatistic/grids'));
		
		parent::_prepareLayout();
	}
	
	public function getSwitchUrl(){
		if ($url = $this->getData('switch_url'))
			return $url;
		return $this->getUrl('*/*/*', array('_current'=>true, 'period'=>null));
	}
}