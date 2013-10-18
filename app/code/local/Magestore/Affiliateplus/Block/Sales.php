<?php
class Magestore_Affiliateplus_Block_Sales extends Mage_Core_Block_Template
{
	protected $_transBlocksLabel = array();
	protected $_activeTransBlock = null;
	
	public function addTransactionBlock($name,$label,$link,$block){
		$this->_transBlocksLabel[$name] = array(
			'label'	=> $label,
			'link'	=> $this->getUrl($link)
		);
		$this->setChild($name,$block);
		return $this;
	}
	
	public function activeTransactionBlock($name){
		$this->_activeTransBlock = $name;
		return $this;
	}
	
	public function getTransactionHtml(){
		return $this->getChildHtml($this->_activeTransBlock);
	}
	
	public function getCommissionTabs(){
		if (count($this->_transBlocksLabel) > 1)
			return $this->_transBlocksLabel;
		return false;
	}
	
	public function getActiveTab(){
		return $this->_activeTransBlock;
	}
}