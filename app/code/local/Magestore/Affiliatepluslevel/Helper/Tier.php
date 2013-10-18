<?php

class Magestore_Affiliatepluslevel_Helper_Tier extends Mage_Core_Helper_Abstract
{
	protected $_tierCache = array();
	
	public function getOptionTypeHtml($level,$value=null){
		$html = '';
		foreach ($this->getTypeArray() as $key => $label){
			$html .= '<option value="'.$key.'" ';
			if ($value == $key) $html .= 'selected="true" ';
			$html .= '>'.$label.'</option>';
		}
		return $html;
	}
	
	public function getTypeArray(){
		return array(
			'percentage'	=> $this->__('Percent') .' - '. $this->__('Caculated on sales amount'),
			'fixed'	=> $this->__('Fixed'),
		);
	}
	
	public function getTypeLabel($type){
		$typeArray = $this->getTypeArray();
		if (isset($typeArray[$type])) return $typeArray[$type];
		return current($typeArray);
	}
	
	public function getConfig($code, $store = null){
		return Mage::getStoreConfig('affiliateplus/commission/'.$code,$store);
	}
	
	public function getMaxLevel($store = null){
		$maxLevel = intval($this->getConfig('max_level',$store));
		$maxLevel = ($maxLevel > 0) ? $maxLevel : 1;
		return $maxLevel;
	}
	
	public function getTierCommissionRates($store = null){
		if (is_null($store)) $storeId = Mage::app()->getStore()->getId();
		elseif (is_object($store)) $storeId = $store->getId();
		else $storeId = $store;
		
		if (isset($this->_tierCache["tier_commission_rates_$storeId"]))
			return $this->_tierCache["tier_commission_rates_$storeId"];
		
		$maxLevel = $this->getMaxLevel($store);
		$tierComs = unserialize($this->getConfig('tier_commission',$store));
		$tierRates = array();
		for ($i = 2; $i <= $maxLevel; $i++){
			if (isset($tierComs[$i])){
				$tierCom = $tierComs[$i];
				$tierRates[$i] = array(
					'value'	=> isset($tierCom['value']) ? $tierCom['value'] : 0,
					'type'	=> isset($tierCom['type']) ? $tierCom['type'] : 'percentage'
				);
			}
		}
		$this->_tierCache["tier_commission_rates_$storeId"] = $tierRates;
		
		return $this->_tierCache["tier_commission_rates_$storeId"];
	}
	
	public function getProgramMaxLevel($program){
		if ($program->getUseTierConfig()) return $this->getMaxLevel($program->getStoreId());
		$maxLevel = intval($program->getMaxLevel());
		return ($maxLevel > 0) ? $maxLevel : 1;
	}
	
	public function getTierProgramCommissionRates($program){
		if ($program->getUseTierConfig()) return $this->getTierCommissionRates($program->getStoreId());
		
		$maxLevel = $this->getProgramMaxLevel($program);
		$tierComs = $program->getTierCommission();
		if (!is_array($tierComs)) $tierComs = unserialize($tierComs);
		$tierRates = array();
		for ($i = 2; $i <= $maxLevel; $i++){
			if (isset($tierComs[$i])){
				$tierCom = $tierComs[$i];
				$tierRates[$i] = array(
					'value'	=> (isset($tierCom['value']) && $tierCom['value']) ? $tierCom['value'] : 0,
					'type'	=> (isset($tierCom['type']) && $tierCom['type']) ? $tierCom['type'] : 'percentage'
				);
			}
		}
		return $tierRates;
	}
	
	public function prepareLabelRates($rates){
		$tierRates = array();
		foreach ($rates as $level => $rate){
			$tierRate = array();
			$tierRate['level'] = $this->__("Level %d",$level);
			if ($rate['type'] == 'percentage'){
				$tierRate['commission'] = $rate['value'].'% '.$this->__('of sales amount');
			} else {
				$tierRate['commission'] = Mage::helper('core')->currency($rate['value']).' '.$this->__('per sale');
			}
			$tierRates[] = $tierRate;
		}
		return $tierRates;
	}
    
    public function getSecTierCommissionRates($store = null) {
        if (is_null($store)) $storeId = Mage::app()->getStore()->getId();
		elseif (is_object($store)) $storeId = $store->getId();
		else $storeId = $store;
		
        if (!$this->getConfig('use_sec_tier'))
            return array();
        
		if (isset($this->_tierCache["sec_tier_commission_rates_$storeId"]))
			return $this->_tierCache["sec_tier_commission_rates_$storeId"];
		
		$maxLevel = $this->getMaxLevel($store);
		$tierComs = unserialize($this->getConfig('sec_tier_commission',$store));
		$tierRates = array();
		for ($i = 2; $i <= $maxLevel; $i++){
			if (isset($tierComs[$i])){
				$tierCom = $tierComs[$i];
				$tierRates[$i] = array(
					'value'	=> isset($tierCom['value']) ? $tierCom['value'] : 0,
					'type'	=> isset($tierCom['type']) ? $tierCom['type'] : 'percentage'
				);
			}
		}
		$this->_tierCache["sec_tier_commission_rates_$storeId"] = $tierRates;
		
		return $this->_tierCache["sec_tier_commission_rates_$storeId"];
    }
    
    public function getSecTierProgramCommissionRates($program){
        if ($program->getUseTierConfig()) return $this->getSecTierCommissionRates($program->getStoreId());
        
        if (!$program->getUseSecTier())
            return array();
        
        $maxLevel = $this->getProgramMaxLevel($program);
		$tierComs = $program->getSecTierCommission();
		if (!is_array($tierComs)) $tierComs = unserialize($tierComs);
		$tierRates = array();
		for ($i = 2; $i <= $maxLevel; $i++){
			if (isset($tierComs[$i])){
				$tierCom = $tierComs[$i];
				$tierRates[$i] = array(
					'value'	=> (isset($tierCom['value']) && $tierCom['value']) ? $tierCom['value'] : 0,
					'type'	=> (isset($tierCom['type']) && $tierCom['type']) ? $tierCom['type'] : 'percentage'
				);
			}
		}
		return $tierRates;
    }
}
