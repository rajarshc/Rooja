<?php
class Magestore_Affiliateplus_Block_Sales_Statistic extends Mage_Core_Block_Template
{
	protected $_transactionBlock = array();
	
	protected $_statisticInfo = array();
	protected $_totalStatistic = array(
		'number_commission'	=> 0,
		'commissions'	=> 0
	);
	
	public function addTransactionBlock($name,$label,$link,$type,$template=null){
		$block = $this->getLayout()->createBlock($type,$name);
		if ($template) $block->setTemplate($template);
		$this->_transactionBlock[$name] = $block;
		$this->getParentBlock()->addTransactionBlock($name,$label,$link,$block);
		return $this;
	}
	
	public function getFormatedCurreny($value){
		return Mage::helper('core')->currency($value);
    }
	
	public function getStatisticInfo(){
		return $this->_statisticInfo;
	}
	
	public function getTotalStatistic(){
		return $this->_totalStatistic;
	}
	
	protected function _toHtml(){
		foreach ($this->_transactionBlock as $block)
			if (method_exists($block,'getStatisticInfo')){
				$staticInfo = $block->getStatisticInfo();
				$this->_statisticInfo[] = $staticInfo;
				$this->_totalStatistic['number_commission'] += $staticInfo['number_commission'];
				$this->_totalStatistic['commissions'] += $staticInfo['commissions'];
			}
		if (count($this->_statisticInfo) <= 1) return '';
		return parent::_toHtml();
	}
}