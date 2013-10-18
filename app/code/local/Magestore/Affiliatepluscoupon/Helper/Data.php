<?php

class Magestore_Affiliatepluscoupon_Helper_Data extends Mage_Core_Helper_Data
{
	protected $_helpData = array();
	
	public function calcCode($expression){
		if ($this->isExpression($expression)){
			return preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#',array($this,'convertExpression'),$expression);
		}else{
			return $expression;
		}
	}
	
	public function convertExpression($param){
		$alphabet  = (strpos($param[1],'A'))===false ? '':'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$alphabet .= (strpos($param[1],'N'))===false ? '': '0123456789';
		return $this->getRandomString($param[2],$alphabet);
	}
	
	public function isExpression($string){
		return preg_match('#\[([AN]{1,2})\.([0-9]+)\]#',$string);
	}
	
	public function generateNewCoupon($expression = null){
		if (!$expression)
			$expression = Mage::getStoreConfig('affiliateplus/coupon/pattern');
		if (!$this->isExpression($expression))
			$expression = 'AFFILIATE-[N.4]-[AN.5]-[A.4]';
		$code = $this->calcCode($expression);
		$times = 100;
		while (Mage::getModel('affiliatepluscoupon/coupon')->load($code,'coupon_code')->getId() && $times){
			$times--;
			if ($times == 0){
				throw new Exception($this->__('Tried to generate coupon code 100 times!'));
			} else {
				$code = $this->calcCode($expression);
			}
		}
		return $code;
	}
	
	public function couponIsDisable(){
		return !Mage::getStoreConfig('affiliateplus/coupon/enable') || Mage::helper('affiliateplus/account')->accountNotLogin();
	}
	
	public function isMultiProgram(){
		if (isset($this->_helpData['is_multi_program'])) return $this->_helpData['is_multi_program'];
		$this->_helpData['is_multi_program'] = false;
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;
		if (isset($modulesArray['Magestore_Affiliateplusprogram'])
			&& is_object($modulesArray['Magestore_Affiliateplusprogram']))
				$this->_helpData['is_multi_program'] = $modulesArray['Magestore_Affiliateplusprogram']->is('active');
		return $this->_helpData['is_multi_program'];
	}
}