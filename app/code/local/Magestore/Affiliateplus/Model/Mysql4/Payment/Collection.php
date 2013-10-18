<?php

class Magestore_Affiliateplus_Model_Mysql4_Payment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $_load_method_info = true;
	
	public function setLoadMethodInfo($value){
		$this->_load_method_info = $value;
		return $this;
	}
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('affiliateplus/payment');
    }
    
    public function addStoreToFilter($storeId){
    	$this->getSelect()
    		->where('store_id = 0 OR store_id = ?',$storeId);
    	return $this;
    }
    
    protected function _afterLoad(){
    	parent::_afterLoad();
    	if ($this->_load_method_info)
	    	foreach ($this->_items as $item)
	    		$item->addPaymentInfo();
    	return $this;
    }
}