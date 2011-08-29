<?php

abstract class TBT_Rewardssocial_Model_Facebook_Like_Html_Abstract_Observer extends Varien_Object {
    
	/**
	 * Executed from the core_block_abstract_to_html_after event
	 * @param Varien_Event $obj
	 */
	public function afterOutput($obj) {
		$block = $obj->getEvent ()->getBlock ();
		$transport = $obj->getEvent ()->getTransport ();
		
		// Magento 1.4.0.1 and lower dont have this transport, so we can't do autointegration : (
		if(empty($transport)) {
			return $this;
		}
		
		$this->_afterOutput ( $block, $transport );
		
		return $this;
	}
	
	protected abstract function _afterOutput( &$block, &$transport ) ;
	
}
