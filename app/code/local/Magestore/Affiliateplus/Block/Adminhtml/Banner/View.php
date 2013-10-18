<?php

class Magestore_Affiliateplus_Block_Adminhtml_Banner_View extends Mage_Core_Block_Template
{
	protected function _beforeToHtml()
	{
		parent::_beforeToHtml();
		
		$banner = Mage::registry('banner'); 
		
		if(!$banner)
			$banner = $this->getBanner();
		if(!$banner)
			$bannerType = $this->getBannerType();
		else
			$bannerType = $banner->getTypeId();	
		if($bannerType == '1')
			$this->setTemplate('affiliateplus/banner/imageview.phtml');
		elseif($bannerType == '2')
			$this->setTemplate('affiliateplus/banner/flashview.phtml');
		
		return $this;
	}
}