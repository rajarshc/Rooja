<?php
class Magestore_Affiliatepluslevel_Block_Adminhtml_System_Config_Form_Field_Tier extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
		$this->setElement($element);
		return $this->_toHtml();
	}
	
	protected function _getConfig($path){
		$storeCode = Mage::app()->getRequest()->getParam('store');
		$websiteCode = Mage::app()->getRequest()->getParam('website');
		if ($storeCode) return Mage::app()->getStore($storeCode)->getConfig($path);
		if ($websiteCode) return Mage::app()->getWebsite($websiteCode)->getConfig($path);
		return (string) Mage::getConfig()->getNode('default/'.$path);
	}
	
	/**
	 * Constructor for block 
	 * 
	 */
	public function __construct(){
		parent::__construct();
		$this->setTemplate('affiliatepluslevel/tier.phtml');
	}
	
	public function getHtmlId(){
		return 'affiliateplus_commission_tier_commission';
	}
	
	public function getMaxLevel(){
		$_maxLevel = intval($this->_getConfig('affiliateplus/commission/max_level'));
		return ($_maxLevel > 0) ? $_maxLevel : 1;
	}
	
	public function getArrayRows(){
		if ($this->hasData('_array_rows_cache')) return $this->getData('_array_rows_cache');
		
		$result = array();
		$element = $this->getElement();
		if ($element->getValue() && is_array($element->getValue())){
			foreach ($element->getValue() as $rowId => $row){
				foreach ($row as $key => $value) {
					$row[$key] = $this->htmlEscape($value);
				}
				$row['_id'] = $rowId;
				$result[$rowId] = new Varien_Object($row);
			}
		}
		$this->setData('_array_rows_cache',$result);
		
		return $this->getData('_array_rows_cache');
	}
	
	public function getDefaultCommission(){
		return $this->_getConfig('affiliateplus/commission/commission');
	}
	
	public function getDefaultCommissionType(){
		return $this->_getConfig('affiliateplus/commission/commission_type');
	}
}