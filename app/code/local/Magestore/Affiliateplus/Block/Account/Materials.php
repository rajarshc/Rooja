<?php
class Magestore_Affiliateplus_Block_Account_Materials extends Mage_Core_Block_Template
{
	public function getPageIdentifier(){
		return Mage::helper('affiliateplus/config')->getMaterialConfig('page');
	}
	
	public function getPageId(){
		$page = Mage::getModel('cms/page');
		$pageId = $page->checkIdentifier($this->getPageIdentifier(), Mage::app()->getStore()->getId());
		return $pageId;
	}
	
	public function getPage(){
		return Mage::getSingleton('cms/page');
	}
	
	protected function _construct(){
		parent::_construct();
		$page = Mage::getSingleton('cms/page');
		if ($pageId = $this->getPageId())
			$page->setStoreId(Mage::app()->getStore()->getId())->load($pageId);
	}
	
	protected function _toHtml(){
		$helper = Mage::helper('cms');
		$processor = $helper->getPageTemplateProcessor();
		
		$html = $this->getMessagesBlock()->getGroupedHtml();
		if ($pageHeading = $this->getChild('page_content_heading')){
			$pageHeading->setContentHeading($this->getPage()->getContentHeading());
			$html .= $pageHeading->toHtml();
		}
		$html .= $processor->filter($this->getPage()->getContent());
		return $html;
	}
}