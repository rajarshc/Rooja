<?php

class Magestore_Affiliateplus_Model_Mysql4_Referer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplus/referer');
    }
    
    public function getIpListArray(){
    	$ipList = array();
    	$this->load();
    	foreach ($this->_items as $item)
    		$ipList = array_merge($ipList,explode(',',$item->getIpList()));
    	
    	return $ipList;
    }
}